<?php

namespace MainGPT\Helpers;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\PostMeta\AiMemory\IndexNameObject;
use MainGPT\Service\PineconeClient;
use MainGPT\Repository\Config;
use MainGPT\PostType\AiMemory;
use MainGPT\PostMeta\AiChatter\AiChatObject;
use MainGPT\PostType\AiCampaign;
use MainGPT\PostMeta\AiCampaign\IsActiveObject;
use MainGPT\Validation;

final class AiChatterHelper
{
    /**
     * get a list of all pinecone indexes
     * 
     * @return array
     */
    public static function pineconeIndexes(): array
    {
        $config = new Config();
        $client = new \GuzzleHttp\Client();

        $pineconeClient = new PineconeClient(
            $config,
            $client
        );

        return $pineconeClient->listIndexes();
    }

    /**
     * get the aiMemories from the post meta
     * 
     * @return string JSON
     */
    public static function aiMemories(): array
    {
        $aiMemories = [];

        $aiMemoryObjects = get_posts(
            [
                'post_type' => AiMemory::POST_TYPE,
                'post_status' => 'publish',
            ]
        );

        foreach ($aiMemoryObjects as $memory) {
            $aiMemories[] = [
                'id' => $memory->ID,
                'title' => get_the_title($memory->ID),
                'status' => AiMemoryHelper::indexStatus($memory->ID),
                'name' => get_post_meta($memory->ID, IndexNameObject::FIELD_ID, true),
            ];
        }

        // filter ony if status is trained
        $aiMemories = array_filter($aiMemories, function ($memory) {
            return $memory['status'] === 'trained';
        });

        return $aiMemories;
    }

    /**
     * get the aiChat JSON from the post meta and 
     * return the default values if the field is empty
     * 
     * @return array
     */
    public static function aiChat($postId): array
    {
        $jsonString = get_post_meta($postId, AiChatObject::FIELD_ID, true);

        // at creation, the field is empty and the default values are used
        if (empty($jsonString) || !Validation::isValidAiChatObject($jsonString, false)) {
            return AiChatObject::DEFAULT_VALUE;
        }

        return json_decode($jsonString, true);
    }

    /**
     * get the all the GDPR active campaigns custom post type
     * 
     * @return string JSON
     */
    public static function gdprCampaigns(): array
    {
        $gdprCampaigns = [];

        $gdprCampaignObjects = get_posts(
            [
                'post_type' => AiCampaign::POST_TYPE,
                'post_status' => 'publish',
            ]
        );

        foreach ($gdprCampaignObjects as $campaign) {
            $isActive = get_post_meta($campaign->ID, IsActiveObject::FIELD_ID, true);

            if (!$isActive) {
                continue;
            }

            $gdprCampaigns[] = [
                'id' => $campaign->ID,
                'title' => get_the_title($campaign->ID)
            ];
        }

        return $gdprCampaigns;
    }
}
