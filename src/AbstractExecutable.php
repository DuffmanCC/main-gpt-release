<?php

namespace MainGPT;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * This abstract class implement the hookable behaviour using as default method name "execute".
 */
abstract class AbstractExecutable extends AbstractHookable
{
    public const METHOD_NAME = 'execute';

    /**
     * The method will be hooked at the init
     *
     * @return void
     */
    abstract public function execute(): void;
}
