<?php

namespace MainGPT\Shortcode;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\AbstractHookable;
use MainGPT\Repository\Config;

/**
 * This abstract class describe a shortcodable call
 * The INIT_NAME will represent the shortcode name
 */
abstract class AbstractShortcodable extends AbstractHookable
{
    public const METHOD_NAME = 'execute';
    protected $scriptName;

    public function init(): void
    {
        $this->addShortcode();
    }

    protected function addScripts($name, $data): void
    {
        $this->scriptName = $name;
        $version = Config::getVersion();

        wp_enqueue_style('ai-style', plugins_url('main-gpt') . '/dist/wug-style.css', null, $version, 'all');
        wp_enqueue_style('ai-chat-style', plugins_url('main-gpt') . '/dist/chat_gpt.css', null, $version, 'all');
        wp_enqueue_script($name . '-script', plugins_url('main-gpt/dist/') . $name . '.js', null, $version, true);

        // we need a nested array passing the data to get boolean values
        wp_localize_script($name . '-script', $name, ['data' => $data]);
        add_filter('script_loader_tag', [$this, 'addModuleToScript'], 10, 2);
    }

    /**
     * Add the type="module" attribute
     */
    public function addModuleToScript($tag, $handle): string
    {
        if ($handle !== $this->scriptName . '-script') {
            return $tag;
        }

        return str_replace(' src', ' type="module" src', $tag);
    }

    /**
     * The method will be executed at the shortcode call
     * It has to return the shortcode content as a string
     *
     * @param mixed $attributes
     * @param mixed $content
     * @return string
     */
    abstract public function execute(mixed $attributes, mixed $content = ""): string;
}
