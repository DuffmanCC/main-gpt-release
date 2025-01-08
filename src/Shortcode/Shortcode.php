<?php

namespace MainGPT\Shortcode;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\Ajax\Public\ChatBotAjax;
use MainGPT\Ajax\Public\CreateAiChatAjax;
use MainGPT\Ajax\Public\CreateAiContactAjax;
use MainGPT\Ajax\Public\RecordAiChatAjax;
use MainGPT\App;
use MainGPT\HookableTrait;
use MainGPT\PostMeta\AiChatter\AiChatObject;
use MainGPT\PostMeta\AiChat\CampaignsObject as AiChatCampaignsObject;
use MainGPT\Repository\Config;
use MainGPT\Helpers\ShortcodeHelper;
use MainGPT\PostType\AiChatter;

final class Shortcode extends AbstractShortcodable
{
    use HookableTrait;

    public const INIT_NAME = 'main-gpt';
    public const QUERY_FIELD = App::ID . '_main_gpt_query';
    public const SCRIPT_NAME = 'chat_gpt';

    public function init(): void
    {
        $isConfigured = Config::getOption(Config::OPTION_IS_PLUGIN_CONFIGURED) ? true : false;
        $isActive = Config::getOption(Config::OPTION_IS_ACTIVE_PLUGIN) ? true : false;

        if (!$isConfigured || !$isActive) return;

        $this->addShortcode();

        add_action('rest_api_init', [$this, 'registerEndpoint']);
    }

    /**
     * @param mixed $attributes
     * @param mixed $content
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function execute(mixed $attributes, mixed $content = ''): string
    {
        $id = '';

        if (isset($attributes['ai-chatter-id'])) {
            $id = $attributes['ai-chatter-id'];
        }

        // check if id exists on db
        if (!get_post($id)) {
            return '';
        }

        $this->addScripts(self::SCRIPT_NAME, $this->getData($id));

        return '<div id="wug_chat_gpt"></div>';
    }

    private function getAiChat(string $id): object | null
    {
        $aiChat = get_post_meta($id, AiChatObject::FIELD_ID, true);

        return json_decode($aiChat);
    }

    public function registerEndpoint()
    {
        register_rest_route(
            'wp/v2',
            '/main-gpt-chat/(?P<id>\d+)',
            [
                'methods'  => 'GET',
                'callback' => [$this, 'endpointCallback'],
                'args' => [
                    'id' => [
                        'validate_callback' => function ($param) {
                            return is_numeric($param);
                        }
                    ],
                ],
                'permission_callback' => '__return_true'
            ]
        );
    }

    public function endpointCallback($request)
    {
        $id = (int) $request['id'];

        // check if is a valid aiChatter ID
        if (get_post_type($id) !== AiChatter::POST_TYPE) {
            $id = false;
        }

        $response = [
            'message' => 'chatbot endpoint working!',
            'data' => $id ? $this->getData($id) : false,
            'error' => $id ? false : 'Id ' . $id . ' not found'
        ];

        return new \WP_REST_Response($response, 200);
    }

    private function getData(string $postId): array
    {
        if (!$postId) {
            return [];
        }

        $aiChat = ShortcodeHelper::getAiChat($postId);

        if (!$aiChat) {
            return [];
        }

        // TODO: check key names
        return [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'ajaxActionChatBot' => ChatBotAjax::AJAX_ACTION,
            'ajaxActionCreateAiChat' => CreateAiChatAjax::AJAX_ACTION,
            'ajaxActionCreateAiContact' => CreateAiContactAjax::AJAX_ACTION,
            'ajaxActionRecordAiChat' => RecordAiChatAjax::AJAX_ACTION,
            'nonceChatBot' => wp_create_nonce(ChatBotAjax::AJAX_ACTION),
            'nonceAiChat' => wp_create_nonce(CreateAiChatAjax::AJAX_ACTION),
            'nonceAiContact' => wp_create_nonce(CreateAiContactAjax::AJAX_ACTION),
            'nonceRecordAiChat' => wp_create_nonce(RecordAiChatAjax::AJAX_ACTION),
            'isActive' => $aiChat->isActive,
            'initMessage' => $aiChat->initMessage,
            'allowAiChatCampaigns' => $aiChat->allowAiChatCampaigns,
            'allowAiContactCampaigns' => $aiChat->allowAiContactCampaigns,
            'queryField' => self::QUERY_FIELD,
            'aiChatterId' => $postId,
            'aiChatCampaigns' => ShortcodeHelper::aiChatCampaigns($aiChat),
            'aiContactCampaigns' => ShortcodeHelper::aiContactCampaigns($aiChat),
            'campaigns' => ShortcodeHelper::campaignsModals($aiChat),
            'questionsLimit' => $aiChat->questionsLimit,
            'beforeFormMessage' => $aiChat->beforeFormMessage,
            'afterFormMessageSuccess' => $aiChat->afterFormMessageSuccess,
            'afterFormMessageError' => $aiChat->afterFormMessageError,
            'formFields' => $aiChat->formFields,
            'aiChatCampaignsField' => AiChatCampaignsObject::FIELD_ID,
            'hasAiChat' => $aiChat->hasAiChat,
            'limitChat' => $aiChat->limitChat,
        ];
    }
}
