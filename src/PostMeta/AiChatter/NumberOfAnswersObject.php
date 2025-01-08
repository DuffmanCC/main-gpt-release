<?php

namespace MainGPT\PostMeta\AiChatter;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;

class NumberOfAnswersObject
{
    public const FIELD_ID = App::ID . '_ai_chatter_number_of_answers';
    public const DEFAULT_VALUE = 5;
}
