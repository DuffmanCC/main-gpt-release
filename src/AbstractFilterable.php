<?php

namespace MainGPT;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Implement the executable behaviour for a class hooked to a filter
 */
abstract class AbstractFilterable extends AbstractExecutable
{
    public function init(): void
    {
        $this->addFilter();
    }
}
