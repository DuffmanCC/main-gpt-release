<?php

namespace MainGPT\PostMeta\AiChatter;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;

class PrimerPromptObject
{
    public const FIELD_ID = App::ID . '_ai_chatter_primer_prompt';
    public const DEFAULT_VALUE = "You are a Q&A bot. A highly intelligent system that answers " .
        "user questions based first, on the information provided by the user above each question " .
        "and only secondary on your knowledge. " .
        "If the information can not be found you truthfully say: \"I don't know.\"." .
        "You should format the answer in HTML.";
}
