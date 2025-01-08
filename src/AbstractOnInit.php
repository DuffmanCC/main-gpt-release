<?php

namespace MainGPT;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

abstract class AbstractOnInit extends AbstractExecutable
{
    public function init(): void
    {
        $this->execute();
    }
}
