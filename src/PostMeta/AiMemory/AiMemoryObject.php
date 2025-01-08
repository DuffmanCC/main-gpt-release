<?php

namespace MainGPT\PostMeta\AiMemory;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;

class AiMemoryObject
{
    public const FIELD_ID = App::ID . '_ai_memory_ai_memory';
    public const DEFAULT_VALUE = [
        "metric" => "dotproduct",
    ];

    public static function jsonSchema()
    {
        return json_decode(file_get_contents(WP_PLUGIN_DIR . '/main-gpt/src/Json/AiMemoryJsonSchema.json'), true);
    }
}
