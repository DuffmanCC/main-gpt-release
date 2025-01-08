<?php

namespace MainGPT\PostMeta\AiChatter;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;

class QuestionsLimitObject
{
    public const FIELD_ID = App::ID . '_ai_chatter_questions_limit';
    public const DEFAULT_VALUE = 5;
}
