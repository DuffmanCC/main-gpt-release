<?php

namespace MainGPT\Service;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use MainGPT\Repository\Config;

class PineconeClient
{
    public const INDEXES_URL = 'https://api.pinecone.io/indexes';
    public const VECTOR_UPSERT_URL = '/vectors/upsert';
    public const VECTOR_DELETE_URL = '/vectors/delete';
    public const DESCRIBE_INDEX_STATS_URL = '/describe_index_stats';
    public const QUERY_URL  = '/query';

    protected string $apiKey;
    protected string $environment;
    protected ClientInterface $client;

    public function __construct(
        Config $config,
        ClientInterface $client
    ) {
        $this->apiKey = $config::getOption($config::OPTION_PINECONE_API_KEY) ?? '';
        $this->client = $client;
    }

    /**
     * @throws ClientException
     * @throws Exception
     */
    public function listIndexes(): array
    {
        $response = $this->client->request(
            'GET',
            self::INDEXES_URL,
            [
                'headers' => $this->getAuthenticatedHeaders(),
            ]
        );

        $body = $response->getBody()->getContents();

        $responseArray = json_decode($body, true);

        return $responseArray;
    }

    /**
     * Creates an index in Pinecone.
     *
     * Starter plan is serverless, no pod base as before,
     * we only need to specify name and metric for the moment.
     *
     * @link https://docs.pinecone.io/reference/api/2024-07/control-plane/create_index
     * @link https://docs.pinecone.io/guides/indexes/understanding-indexes#cloud-regions
     *
     * @param string $metric
     * @param string $name
     * @param int $dimension
     * @return array
     * @throws ClientException
     * @throws Exception
     */
    public function createIndex(
        string $metric,
        string $name,
        int $dimension
    ) {
        return $this->client->request(
            'POST',
            self::INDEXES_URL,
            [
                'headers' => $this->getAuthenticatedHeaders(),
                'json' => [
                    'name' => $name,
                    'dimension' => $dimension,
                    'metric' => $metric,
                    'spec' => [
                        'serverless' => [
                            'cloud' => 'aws',
                            'region' => 'us-east-1'
                        ]
                    ],
                ]
            ]
        );
    }

    /**
     * Deletes an index in Pinecone.
     *
     * @link https://docs.pinecone.io/reference/api/2024-07/control-plane/delete_index
     *
     * @param string $indexName
     * @throws ClientException
     * @throws Exception
     */
    public function deleteIndex($indexName)
    {
        return $this->client->request(
            'DELETE',
            self::INDEXES_URL . '/' . $indexName,
            [
                'headers' => $this->getAuthenticatedHeaders(),
            ]
        );
    }

    /**
     * @throws ClientException
     * @throws Exception
     */
    public function deleteAllVectors(
        string $host
    ) {
        $headers = $this->getAuthenticatedHeaders();
        $headers['accept'] = 'application/json';
        $headers['content-type'] = 'application/json';

        return $this->client->request(
            'POST',
            'https://' . $host . self::VECTOR_DELETE_URL,
            [
                'headers' => $headers,
                'json' => [
                    'deleteAll' => 'true',
                    'namespace' => ''
                ]
            ]
        );
    }

    /**
     * @param string $name
     * @return int|false
     * @throws ClientException
     * @throws Exception
     */
    public function numberOfVectors(
        string $name
    ): int | false {
        if (empty($name)) {
            throw new Exception('Index name is empty');

            return false;
        }

        $indexStats = $this->indexStats($name);

        if (empty($indexStats)) {
            return -1;
        }

        return $indexStats['totalVectorCount'];
    }

    /**
     * @param string $indexName
     * @return array
     * @throws ClientException
     * @throws Exception
     */
    public function indexStats(string $indexName): array
    {
        if (empty($indexName)) {
            throw new Exception('Index name is empty');
        }

        $indexes = $this->listIndexes();

        $indexKey = array_search($indexName, array_column($indexes['indexes'], 'name'));

        if ($indexKey === false) {
            return [];
        }

        $matchingIndex = $indexes['indexes'][$indexKey];
        $host = $matchingIndex['host'];

        $response = $this->client->request(
            'GET',
            'https://' . $host . self::DESCRIBE_INDEX_STATS_URL,
            [
                'headers' => $this->getAuthenticatedHeaders(),
            ]
        );

        $body = $response->getBody()->getContents();

        $responseArray = json_decode($body, true);

        return $responseArray;
    }

    /**
     * @param string $indexName
     * @return array
     * @throws ClientException
     * @throws Exception
     */
    public function indexInfo(string $indexName): array
    {
        $response = $this->client->request(
            'GET',
            self::INDEXES_URL . '/' . $indexName,
            [
                'headers' => $this->getAuthenticatedHeaders(),
            ]
        );

        $body = $response->getBody()->getContents();

        $responseArray = json_decode($body, true);

        return $responseArray;
    }

    /**
     * @throws ClientException
     * @throws Exception
     */
    public function upsert(
        string $host,
        array $vectors
    ) {
        $headers = $this->getAuthenticatedHeaders();
        $headers['accept'] = 'application/json';
        $headers['content-type'] = 'application/json';

        return $this->client->request(
            'POST',
            'https://' . $host . self::VECTOR_UPSERT_URL,
            [
                'headers' => $headers,
                'json' => [
                    'vectors' => $vectors,
                    'namespace' => ''
                ]
            ]
        );
    }

    /**
     * @throws ClientException
     * @throws Exception
     */
    public function query(
        string $host,
        array $request,
        int $topK,
    ): array {
        $headers = $this->getAuthenticatedHeaders();
        $headers['accept'] = 'application/json';
        $headers['content-type'] = 'application/json';

        $arr = [
            'headers' => $headers,
            'json' => [
                'vector' => $request,
                'topK' => $topK,
                'includeMetadata' => true, // I need the metadata
                'includeValues' => false // I don't need the vectors, only the metadata
            ]
        ];

        $response = $this->client->request(
            'POST',
            'https://' . $host . self::QUERY_URL,
            $arr
        );

        $body = $response->getBody()->getContents();

        $responseArray = json_decode($body, true);

        return $responseArray;
    }

    protected function getAuthenticatedHeaders(): array
    {
        return ['Api-Key' => $this->apiKey];
    }
}
