<?php

namespace MainGPT\PostMeta\AiCampaign;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;

class InterestObject
{
    public const FIELD_ID = App::ID . '_ai_campaign_interest';
    public const DEFAULT_VALUE = 'test';
}
