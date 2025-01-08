<?php

namespace MainGPT;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * This interface describe a class able to be init into WordPress
 */
interface InitableInterface
{
    /**
     * Register the method execute into the WordPress framework as an action, filter, etc.
     *
     * @return void
     */
    public function init(): void;
}
