<?php

namespace MainGPT\MetaBox;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\AbstractRenderableAdminAction;
use MainGPT\HookableTrait;
use MainGPT\Repository\Config;

abstract class AbstractMetaBoxable extends AbstractRenderableAdminAction
{
    use HookableTrait;

    public const INIT_NAME = 'add_meta_boxes';
    protected $scriptName;

    public function getPostId(): int | null
    {
        global $post;
        return $post->ID;
    }

    protected function saveMetaBox(string $postType): void
    {
        add_action('save_post_' . $postType, [$this, 'saveCustomMetaBoxData'], 10, 3);
    }

    protected function removePostClass($name): string
    {
        return '            
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    $("#' . $name . '").removeClass("postbox");
                });
            </script>
        ';
    }

    protected function addScripts($name, $data): void
    {
        $this->scriptName = $name;
        $version = Config::getVersion();

        wp_enqueue_style('ai-style', plugins_url('main-gpt') . '/dist/wug-style.css', null, $version, 'all');
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

    abstract protected function getData($postId = null): array;
}
