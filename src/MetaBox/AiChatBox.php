<?php

namespace MainGPT\MetaBox;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;
use MainGPT\MetaBox\AbstractMetaBoxable;
use MainGPT\PostMeta\AiChat\TimestampObject;
use MainGPT\PostMeta\AiChat\CampaignsObject;
use MainGPT\PostMeta\AiChat\MessagesObject;
use MainGPT\PostType\AiChat;
use MainGPT\Repository\Config;

final class AiChatBox extends AbstractMetaBoxable
{
    /** Used here as MetaBox id*/
    public const INIT_NAME = App::ID . '_ai_chat';
    public const TITLE = 'Metabox Title';
    public const CONTEXT = 'normal';
    public const PRIORITY = 'default';
    public const SCRIPT_NAME = 'ai_chat';

    public function init(): void
    {
        $this->addAction(12, 2);
    }

    public function execute(): void
    {
        add_meta_box(
            self::INIT_NAME,
            self::TITLE,
            [$this, parent::RENDER_METHOD],
            AiChat::POST_TYPE,
            self::CONTEXT,
            self::PRIORITY
        );

        $postId = $this->getPostId();
        $this->addScripts(self::SCRIPT_NAME, $this->getData($postId));
    }

    public function render(): void
    {
        echo $this->removePostClass(self::INIT_NAME);
    }

    protected function getData($postId = null): array
    {
        $content = get_post_meta($postId, MessagesObject::FIELD_ID, true);
        $content = base64_decode($content);
        $content = json_decode($content, true);

        return [
            'timestamp' => TimestampObject::FIELD_ID,
            'timestampValue' => get_post_meta($postId, TimestampObject::FIELD_ID, true),
            'campaigns' => CampaignsObject::FIELD_ID,
            'campaignsValue' => get_post_meta($postId, CampaignsObject::FIELD_ID, true),
            'messages' => MessagesObject::FIELD_ID,
            'messagesValue' => $content,
        ];
    }
}
