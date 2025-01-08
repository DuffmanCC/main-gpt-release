<?php

namespace MainGPT\Ajax\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Exception;
use Throwable;
use MainGPT\AbstractActionable;
use MainGPT\App;
use MainGPT\HookableTrait;
use MainGPT\PostMeta\AiMemory\IndexNameObject;
use MainGPT\PostMeta\AiMemory\AiMemoryObject;
use MainGPT\PostMeta\AiMemory\IsAdvancedMetaboxObject;
use JsonSchema\Validator;


class SaveAiMemoryAjax extends AbstractActionable
{
    use HookableTrait;
    public const AJAX_ACTION = App::ID . '_save_ai_memory_index_ajax';
    public const INIT_NAME = 'wp_ajax_' . self::AJAX_ACTION;

    public function execute(): void
    {
        try {
            check_ajax_referer(self::AJAX_ACTION, 'security');

            $id = $_POST['data']['postId'];
            $pinecone_values = $_POST['data']['pineconeValues'];
            $selected_index = $_POST['data']['selectedIndex'];
            $is_advanced = $_POST['data']['isAdvanced'];

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

            if ($validator->isValid()) {
                update_post_meta($id, AiMemoryObject::FIELD_ID, $unescaped_json_string);
            } else {
                $error_string =  "<strong>JSON does not validate</strong>. Violations:<br/>";

                foreach ($validator->getErrors() as $error) {
                    $error_string .= "<b>{$error['property']}</b>: {$error['message']}<br/>";
                }

                throw new Exception($error_string);
            }

            if ($is_advanced === 'false') {
                $is_advanced = false;
            } else {
                $is_advanced = true;
            }

            update_post_meta($id, IndexNameObject::FIELD_ID, $selected_index);
            update_post_meta($id, IsAdvancedMetaboxObject::FIELD_ID, $is_advanced);

            wp_send_json(
                [
                    'message' => 'Data saved successfully.',
                    'data' => [
                        'postId' => $id,
                        'pineconeValues' => $pinecone_values,
                        'selectedIndex' => $selected_index
                    ]
                ],
                200
            );
        } catch (Throwable $exception) {
            error_log(__FILE__ . ":" . __LINE__ . " | execute unexpected error.");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));

            wp_send_json(
                [
                    // "code" => $exception->getCode(),
                    "message" => $exception->getMessage(),
                    // "data" => $values
                ],
                500
            );
        }
    }
}
