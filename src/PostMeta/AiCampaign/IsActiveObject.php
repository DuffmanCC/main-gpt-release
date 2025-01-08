<?php

namespace MainGPT\PostMeta\AiCampaign;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;

class IsActiveObject
{
    public const FIELD_ID = App::ID . '_ai_campaign_is_active';
    public const DEFAULT_VALUE = false;
}
