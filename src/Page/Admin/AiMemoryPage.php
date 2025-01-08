<?php

namespace MainGPT\Page\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;
use MainGPT\Repository\Config;
use MainGPT\PostType\AiMemory;

final class AiMemoryPage extends AbstractAdminPage
{
    public const PAGE_TITLE = 'AiMemory';
    public const MENU_TITLE = 'AiMemory';
    public const CAPABILITY = 'manage_options';
    public const MENU_SLUG = 'edit.php?post_type=' . AiMemory::POST_TYPE;

    public function execute(): void
    {
        $isConfigured = Config::getOption(Config::OPTION_IS_PLUGIN_CONFIGURED) ? true : false;
        $isActive = Config::getOption(Config::OPTION_IS_ACTIVE_PLUGIN) ? true : false;

        if (!$isConfigured || !$isActive) return;

        add_submenu_page(
            MainAiPage::MENU_SLUG,
            __(self::PAGE_TITLE, App::TEXT_DOMAIN),
            __(self::MENU_TITLE, App::TEXT_DOMAIN),
            self::CAPABILITY,
            self::MENU_SLUG,
            '',
            2
        );
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function render(): void {}
}
