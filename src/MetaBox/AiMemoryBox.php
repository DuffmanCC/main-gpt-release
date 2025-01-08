<?php

namespace MainGPT\MetaBox;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;
use MainGPT\MetaBox\AbstractMetaBoxable;
use MainGPT\PostMeta\AiMemory\IndexNameObject;
use MainGPT\PostMeta\AiMemory\IsAdvancedMetaboxObject;
use MainGPT\PostMeta\AiMemory\PostIdsObject;
use MainGPT\PostMeta\AiChatter\AiChatObject;
use MainGPT\PostType\AiMemory;
use MainGPT\Repository\Config;
use MainGPT\Ajax\Admin\CreateIndexAjax;
use MainGPT\Ajax\Admin\SaveAiMemoryAjax;
use MainGPT\Ajax\Admin\TokenizePostsAjax;
use MainGPT\Ajax\Admin\EmbedDataAjax;
use MainGPT\Ajax\Admin\EmbeddingPercentageAjax;
use MainGPT\Ajax\Admin\TokenizingPercentageAjax;
use MainGPT\Ajax\Admin\UpsertVectorAjax;
use MainGPT\Ajax\Admin\UploadFileAjax;
use MainGPT\Helpers\AiMemoryHelper;
use MainGPT\Helpers\AiChatterHelper;

final class AiMemoryBox extends AbstractMetaBoxable
{
    public const INIT_NAME = App::ID . '_ai_memory';
    public const TITLE = 'AI Memory';
    public const CONTEXT = 'normal';
    public const PRIORITY = 'default';
    public const SCRIPT_NAME = 'ai_memory';

    public function init(): void
    {
        $this->addAction(14, 2);
        $this->saveMetaBox(self::INIT_NAME);
        add_action('before_delete_post', [$this, 'deleteHook'], 10, 1);
        add_action('wp_trash_post', [$this, 'deleteHook'], 10, 1);
    }

    public function execute(): void
    {
        add_meta_box(
            self::INIT_NAME,
            self::TITLE,
            [$this, parent::RENDER_METHOD],
            AiMemory::POST_TYPE,
            self::CONTEXT,
            self::PRIORITY
        );

        $postId = $this->getPostId();
        $this->addScripts(self::SCRIPT_NAME, $this->getData($postId));
    }

    public function saveCustomMetaBoxData() {}

    /**
     * @param int $postId
     * 
     * This method is called before a post is sent 
     * to the Trash and and before a post is deleted
     */
    public function deleteHook($postId): void
    {
        $indexName = get_post_meta($postId, IndexNameObject::FIELD_ID, true);

        AiMemoryHelper::deleteIndex($indexName);

        $aiChatObject = AiChatterHelper::aiChat($postId);
        $aiChatObject['pineconeIndexName'] = '';

        update_post_meta($postId, AiChatObject::FIELD_ID, json_encode($aiChatObject));
    }

    public function render(): void
    {
        echo $this->removePostClass(self::INIT_NAME);
    }

    protected function getData($postId = null): array
    {
        return [
            'restUrl' => rest_url('wp/v2/'),
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'ajaxActionConnectOrCreateIndex' => CreateIndexAjax::AJAX_ACTION,
            'ajaxActionSaveAiMemory' => SaveAiMemoryAjax::AJAX_ACTION,
            'ajaxActionTokenizePosts' => TokenizePostsAjax::AJAX_ACTION,
            'ajaxActionEmbedData' => EmbedDataAjax::AJAX_ACTION,
            'ajaxActionEmbeddingPercentage' => EmbeddingPercentageAjax::AJAX_ACTION,
            'ajaxActionTokenizingPercentage' => TokenizingPercentageAjax::AJAX_ACTION,
            'ajaxActionUploadFile' => UploadFileAjax::AJAX_ACTION,
            'ajaxUpsertVector' => UpsertVectorAjax::AJAX_ACTION,
            'nonceConnectOrCreateIndex' => wp_create_nonce(CreateIndexAjax::AJAX_ACTION),
            'nonceSaveAiMemory' => wp_create_nonce(SaveAiMemoryAjax::AJAX_ACTION),
            'nonceTokenizePosts' => wp_create_nonce(TokenizePostsAjax::AJAX_ACTION),
            'nonceEmbedData' => wp_create_nonce(EmbedDataAjax::AJAX_ACTION),
            'nonceEmbeddingPercentage' => wp_create_nonce(EmbeddingPercentageAjax::AJAX_ACTION),
            'nonceTokenizingPercentage' => wp_create_nonce(TokenizingPercentageAjax::AJAX_ACTION),
            'nonceUpsertVector' => wp_create_nonce(UpsertVectorAjax::AJAX_ACTION),
            'nonceUploadFile' => wp_create_nonce(UploadFileAjax::AJAX_ACTION),
            'postId' => $postId,
            'indexName' => get_post_meta($postId, IndexNameObject::FIELD_ID, true),
            'indexStatus' => AiMemoryHelper::indexStatus($postId),
            'aiMemory' => AiMemoryHelper::aiMemory($postId),
            'addedPosts' => json_decode(get_post_meta($postId, PostIdsObject::FIELD_ID, true)),
            'isAdvancedMetabox' => get_post_meta($postId, IsAdvancedMetaboxObject::FIELD_ID, true)
        ];
    }
}
