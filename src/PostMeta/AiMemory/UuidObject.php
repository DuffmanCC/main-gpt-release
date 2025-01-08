<?php

namespace MainGPT\PostMeta\AiMemory;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;

class UuidObject
{
    public const FIELD_ID = App::ID . '_ai_memory_uuid';
}
