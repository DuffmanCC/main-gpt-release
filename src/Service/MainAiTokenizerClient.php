<?php

namespace MainGPT\Service;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use MainGPT\PostMeta\AiMemory\UuidObject;
use MainGPT\Repository\Config;

class MainAiTokenizerClient
{
    protected string $url;
    protected ClientInterface $client;

    public function __construct(
        string $url,
        ClientInterface $client
    ) {
        $this->url = $url;
        $this->client = $client;
    }

    /**
     * @param int $size
     * @param int $overlap
     * @param string[] $separators
     * @param array<int, array<string, string>> $data
     * @return array
     * @throws GuzzleException
     * @throws Exception
     */
    public function tokenize(
        int $size,
        int $overlap,
        array $separators,
        array $data,
        int $postId
    ): array {
        $uuid = get_post_meta($postId, UuidObject::FIELD_ID, true);

        $request = [
            'chunk_size' => $size,
            'chunk_overlap' => $overlap,
            'separators' => $separators,
            "ai_memory_uuid" => $uuid,
            'data' => $data
        ];

        $headers = [
            'X-API-KEY-UL' => Config::getOption(Config::OPTION_MAIN_API_KEY),
            'X-API-SECRET-UL' => Config::getOption(Config::OPTION_MAIN_API_SECRET)
        ];

        $response = $this->client->request(
            'POST',
            $this->url,
            [
                'json' => $request,
                'headers' => $headers
            ]
        );

        $body = $response->getBody()->getContents();

        if (200 != $response->getStatusCode()) {
            /**
             * @todo Substitute this with an Exception class
             */
            throw new Exception(
                __FILE__ . ":" . __LINE__ . " | tokenize response error with body: " . $body
            );
        }

        $responseArray = json_decode($body, true);

        $lastError = json_last_error();
        if (JSON_ERROR_NONE != $lastError) {
            /**
             * @todo Substitute this with an Exception class
             */
            throw new Exception(
                __FILE__ . ":" . __LINE__ . " | tokenize cannot decode body, error id: " . $lastError .
                    " and body: " . $responseArray
            );
        }

        return $responseArray;
    }
}
