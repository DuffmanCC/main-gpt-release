<?php

namespace MainGPT\Service;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Exception;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use MainGPT\Repository\Config;

class OpenAiClient
{
    protected string $apiKey;
    protected string $organization;
    protected ClientInterface $client;
    protected string $embedUrl;
    protected string $chatCompletionUrl;

    public function __construct(
        Config $config,
        ClientInterface $client,
        string $embedUrl,
        string $chatCompletionUrl
    ) {
        $this->apiKey = $config::getOption($config::OPTION_OPENAI_API_KEY) ?? '';;
        $this->organization = $config::getOption($config::OPTION_OPENAI_ORGANIZATION) ?? '';;
        $this->client = $client;
        $this->embedUrl = $embedUrl;
        $this->chatCompletionUrl = $chatCompletionUrl;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function embedData(string $modelName, string|array $data): array
    {
        $response = $this->client->request(
            'POST',
            $this->embedUrl,
            [
                'headers' => $this->getAuthenticatedHeaders(),
                'json' => [
                    'model' => $modelName,
                    'input' => $data
                ]
            ]
        );

        $body = $response->getBody()->getContents();

        if (200 != $response->getStatusCode()) {
            /**
             * @todo Substitute this with an Exception class
             */
            throw new Exception(
                __FILE__ . ":" . __LINE__ . " | embedData response error with body: " . $body
            );
        }

        $responseArray = json_decode($body, true);

        $lastError = json_last_error();
        if (JSON_ERROR_NONE != $lastError) {
            /**
             * @todo Substitute this with an Exception class
             */
            throw new Exception(
                __FILE__ . ":" . __LINE__ . " | embedData cannot decode body, error id: " . $lastError .
                    " and body: " . $body
            );
        }

        return $responseArray;
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    public function chatCompletion(string $modelName, array $messages): array
    {
        $response = $this->client->request(
            'POST',
            $this->chatCompletionUrl,
            [
                'headers' => $this->getAuthenticatedHeaders(),
                'json' => [
                    'model' => $modelName,
                    'messages' => $messages
                ]
            ]
        );

        $body = $response->getBody()->getContents();

        if (200 != $response->getStatusCode()) {
            /**
             * @todo Substitute this with an Exception class
             */
            throw new Exception(
                __FILE__ . ':' . __LINE__ . ":" . __LINE__ . " | chatCompletion response error with body: " . $body
            );
        }

        $responseArray = json_decode($body, true);

        $lastError = json_last_error();
        if (JSON_ERROR_NONE != $lastError) {
            /**
             * @todo Substitute this with an Exception class
             */
            throw new Exception(
                __FILE__ . ':' . __LINE__ . ":" . __LINE__ . " | chatCompletion cannot decode body, error id: " . $lastError .
                    " and body: " . $body
            );
        }

        return $responseArray;
    }

    /**
     * @throws GuzzleException
     */
    protected function getAuthenticatedHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'OpenAI-Organization' => $this->organization
        ];
    }
}
