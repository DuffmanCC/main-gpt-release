<?php

namespace MainGPT\MetaBox;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;
use MainGPT\MetaBox\AbstractMetaBoxable;
use MainGPT\PostType\AiChatter;
use MainGPT\PostMeta\AiChatter\ModelNameObject;
use MainGPT\PostMeta\AiChatter\AiChatObject;
use MainGPT\Repository\Config;
use MainGPT\Validation;
use MainGPT\Helpers\AiChatterHelper;

final class AiChatterBox extends AbstractMetaBoxable
{
    /** Used here as MetaBox id*/
    public const INIT_NAME = App::ID . '_ai_chatter';
    public const TITLE = 'AI Chatter';
    public const CONTEXT = 'normal';
    public const PRIORITY = 'default';
    public const SCRIPT_NAME = 'ai_chatter';

    public function init(): void
    {
        $this->addAction(12, 2);
        $this->saveMetaBox(self::INIT_NAME);
    }

    public function execute(): void
    {
        add_meta_box(
            self::INIT_NAME,
            self::TITLE,
            [$this, parent::RENDER_METHOD],
            AiChatter::POST_TYPE,
            self::CONTEXT,
            self::PRIORITY
        );

        $postId = $this->getPostId();
        $this->addScripts(self::SCRIPT_NAME, $this->getData($postId));
    }

    public function saveCustomMetaBoxData($postId, $post, $update): void
    {
        if (isset($_POST[AiChatObject::FIELD_ID])) {
            $jsonString = $_POST[AiChatObject::FIELD_ID];

            error_log($jsonString);
            error_log('is valid: ' . Validation::isValidAiChatObject($jsonString));

            if (Validation::isValidAiChatObject($jsonString)) {
                update_post_meta($postId, AiChatObject::FIELD_ID, $jsonString);
            }
        }
    }

    public function render(): void
    {
        echo $this->removePostClass(self::INIT_NAME);
    }

    protected function getData($postId = null): array
    {
        return [
            'modelNameValues' => ModelNameObject::VALUES,
            'aiMemoriesValues' => AiChatterHelper::aiMemories(),
            'gdprCampaigns' => AiChatterHelper::gdprCampaigns(),
            'aiChat' => AiChatterHelper::aiChat($postId),
            'fieldName' => AiChatObject::FIELD_ID,
            'shortcode' => '[main-gpt ai-chatter-id="' . $postId . '"]',
            'gdprSemaphore' => Config::getOption(Config::OPTION_GDPR_SEMAPHORE),
        ];
    }
}
