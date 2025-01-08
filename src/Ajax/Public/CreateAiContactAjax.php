<?php

namespace MainGPT\Ajax\Public;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Throwable;
use MainGPT\AbstractActionable;
use MainGPT\App;
use MainGPT\HookableTrait;
use MainGPT\PostMeta\AiContact\AiChatIdObject;
use MainGPT\PostMeta\AiContact\CreationDateObject;
use MainGPT\PostMeta\AiContact\EmailObject;
use MainGPT\PostMeta\AiContact\CampaignsObject;
use MainGPT\PostMeta\AiContact\MessageObject;
use MainGPT\PostMeta\AiContact\NameObject;
use MainGPT\PostMeta\AiContact\PhoneObject;
use MainGPT\PostMeta\AiChatter\AiChatObject;

class CreateAiContactAjax extends AbstractActionable
{
    use HookableTrait;

    public const AJAX_ACTION = App::ID . '_create_ai_contact_ajax';
    public const INIT_NAME = 'wp_ajax_nopriv_' . self::AJAX_ACTION;

    public function execute(): void
    {
        try {
            $this->setCorsHeaders();

            check_ajax_referer(self::AJAX_ACTION, 'security');

            $data = $_POST['data'];

            $activeAndRequiredFields = $this->getActiveRequiredFields($data['aiChatterId']);

            $fields = stripslashes($data['fields']);

            $fields = json_decode($fields);

            foreach ($activeAndRequiredFields as $field) {
                if ($field === 'email' && !is_email($fields->$field)) {
                    throw new \Exception('Invalid email address.', 400);
                }

                if ($fields->$field === '') {
                    throw new \Exception($field . ' is required.', 400);
                }
            }

            $response = $this->createAiContact($data);

            wp_send_json([
                'message' => $response
            ], 200);
        } catch (Throwable $exception) {
            error_log(__FILE__ . ":" . __LINE__ . " | execute unexpected error.");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));

            wp_send_json([
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
            ], 500);
        }
    }

    private function createDate()
    {
        date_default_timezone_set('UTC');

        return date('Y-m-d H:i:s');
    }

    private function createAiContact(array $data): int | \WP_Error
    {
        $fields = stripslashes($data['fields']);
        $fields = json_decode($fields);

        $postarr = [
            'post_author'   => 0,
            'post_title'    => $this->createDate() . ' - ' . $fields->email,
            'post_type'     => App::ID . '_ai_contact',
            'post_status'   => 'publish',
            'meta_input'    => [
                CreationDateObject::FIELD_ID   => $this->createDate(),
                CampaignsObject::FIELD_ID  => $this->gdprCampaingsIds($data['campaignIds']),
                AiChatIdObject::FIELD_ID    => sanitize_text_field($data['aiChatId']),
                NameObject::FIELD_ID           => sanitize_text_field($fields->name),
                EmailObject::FIELD_ID          => sanitize_text_field($fields->email),
                PhoneObject::FIELD_ID          => sanitize_text_field($fields->phone),
                MessageObject::FIELD_ID        => sanitize_textarea_field($fields->message),
            ]
        ];

        return wp_insert_post($postarr, true);
    }

    private function gdprCampaingsIds(array | null $data): string
    {
        if (is_null($data)) {
            return '';
        }

        return implode(',', $data);
    }

    private function getActiveRequiredFields(int $id): array
    {
        $aiChat = get_post_meta($id, AiChatObject::FIELD_ID, true);

        $aiChat = json_decode($aiChat);
        $arr = [];

        foreach ($aiChat->formFields as $key => $value) {
            if ($value->active == 1 && $value->required == 1) {
                $arr[] = $key;
            }
        }

        return $arr;
    }
}
