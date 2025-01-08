<?php

namespace MainGPT\Ajax\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Exception;
use Throwable;
use MainGPT\AbstractActionable;
use MainGPT\App;
use MainGPT\HookableTrait;
use MainGPT\PostMeta\AiMemory\EmbeddedDataObject;
use MainGPT\PostMeta\AiMemory\IndexNameObject;
use MainGPT\Service\OpenAiClient;
use MainGPT\Service\PineconeClient;
use MainGPT\Validation;

class UpsertVectorAjax extends AbstractActionable
{
    use HookableTrait;
    public const AJAX_ACTION = App::ID . '_upsert_vector_ajax';
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

            $id = (int) $_POST['data']['postId'];
            $json = get_post_meta($id, EmbeddedDataObject::FIELD_ID, true);
            $values = json_decode($json, true);

            $indexName = $_POST['data']['indexName'];

            $info = $this->pineconeClient->indexInfo($indexName);
            $stats = $this->pineconeClient->indexStats($indexName);

            if ($stats['totalVectorCount'] > 0) {
                $this->pineconeClient->deleteAllVectors($info['host']);
            }

            $vectors = [];

            foreach ($values['embeddedData'] as $embed) {
                foreach ($embed['data'] as $data) {
                    $vectors[] = [
                        'id' => $data['id'],
                        'values' => $data['values'],
                        'metadata' => $data['metadata']
                    ];
                }
            }

            foreach (array_chunk($vectors, 10) as $vector) {
                $this->pineconeClient->upsert(
                    $info['host'],
                    $vector
                );
            }

            $message = 'The vectors have been successfully upserted';

            update_post_meta(
                $id,
                IndexNameObject::FIELD_ID,
                $indexName
            );

            wp_send_json(
                [
                    'message' => $message
                ],
                200
            );
        } catch (Throwable $exception) {
            error_log(__FILE__ . ":" . __LINE__ . " | execute unexpected error.");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));

            wp_send_json(
                [
                    "code" => $exception->getCode(),
                    "message" => $exception->getMessage(),
                    "data" => $values,
                    "dimensions" => $values['embeddedData'][0]['data'][0]['dimension'],
                ],
                500
            );
        }
    }
}
