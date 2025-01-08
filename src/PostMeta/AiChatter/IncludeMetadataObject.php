<?php

namespace MainGPT\PostMeta\AiChatter;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;

class IncludeMetadataObject
{
    public const FIELD_ID = App::ID . '_ai_chatter_include_metadata';
    public const DEFAULT_VALUE = true;
}
