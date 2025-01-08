<?php

namespace MainGPT\Ajax\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Throwable;
use MainGPT\AbstractActionable;
use MainGPT\App;
use MainGPT\HookableTrait;

class TokenizingPercentageAjax extends AbstractActionable
{
    use HookableTrait;
    public const AJAX_ACTION = App::ID . '_tokenizing_percentage_ajax';
    public const INIT_NAME = 'wp_ajax_' . self::AJAX_ACTION;

    public function execute(): void
    {
        try {
            check_ajax_referer(self::AJAX_ACTION, 'security');

            $id = (int) $_POST['data']['postId'];

            $percentage = get_post_meta($id, 'tokenizing-percentage', true);

            wp_send_json(
                [
                    'status' => 'Success',
                    'progress' => $percentage
                ],
                200
            );
        } catch (Throwable $exception) {
            error_log(__FILE__ . ":" . __LINE__ . " | execute unexpected error.");
            error_log($exception->getCode() . ' - ' . $exception->getMessage());
            error_log(print_r($exception->getTraceAsString(), true));

            wp_send_json(
                [
                    "code" => $exception->getCode(),
                    "message" => $exception->getMessage()
                ],
                500
            );
        }
    }
}
