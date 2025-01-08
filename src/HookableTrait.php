<?php

namespace MainGPT;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * This trait helps to implement AbstractHookable behaviour with minimum of code needed.
 *
 * to use it property you need to define two constant:
 * - INIT_NAME      that describes the init action or filter that we will hook the class
 * - METHOD_NAME    return the name of the method to be hooked, suggested default should be "execute"
 * that will be used to hook the class into WordPress
 */
trait HookableTrait
{
    public function getInitName(): string
    {
        return self::INIT_NAME;
    }

    public function getMethodName(): string
    {
        return self::METHOD_NAME;
    }
}
