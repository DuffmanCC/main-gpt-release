<?php

namespace MainGPT\Page\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\Ajax\Admin\PluginSettingsAjax;
use MainGPT\App;
use MainGPT\Repository\Config;

final class AiSettingsPage extends AbstractAdminPage
{
    public const PAGE_TITLE = 'AiSettings';
    public const MENU_TITLE = 'AiSettings';
    public const CAPABILITY = 'manage_options';
    public const MENU_SLUG = 'main-gpt-settings';
    public const METHOD_RENDERER = 'render';
    public const ICON = 'dashicons-schedule';
    public const SCRIPT_NAME = 'ai_settings';

    public function execute(): void
    {
        add_submenu_page(
            MainAiPage::MENU_SLUG,
            __(self::PAGE_TITLE, App::TEXT_DOMAIN),
            __(self::MENU_TITLE, App::TEXT_DOMAIN),
            self::CAPABILITY,
            self::MENU_SLUG,
            [$this, self::METHOD_RENDERER],
            7,
            self::ICON,
        );

        $version = Config::getVersion();

        wp_enqueue_style('ai-style', plugins_url('main-gpt') . '/dist/wug-style.css', null, $version, 'all');
        wp_enqueue_script(self::SCRIPT_NAME . '-script', plugins_url('main-gpt') . '/dist/' . self::SCRIPT_NAME . '.js', null, $version, true);

        // we need a nested array passing the data to get boolean values
        wp_localize_script(self::SCRIPT_NAME . '-script', self::SCRIPT_NAME, ['data' => $this->getData()]);
        add_filter('script_loader_tag', [$this, 'addModuleToScript'], 10, 2);
    }

    /**
     * @throws RuntimeError
     * @throws SyntaxError
     * @throws LoaderError
     */
    public function render(): void
    {
        echo '<div id="main_ai_settings"></div>';
    }

    /**
     * Add the type="module" attribute
     */
    public function addModuleToScript($tag, $handle): string
    {
        if ($handle !== self::SCRIPT_NAME . '-script') {
            return $tag;
        }

        return str_replace(' src', ' type="module" src', $tag);
    }

    public function getData(): array
    {
        return [
            'pageTitle' => self::PAGE_TITLE,
            'openAiApiKey' => Config::getOption(Config::OPTION_OPENAI_API_KEY),
            'openAiOrg' => Config::getOption(Config::OPTION_OPENAI_ORGANIZATION),
            'pineconeApiKey' => Config::getOption(Config::OPTION_PINECONE_API_KEY),
            'ajaxAction' => PluginSettingsAjax::AJAX_ACTION,
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'ajaxNonce' => wp_create_nonce(PluginSettingsAjax::AJAX_ACTION),
            'arePineconeCredentialsValid' => Config::getOption(Config::OPTION_ARE_PINECONE_CREDENTIALS_VALID),
            'areOpenAiCredentialsValid' => Config::getOption(Config::OPTION_ARE_OPENAI_CREDENTIALS_VALID),
            'isActivePlugin' => Config::getOption(Config::OPTION_IS_ACTIVE_PLUGIN),
            'gdprSemaphore' => Config::getOption(Config::OPTION_GDPR_SEMAPHORE),
        ];
    }
}
