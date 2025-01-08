<?php

namespace MainGPT;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

abstract class AbstractRenderableAction extends AbstractActionable
{
    public const RENDER_METHOD = 'render';

    /**
     * Use this method to render the page
     *
     * @return void
     */
    abstract public function render(): void;
}
