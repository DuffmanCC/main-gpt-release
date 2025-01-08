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
final class AiContact extends AbstractOnInit
{
    use HookableTrait;

    public const INIT_NAME = 'ai-contact';
    public const POST_TYPE = App::ID . '_ai_contact';

    public function execute(): void
    {
        $labels = [
            'name'                  => _x('AiContacts', 'AiContacts General Name', App::TEXT_DOMAIN),
            'singular_name'         => _x('AiContact', 'AiContact Singular Name', App::TEXT_DOMAIN),
            'menu_name'             => __('AiContacts', App::TEXT_DOMAIN),
            'name_admin_bar'        => __('AiContact', App::TEXT_DOMAIN),
            'archives'              => __('AiContact Archives', App::TEXT_DOMAIN),
            'attributes'            => __('AiContact Attributes', App::TEXT_DOMAIN),
            'parent_item_colon'     => __('Parent AiContact:', App::TEXT_DOMAIN),
            'all_items'             => __('All AiContacts', App::TEXT_DOMAIN),
            'add_new_item'          => __('Add New AiContact', App::TEXT_DOMAIN),
            'add_new'               => __('Add New', App::TEXT_DOMAIN),
            'new_item'              => __('New AiContact', App::TEXT_DOMAIN),
            'edit_item'             => __('Edit AiContact', App::TEXT_DOMAIN),
            'update_item'           => __('Update AiContact', App::TEXT_DOMAIN),
            'view_item'             => __('View AiContact', App::TEXT_DOMAIN),
            'view_items'            => __('View AiContacts', App::TEXT_DOMAIN),
            'search_items'          => __('Search AiContact', App::TEXT_DOMAIN),
            'not_found'             => __('Not found', App::TEXT_DOMAIN),
            'not_found_in_trash'    => __('Not found in Trash', App::TEXT_DOMAIN),
            'featured_image'        => __('Featured Image', App::TEXT_DOMAIN),
            'set_featured_image'    => __('Set featured image', App::TEXT_DOMAIN),
            'remove_featured_image' => __('Remove featured image', App::TEXT_DOMAIN),
            'use_featured_image'    => __('Use as featured image', App::TEXT_DOMAIN),
            'insert_into_item'      => __('Insert into AiMemory', App::TEXT_DOMAIN),
            'uploaded_to_this_item' => __('Uploaded to this AiContact', App::TEXT_DOMAIN),
            'items_list'            => __('AiContacts list', App::TEXT_DOMAIN),
            'items_list_navigation' => __('AiContacts list navigation', App::TEXT_DOMAIN),
            'filter_items_list'     => __('Filter AiContacts list', App::TEXT_DOMAIN),
        ];

        $args = [
            'label'                 => __('AiContacts', App::TEXT_DOMAIN),
            'description'           => __('AiContacts', App::TEXT_DOMAIN),
            'labels'                => $labels,
            'supports'              => ['title'],
            'taxonomies'            => [],
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => current_user_can('manage_options'),
            'show_in_menu'          => false,
            'menu_icon'             => 'dashicons-admin-users',
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
