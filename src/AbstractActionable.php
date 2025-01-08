<?php

namespace MainGPT;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Implement the executable behaviour for a class hooked to an action
 */
abstract class AbstractActionable extends AbstractExecutable
{
    public function init(): void
    {
        $this->addAction();
    }

    protected function setCorsHeaders(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST');
        header('Access-Control-Allow-Credentials: true');
    }
}
