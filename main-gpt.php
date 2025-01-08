<?php

/**
 * Main GPT Plugin
 *
 * @package           Main GPT
 * @author            Carlos Ortiz
 * @copyright         2024 Carlos Ortiz
 * @license           LGPL-3.0-only
 *
 * Plugin Name:       Main GPT
 * Plugin URI:        https://main-ai-headless-next-js.vercel.app/
 * Description:       Integrate generative AI to your business to endless possibilities
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Carlos Ortiz
 * Author URI:        https://duffmancc.github.io/web-cv/
 * Text Domain:       main-gpt
 * License:           LGPL-3.0-only
 * License URI:       https://www.gnu.org/licenses/lgpl-3.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

if (file_exists(plugin_dir_path(__FILE__) . 'vendor/autoload.php')) {
    require plugin_dir_path(__FILE__) . 'vendor/autoload.php';
} else {
    error_log("Error locating autoloader. Please run composer install.");
    return;
}

use MainGPT\App;
use MainGPT\Repository\Config;

// Register activation hook
register_activation_hook(__FILE__, function () {
    $optionExists = Config::getOption(Config::OPTION_IS_PLUGIN_CONFIGURED);

    if ($optionExists === null) {
        Config::setOption(Config::OPTION_IS_PLUGIN_CONFIGURED, false);
    }
});

// Register deactivation hook
register_uninstall_hook(__FILE__, 'main_gpt_uninstall');

function main_gpt_uninstall()
{
    delete_option(Config::getOptionsName());
}

// add the admin settings page in the plugins list
add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=main-gpt-settings') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
});

new App(plugin_dir_path(__FILE__));
