<?php

namespace MainGPT\PostType;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\AbstractOnInit;
use MainGPT\App;
use MainGPT\HookableTrait;

/**
 * @note This class has a high technical debt.
 * We need to fix the values of this custom post type in order to have a correct meaning.
 * (I mean $labels and $args)
 * @todo It will need refactor.
 */
final class AiChat extends AbstractOnInit
{
    use HookableTrait;

    public const INIT_NAME = 'ai-chat';
    public const POST_TYPE = App::ID . '_ai_chat';

    public function execute(): void
    {
        $labels = [
            'name'                  => _x('AiChats', 'AiChat General Name', App::TEXT_DOMAIN),
            'singular_name'         => _x('AiChat', 'AiChat Singular Name', App::TEXT_DOMAIN),
            'menu_name'             => __('AiChats', App::TEXT_DOMAIN),
            'name_admin_bar'        => __('AiChat', App::TEXT_DOMAIN),
            'archives'              => __('AiChat Archives', App::TEXT_DOMAIN),
            'attributes'            => __('AiChat Attributes', App::TEXT_DOMAIN),
            'parent_item_colon'     => __('Parent AiChat:', App::TEXT_DOMAIN),
            'all_items'             => __('All AiChats', App::TEXT_DOMAIN),
            'add_new_item'          => __('Add New AiChat', App::TEXT_DOMAIN),
            'add_new'               => __('Add New', App::TEXT_DOMAIN),
            'new_item'              => __('New AiChat', App::TEXT_DOMAIN),
            'edit_item'             => __('Edit AiChat', App::TEXT_DOMAIN),
            'update_item'           => __('Update AiChat', App::TEXT_DOMAIN),
            'view_item'             => __('View AiChat', App::TEXT_DOMAIN),
            'view_items'            => __('View AiChats', App::TEXT_DOMAIN),
            'search_items'          => __('Search AiChat', App::TEXT_DOMAIN),
            'not_found'             => __('Not found', App::TEXT_DOMAIN),
            'not_found_in_trash'    => __('Not found in Trash', App::TEXT_DOMAIN),
            'featured_image'        => __('Featured Image', App::TEXT_DOMAIN),
            'set_featured_image'    => __('Set featured image', App::TEXT_DOMAIN),
            'remove_featured_image' => __('Remove featured image', App::TEXT_DOMAIN),
            'use_featured_image'    => __('Use as featured image', App::TEXT_DOMAIN),
            'insert_into_item'      => __('Insert into AiMemory', App::TEXT_DOMAIN),
            'uploaded_to_this_item' => __('Uploaded to this AiChat', App::TEXT_DOMAIN),
            'items_list'            => __('AiChats list', App::TEXT_DOMAIN),
            'items_list_navigation' => __('AiChats list navigation', App::TEXT_DOMAIN),
            'filter_items_list'     => __('Filter AiChats list', App::TEXT_DOMAIN),
        ];

        $args = [
            'label'                 => __('AiChat', App::TEXT_DOMAIN),
            'description'           => __('AiChat', App::TEXT_DOMAIN),
            'labels'                => $labels,
            'supports'              => ['title'],
            'taxonomies'            => [],
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => current_user_can('manage_options'),
            'show_in_menu'          => false,
            'menu_icon'             => 'dashicons-format-chat',
            'show_in_admin_bar'     => false,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => false,
            'publicly_queryable'    => false,
            'capability_type'       => 'post',
            'capabilities' => [
                'create_posts' => 'do_not_allow', // Removes support for the "Add New" function
            ],
            'map_meta_cap' => true,
            'rewrite'               => ['slug' => self::INIT_NAME]
        ];

        register_post_type(self::POST_TYPE, $args);
    }
}
