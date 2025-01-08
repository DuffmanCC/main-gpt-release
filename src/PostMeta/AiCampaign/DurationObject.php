<?php

namespace MainGPT\PostMeta\AiCampaign;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;

class DurationObject
{
    public const FIELD_ID = App::ID . '_ai_campaign_duration';
    public const DEFAULT_VALUE = 6;
}
