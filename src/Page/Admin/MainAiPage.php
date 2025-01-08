<?php

namespace MainGPT\Page\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly


use MainGPT\App;

final class MainAiPage extends AbstractAdminPage
{
    public const PAGE_TITLE = 'Main AI';
    public const MENU_TITLE = 'Main AI';
    public const CAPABILITY = 'manage_options';
    public const MENU_SLUG = 'main-gpt-settings';

    public function execute(): void
    {
        add_menu_page(
            __(self::PAGE_TITLE, App::TEXT_DOMAIN),
            __(self::MENU_TITLE, App::TEXT_DOMAIN),
            self::CAPABILITY,
            self::MENU_SLUG,
            null,
            plugin_dir_url(__FILE__) . 'Icon.svg',
        );

        add_action('admin_head', function () {
            echo '<style>
                #toplevel_page_main-gpt-settings img {
                    position: absolute !important;
                    bottom: 6px !important;
                    left: 6px !important;
                    padding: 0 !important;
                }

                #toplevel_page_main-gpt-settings img:hover {
                    transition: all 0.3s ease-in-out;
                    rotate: 90deg;
                  }
            </style>';
        });
    }

    public function render(): void {}
}
