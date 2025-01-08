<?php

namespace MainGPT\Ajax\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Exception;
use GuzzleHttp\Exception\ClientException;
use MainGPT\AbstractActionable;
use MainGPT\App;
use MainGPT\HookableTrait;
use MainGPT\PostMeta\AiMemory\IndexNameObject;
use MainGPT\PostMeta\AiMemory\AiMemoryObject;
use MainGPT\Service\OpenAiClient;
use MainGPT\Service\PineconeClient;
use JsonSchema\Validator;

class CreateIndexAjax extends AbstractActionable
{
    use HookableTrait;
    public const AJAX_ACTION = App::ID . '_create_index';
    public const INIT_NAME = 'wp_ajax_' . self::AJAX_ACTION;

    protected OpenAiClient $openAiClient;
    protected PineconeClient $pineconeClient;

    public function __construct(
        OpenAiClient $openAiClient,
        PineconeClient $pineconeClient
    ) {
        $this->openAiClient = $openAiClient;
        $this->pineconeClient = $pineconeClient;
    }

    public function execute(): void
    {
        try {
            check_ajax_referer(self::AJAX_ACTION, 'security');

            $id = $_POST['data']['postId'];
            $index_name = $_POST['data']['indexName'];
            $pinecone_values = $_POST['data']['pineconeValues'];

            // Remove backslashes from the JSON string
            $unescaped_json_string = stripslashes($pinecone_values);

            $json_object = json_decode($unescaped_json_string);

            if ($json_object === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("JSON decoding failed with error: " . json_last_error_msg());
            }

            $validator = new Validator;
            $validator->validate(
                $json_object,
                AiMemoryObject::jsonSchema()
            );

            if (!$validator->isValid()) {
                $error_string = "<strong>JSON does not validate</strong>. Violations:<br/>";

                foreach ($validator->getErrors() as $error) {
                    $error_string .= "<b>{$error['property']}</b>: {$error['message']}<br/>";
                }

                throw new Exception($error_string);
            }

            $metric = $json_object->metric;

            $this->pineconeClient->createIndex(
                $metric,
                $index_name,
                1536
            );

            update_post_meta($id, IndexNameObject::FIELD_ID, $index_name);

            wp_send_json(
                [
                    'message' => 'The index has been successfully created.',
                    'indexName' => $index_name
                ],
                200
            );
        } catch (ClientException $exception) {
            error_log(__FILE__ . ":" . __LINE__ . " | execute unexpected error.");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());

            $response = $exception->getResponse();
            $responseBody = $response->getBody()->getContents();

            wp_send_json(
                [
                    "message" => $responseBody,
                ],
                500
            );
        } catch (Exception $exception) {
            error_log(__FILE__ . ":" . __LINE__ . " | execute unexpected error.");
            error_log($exception->getMessage());

            wp_send_json(
                [
                    "message" => $exception->getMessage(),
                ],
                500
            );
        }
    }
}
