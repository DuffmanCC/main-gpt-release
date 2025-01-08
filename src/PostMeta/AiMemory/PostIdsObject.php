<?php

namespace MainGPT\PostMeta\AiMemory;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;

class PostIdsObject
{
    public const FIELD_ID = App::ID . '_ai_memory_post_ids';
}
