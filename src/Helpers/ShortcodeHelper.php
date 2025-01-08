<?php

namespace MainGPT\Helpers;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


use MainGPT\PostMeta\AiCampaign\GdprTextObject;
use MainGPT\PostMeta\AiCampaign\IsMandatoryObject;
use MainGPT\PostMeta\AiChatter\AiChatObject;

final class ShortcodeHelper
{
    public static function getAiChat(string $id): object | null
    {
        $aiChat = get_post_meta($id, AiChatObject::FIELD_ID, true);

        return json_decode($aiChat);
    }

    public static function aiChatCampaigns(object $aiChat): array
    {
        $campaignIds = $aiChat->aiChatCampaigns;

        $campaigns = [];

        foreach ($campaignIds as $campaignId) {
            $campaigns[] = [
                'id' => trim($campaignId),
                'name' => get_the_title($campaignId),
                'isMandatory' => get_post_meta($campaignId, IsMandatoryObject::FIELD_ID, true) ? true : false
            ];
        }

        return $campaigns;
    }

    public static function aiContactCampaigns(object $aiChat): array
    {
        $campaignIds = $aiChat->aiContactCampaigns;

        $campaigns = [];

        foreach ($campaignIds as $campaignId) {
            $campaigns[] = [
                'id' => trim($campaignId),
                'name' => get_the_title($campaignId),
                'isMandatory' => get_post_meta($campaignId, IsMandatoryObject::FIELD_ID, true) ? true : false
            ];
        }

        return $campaigns;
    }

    public static function campaignsModals(object $aiChat): array
    {
        $aiCampaignIds = $aiChat->aiChatCampaigns;
        $aiContactCampaignIds = $aiChat->aiContactCampaigns;

        $campaignIds = array_merge(
            $aiCampaignIds,
            $aiContactCampaignIds
        );

        $campaignIds = array_unique($campaignIds);

        $campaigns = [];

        foreach ($campaignIds as $campaignId) {
            if ($campaignId !== '') {
                $campaigns[] = [
                    'id' => trim($campaignId),
                    'name' => get_the_title($campaignId),
                    'isMandatory' => get_post_meta($campaignId, IsMandatoryObject::FIELD_ID, true) ? true : false,
                    'content' => get_post_meta($campaignId, GdprTextObject::FIELD_ID, true),
                ];
            }
        }

        return $campaigns;
    }
}
