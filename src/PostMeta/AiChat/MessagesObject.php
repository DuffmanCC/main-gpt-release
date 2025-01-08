<?php

namespace MainGPT\PostMeta\AiChat;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;

class MessagesObject
{
    public const FIELD_ID = App::ID . '_ai_chat_messages';
}
