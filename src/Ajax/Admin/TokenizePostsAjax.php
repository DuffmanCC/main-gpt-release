<?php

namespace MainGPT\Ajax\Admin;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Exception;
use Throwable;
use MainGPT\AbstractActionable;
use MainGPT\App;
use MainGPT\HookableTrait;
use MainGPT\PostMeta\AiMemory\ChunkOverlapObject;
use MainGPT\PostMeta\AiMemory\ChunkSizeObject;
use MainGPT\PostMeta\AiMemory\SeparatorsObject;
use MainGPT\PostMeta\AiMemory\TokenizedChunksObject;
use MainGPT\PostMeta\AiMemory\TotalTokensObject;
use MainGPT\PostMeta\AiMemory\PostIdsObject;
use MainGPT\Service\Gpt3TokenizerClient;

class TokenizePostsAjax extends AbstractActionable
{
    use HookableTrait;
    public const AJAX_ACTION = App::ID . '_tokenize_posts';
    public const INIT_NAME = 'wp_ajax_' . self::AJAX_ACTION;

    protected Gpt3TokenizerClient $tokenizer;

    public function __construct(Gpt3TokenizerClient $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }

    public function execute(): void
    {
        try {
            check_ajax_referer(self::AJAX_ACTION, 'security');

            $jsonString = $_POST['data']['posts'];
            $id = (int) $_POST['data']['postId'];

            $postsArr = json_decode(stripslashes($jsonString), true);

            if ($postsArr === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception("JSON decode error: " . json_last_error_msg());
            }


            $data = [];



            foreach ($postsArr as $post) {
                $postId = (int) $post['id'];
                $found = get_post($postId);

                if (get_post_type($found) === 'product') {
                    $productData = wc_get_product($postId);

                    $data[] = [
                        'url' => get_permalink($found->ID),
                        'text' => json_encode(
                            $this->mapProduct(
                                get_permalink($found->ID),
                                $productData->get_data(),
                                wp_get_attachment_url(
                                    $productData->get_image_id()
                                )
                            ),
                            true
                        ),
                    ];
                } else {
                    $data[] = [
                        'url' => get_permalink($found),
                        'text' => $found->post_content
                    ];
                }
            }

            // even if tokenizer fails, we still want to save the posts
            update_post_meta($id, PostIdsObject::FIELD_ID, $jsonString);

            $response['data'] = $this->tokenizer->tokenize(
                (int) ChunkSizeObject::DEFAULT_VALUE,
                (int) ChunkOverlapObject::DEFAULT_VALUE,
                SeparatorsObject::DEFAULT_VALUE,
                $data,
                $id
            );

            $response['message'] = 'Tokenized ' . count($data) . ' posts.';

            update_post_meta($id, TokenizedChunksObject::FIELD_ID, $response['data']['chunks']);
            update_post_meta($id, TotalTokensObject::FIELD_ID, $response['data']['total_number_of_tokens']);

            wp_send_json(
                $response,
                $jsonString,
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

    protected function mapProduct(string $url, $product, string $image)
    {
        return [
            "link" => $url,
            "name" => $product['name'],
            "description" => $product['description'],
            "price" => $product['price'],
            "regular_price" => $product['regular_price'],
            "sale_price" => $product['sale_price'],
            "date_on_sale_from" => $product['date_on_sale_from'],
            "date_on_sale_to" => $product['date_on_sale_to'],
            "stock_quantity" => $product['stock_quantity'],
            "stock_status" => $product['stock_status'],
            "weight" => $product['weight'],
            "length" => $product['length'],
            "width" => $product['width'],
            "height" => $product['height'],
            "image" => $image,
            "variations_of_the_product" => $this->getVariations($product['attributes']),
        ];
    }

    protected function getVariations(array $attributes)
    {
        $variations = [];

        foreach ($attributes as $key => $value) {
            if ($value->get_variation()) {
                $variations[$key] = $value->get_options();
            }
        }

        return $variations;
    }
}
