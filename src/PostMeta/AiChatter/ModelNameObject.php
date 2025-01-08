<?php

namespace MainGPT\PostMeta\AiChatter;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;

class ModelNameObject
{
    public const FIELD_ID = App::ID . '_ai_chatter_model_name';
    public const GPT3 = 'gpt-3.5-turbo';
    public const GPT4 = 'gpt-4';
    public const DEFAULT_VALUE = self::GPT3;
    public const VALUES = [self::GPT3, self::GPT4];
}
