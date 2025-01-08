<?php

namespace MainGPT\PostMeta\AiChatter;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\App;

class AiChatObject
{
    public const FIELD_ID = App::ID . '_ai_chatter_ai_chat';

    public const DEFAULT_VALUE = [
        "isActive" => false,
        "modelName" => "gpt-4",
        "pineconeIndex" => "",
        "primerPrompt" => "You are a Q&A bot. A highly intelligent system that answers user questions based first, on the information provided by the user above each question and only secondary on your knowledge. If the information can not be found you truthfully say: \"I don\"t know.\". You should format the answer in HTML.",
        "initMessage" => "Hello, I am a Q&A bot. Ask me anything.",
        "hasAiChat" => false,
        "allowAiChatCampaigns" => false,
        "allowAiContactCampaigns" => false,
        "aiChatCampaigns" => [],
        "limitChat" => false,
        "questionsLimit" => 5,
        "beforeFormMessage" => "You have reach the number of tokens, please filled the form below and we will be in contact with you.",
        "showContactForm" => false,
        "formFields" => [
            [
                "label" => "Name",
                "type" => "text",
                "active" => true,
                "required" => true
            ],
            [
                "label" => "Email",
                "type" => "email",
                "active" => true,
                "required" => true
            ],
            [
                "label" => "Phone",
                "type" => "tel",
                "active" => true,
                "required" => false
            ],
            [
                "label" => "Message",
                "type" => "textarea",
                "active" => false,
                "required" => false
            ]
        ],
        "aiContactCampaigns" => [],
        "afterFormMessageSuccess" => "Thanks for completing the form, we will be in contact soon.",
        "afterFormMessageError" => "There was an error submitting the form, please try again."
    ];

    public static function jsonSchema()
    {
        return json_decode(file_get_contents(WP_PLUGIN_DIR . '/main-gpt/src/Json/AiChatJsonSchema.json'), true);
    }
}
