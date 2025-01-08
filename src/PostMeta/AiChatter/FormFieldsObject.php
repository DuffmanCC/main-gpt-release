<?php

namespace MainGPT\PostMeta\AiChatter;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;

class FormFieldsObject
{
    public const FIELD_ID = App::ID . '_ai_chatter_form_fields';
    public const DEFAULT_VALUE = [];
}
