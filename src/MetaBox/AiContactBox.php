<?php

namespace MainGPT\MetaBox;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;
use MainGPT\MetaBox\AbstractMetaBoxable;
use MainGPT\PostMeta\AiContact\AiChatIdObject;
use MainGPT\PostMeta\AiContact\CreationDateObject;
use MainGPT\PostMeta\AiContact\EmailObject;
use MainGPT\PostMeta\AiContact\CampaignsObject;
use MainGPT\PostMeta\AiContact\MessageObject;
use MainGPT\PostMeta\AiContact\NameObject;
use MainGPT\PostMeta\AiContact\PhoneObject;
use MainGPT\PostType\AiContact;
use MainGPT\Repository\Config;

final class AiContactBox extends AbstractMetaBoxable
{
    /** Used here as MetaBox id*/
    public const INIT_NAME = App::ID . '_ai_contact';
    public const TITLE = 'Metabox Title';
    public const CONTEXT = 'normal';
    public const PRIORITY = 'default';
    public const SCRIPT_NAME = 'ai_contact';

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
            AiContact::POST_TYPE,
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
        return [
            'aiChatId' => AiChatIdObject::FIELD_ID,
            'aiChatIdValue' => get_post_meta($postId, AiChatIdObject::FIELD_ID, true),
            'creationDate' => CreationDateObject::FIELD_ID,
            'creationDateValue' => get_post_meta($postId, CreationDateObject::FIELD_ID, true),
            'email' => EmailObject::FIELD_ID,
            'emailValue' => get_post_meta($postId, EmailObject::FIELD_ID, true),
            'campaigns' => CampaignsObject::FIELD_ID,
            'campaignsValue' => get_post_meta($postId, CampaignsObject::FIELD_ID, true),
            'message' => MessageObject::FIELD_ID,
            'messageValue' => get_post_meta($postId, MessageObject::FIELD_ID, true),
            'name' => NameObject::FIELD_ID,
            'nameValue' => get_post_meta($postId, NameObject::FIELD_ID, true),
            'phone' => PhoneObject::FIELD_ID,
            'phoneValue' => get_post_meta($postId, PhoneObject::FIELD_ID, true),
        ];
    }
}
