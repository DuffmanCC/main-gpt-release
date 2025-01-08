<?php

namespace MainGPT\PostMeta\AiContact;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;

class CreationDateObject
{
    public const FIELD_ID = App::ID . '_ai_contact_creation_date';
}
