<?php

namespace MainGPT\MetaBox;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;
use MainGPT\MetaBox\AbstractMetaBoxable;
use MainGPT\PostMeta\AiCampaign\DurationObject;
use MainGPT\PostMeta\AiCampaign\InterestObject;
use MainGPT\PostMeta\AiCampaign\GdprTextObject;
use MainGPT\PostMeta\AiCampaign\IsActiveObject;
use MainGPT\PostMeta\AiCampaign\IsMandatoryObject;
use MainGPT\PostType\AiCampaign;
use MainGPT\Validation;
use MainGPT\Repository\Config;

final class AiCampaignBox extends AbstractMetaBoxable
{
    /** Used here as MetaBox id*/
    public const INIT_NAME = App::ID . '_ai_campaign';
    public const TITLE = 'Metabox Title';
    public const CONTEXT = 'normal';
    public const PRIORITY = 'default';
    public const SCRIPT_NAME = 'ai_campaign';

    public function init(): void
    {
        $this->addAction(12, 2);
        $this->saveMetaBox(self::INIT_NAME); // INIT_NAME is the same as the post type
    }

    public function execute(): void
    {
        add_meta_box(
            self::INIT_NAME,
            self::TITLE,
            [$this, parent::RENDER_METHOD],
            AiCampaign::POST_TYPE,
            self::CONTEXT,
            self::PRIORITY
        );

        $postId = $this->getPostId();
        $this->addScripts(self::SCRIPT_NAME, $this->getData($postId));
        // TODO
        // check if this hook is called when a post is deleted or trashed
        add_action('before_delete_post', [$this, 'deleteHook'], 10, 1);
    }

    public function saveCustomMetaBoxData(int $postId, \WP_Post $post, bool $update): void
    {
        if (isset($_POST[DurationObject::FIELD_ID]) && Validation::validateDurationCampaign($_POST[DurationObject::FIELD_ID])) {
            update_post_meta($postId, DurationObject::FIELD_ID, $_POST[DurationObject::FIELD_ID]);
        }

        if (isset($_POST[InterestObject::FIELD_ID])) {
            update_post_meta($postId, InterestObject::FIELD_ID, sanitize_text_field($_POST[InterestObject::FIELD_ID]));
        }

        if (isset($_POST[GdprTextObject::FIELD_ID])) {
            update_post_meta($postId, GdprTextObject::FIELD_ID, sanitize_textarea_field(
                $_POST[GdprTextObject::FIELD_ID]
            ));
        }

        if (isset($_POST[IsActiveObject::FIELD_ID]) && $_POST[IsActiveObject::FIELD_ID] === 'true') {
            update_post_meta($postId, IsActiveObject::FIELD_ID, true);
        } else {
            update_post_meta($postId, IsActiveObject::FIELD_ID, false);
        }

        if (isset($_POST[IsMandatoryObject::FIELD_ID]) && $_POST[IsMandatoryObject::FIELD_ID] === 'true') {
            update_post_meta($postId, IsMandatoryObject::FIELD_ID, true);
        } else {
            update_post_meta($postId, IsMandatoryObject::FIELD_ID, false);
        }
    }

    public function deleteHook($postId): void {}

    public function render(): void
    {
        echo $this->removePostClass(self::INIT_NAME);
    }

    protected function getData($postId = null): array
    {
        return [
            'duration' => DurationObject::FIELD_ID,
            'durationValue' => get_post_meta($postId, DurationObject::FIELD_ID, true),
            'interest' => InterestObject::FIELD_ID,
            'interestValue' => get_post_meta($postId, InterestObject::FIELD_ID, true),
            'gdprText' => GdprTextObject::FIELD_ID,
            'gdprTextValue' => get_post_meta($postId, GdprTextObject::FIELD_ID, true),
            'isActive' => IsActiveObject::FIELD_ID,
            'isActiveValue' => get_post_meta($postId, IsActiveObject::FIELD_ID, true) ? true : false,
            'isMandatory' => IsMandatoryObject::FIELD_ID,
            'isMandatoryValue' => get_post_meta($postId, IsMandatoryObject::FIELD_ID, true) ? true : false
        ];
    }
}
