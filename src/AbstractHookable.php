<?php

namespace MainGPT;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * This abstract class describe a single purpose object able to be hooked into WordPress
 * and implement the Initable Interface
 */
abstract class AbstractHookable implements InitableInterface
{
    /**
     * Return the name of the hook used to connect our class
     *
     * @return string
     */
    abstract public function getInitName(): string;

    /**
     * Return the name of the method that will be hooked to WordPress and represent the purpose of the class
     *
     * @return string
     */
    abstract public function getMethodName(): string;

    /**
     * Add the class as an action to WordPress
     *
     * @param int $priority
     * @param int $acceptedArgs
     * @return void
     */
    public function addAction(int $priority = 10, int $acceptedArgs = 1): void
    {
        add_action(
            $this->getInitName(),
            [$this, $this->getMethodName()],
            $priority,
            $acceptedArgs
        );
    }

    /**
     * Add the class as a filter to WordPress
     *
     * @param int $priority
     * @param int $acceptedArgs
     * @return void
     */
    public function addFilter(int $priority = 10, int $acceptedArgs = 1): void
    {
        add_filter(
            $this->getInitName(),
            [$this, $this->getMethodName()],
            $priority,
            $acceptedArgs
        );
    }

    /**
     * Add the class as a shortcode to WordPress
     * The shortcode method has to return a string
     *
     * @return void
     */
    public function addShortcode(): void
    {
        add_shortcode($this->getInitName(), [$this, $this->getMethodName()]);
    }
}
