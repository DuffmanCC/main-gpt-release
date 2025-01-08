<?php

namespace MainGPT\Repository;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;

final class Config
{
    public const PREFIX = 'main-gpt_';
    public const SUFFIX = '_settings';
    public const OPTION_OPENAI_API_KEY = 'openai_api_key';
    public const OPTION_OPENAI_ORGANIZATION = 'openai_organization';
    public const OPTION_PINECONE_API_KEY = 'pinecone_api_key';
    public const OPTION_IS_ACTIVE_PLUGIN = 'is_active_plugin';
    public const OPTION_GDPR_SEMAPHORE = 'gdpr_semaphore';
    public const OPTION_ARE_PINECONE_CREDENTIALS_VALID = 'are_pinecone_credentials_valid';
    public const OPTION_ARE_OPENAI_CREDENTIALS_VALID = 'are_openai_credentials_valid';
    public const OPTION_IS_PLUGIN_CONFIGURED = 'is_plugin_configured';
    public const ALLOWED_OPTIONS = [
        self::OPTION_OPENAI_API_KEY,
        self::OPTION_OPENAI_ORGANIZATION,
        self::OPTION_PINECONE_API_KEY,
        self::OPTION_IS_ACTIVE_PLUGIN,
        self::OPTION_GDPR_SEMAPHORE,
        self::OPTION_ARE_PINECONE_CREDENTIALS_VALID,
        self::OPTION_ARE_OPENAI_CREDENTIALS_VALID,
        self::OPTION_IS_PLUGIN_CONFIGURED
    ];

    static public function getOptions(): false|array
    {
        return get_option(self::getOptionsName(), []);
    }

    /**
     * @param string $optionName
     * @return bool
     */
    static public function getOption(string $optionName): mixed
    {
        $options = self::getOptions();

        if (!array_key_exists($optionName, $options)) {
            error_log(__FILE__ . ':' . __LINE__ . " | config option name doesn't exists: " . $optionName);
            return null;
        }

        return $options[$optionName];
    }

    static public function setOptions(array $options, $autoload = null): bool
    {
        return update_option(self::getOptionsName(), $options);
    }

    static public function setOption(string $optionName, mixed $optionValue, $autoload = null): bool
    {
        $options = self::getOptions();
        $options[$optionName] = $optionValue;
        return self::setOptions($options, $autoload);
    }

    static public function getOptionsName(): string
    {
        return self::PREFIX . App::ID . self::SUFFIX;
    }

    static public function getVersion(): string | null
    {
        $composerJson = file_get_contents(WP_PLUGIN_DIR . '/main-gpt/composer.json');
        $composerData = json_decode($composerJson, true);

        return $composerData['version'];
    }
}
