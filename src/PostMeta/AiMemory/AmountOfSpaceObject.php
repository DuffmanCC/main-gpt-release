<?php

namespace MainGPT\PostMeta\AiMemory;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class AmountOfSpaceObject
{
    public const OPTIONS = ['small', 'medium', 'large'];
    public const DEFAULT = [
        'metric' => 'dotproduct',
        'podsNumber' => 1,
        'replicas' => 1,
        'podType' => 'p1.x1',
    ];
    public const PRESETS = [
        'small' => [
            'metric' => 'dotproduct',
            'podsNumber' => 1,
            'replicas' => 1,
            'podType' => 'p1.x1',
        ],
        'medium' => [
            'metric' => 'dotproduct',
            'podsNumber' => 2,
            'replicas' => 2,
            'podType' => 'p1.x2',
        ],
        'large' => [
            'metric' => 'dotproduct',
            'podsNumber' => 4,
            'replicas' => 3,
            'podType' => 'p2.x4',
        ],
    ];
}
