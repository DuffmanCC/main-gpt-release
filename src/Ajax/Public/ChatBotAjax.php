<?php

namespace MainGPT\Ajax\Public;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Throwable;
use MainGPT\AbstractActionable;
use MainGPT\App;
use MainGPT\HookableTrait;
use MainGPT\PostMeta\AiMemory\EmbedModelObject;
use MainGPT\Service\OpenAiClient;
use MainGPT\Service\PineconeClient;
use MainGPT\Shortcode\Shortcode;
use MainGPT\PostMeta\AiChatter\ModelNameObject;
use MainGPT\PostMeta\AiChatter\PrimerPromptObject;
use MainGPT\PostMeta\AiChatter\NumberOfAnswersObject;
use MainGPT\PostMeta\AiChatter\AiChatObject;
use MainGPT\PostMeta\AiChatter\IncludeMetadataObject;

class ChatBotAjax extends AbstractActionable
{
    use HookableTrait;

    public const AJAX_ACTION = App::ID . '_chat_bot_ajax';
    public const INIT_NAME = 'wp_ajax_nopriv_' . self::AJAX_ACTION;

    protected OpenAiClient $openAiClient;
    protected PineconeClient $pineconeClient;
    protected string $postType;

    public function __construct(
        OpenAiClient $openAiClient,
        PineconeClient $pineconeClient,
    ) {
        $this->openAiClient = $openAiClient;
        $this->pineconeClient = $pineconeClient;
    }

    public function execute(): void
    {
        try {
            $this->setCorsHeaders();

            check_ajax_referer(self::AJAX_ACTION, 'security');

            $message = $_POST['data'][Shortcode::QUERY_FIELD];

            $embed = $this->openAiClient->embedData(
                EmbedModelObject::DEFAULT_VALUE,
                $message
            );

            $aiChatterId = (int) $_POST['data']['aiChatterId'];
            $aiChatContentArray = [];

            /**
             * if aiChatId is set, means there is already a chat in the 
             * database and the user wants to continue the conversation
             */
            if (isset($_POST['data']['aiChatId'])) {
                $aiChatId = (int) $_POST['data']['aiChatId'];

                if ($aiChatId) {
                    $aiChatContent = get_the_content(null, false, $aiChatId);
                    $aiChatContentArray = $aiChatContent ? json_decode($aiChatContent, true) : [];
                }
            }

            $request = $embed['data'][0]['embedding'];
            $indexName = $this->getPineconeIndex($aiChatterId);
            $info = $this->pineconeClient->indexInfo($indexName);
            $topK = $this->getTopK($aiChatterId);

            $search = $this->pineconeClient->query(
                $info['host'],
                $request,
                $topK,
            );

            // error_log(__FILE__ . ':' . __LINE__ . " | execute search: " . json_encode($search));

            $query = array_reduce(
                $search['matches'],
                function (string $carry, array $match): string {
                    $carry .= $match['metadata']['text'] . "\n\n---\n\n";
                    return $carry;
                },
                ''
            ) . $message;

            $messages = [];

            // primer prompt
            $messages[] = [
                'role' => 'system',
                'content' => $this->getPrimerPrompt($aiChatterId)
            ];

            // system message to always respond in HTML format
            $messages[] = [
                'role' => 'system',
                'content' => 'Always respond in HTML format without escaping tags or wrapping the response in quotes.'
            ];

            // recorded messages
            $messages = array_merge($messages, $aiChatContentArray);

            // current message
            $messages[] = [
                'role' => 'user',
                'content' => $query
            ];

            $response = $this->openAiClient->chatCompletion(
                $this->getModelName($aiChatterId),
                $messages
            );

            // error_log(__FILE__ . ':' . __LINE__ . " | execute response: " . json_encode($response));

            wp_send_json(
                $response['choices'][0]['message']['content'],
                200
            );
        } catch (Throwable $exception) {
            error_log(__FILE__ . ':' . __LINE__ . " | execute unexpected error: " . $exception->getCode() . ' - ' . $exception->getMessage());

            wp_send_json(
                [
                    "code" => $exception->getCode(),
                    "message" => $exception->getMessage()
                ],
                500
            );
        }
    }

    private function getPrimerPrompt(int | null $id): string
    {
        if (!$id) return PrimerPromptObject::DEFAULT_VALUE;

        $aiChatString = get_post_meta($id, AiChatObject::FIELD_ID, true);
        $aiChatObject = json_decode($aiChatString, true);
        return $aiChatObject['primerPrompt'];
    }

    private function getModelName(int | null $id): string
    {
        if (!$id) return ModelNameObject::DEFAULT_VALUE;

        $aiChatString = get_post_meta($id, AiChatObject::FIELD_ID, true);
        $aiChatObject = json_decode($aiChatString, true);
        return $aiChatObject['modelName'];
    }

    private function getPineconeIndex(int | null $id): string
    {
        if (!$id) return '';

        $aiChatString = get_post_meta($id, AiChatObject::FIELD_ID, true);
        $aiChatObject = json_decode($aiChatString, true);
        return $aiChatObject['pineconeIndex'];
    }

    private function getTopK(int | null $id): int
    {
        if (!$id) return NumberOfAnswersObject::DEFAULT_VALUE;

        $aiChatString = get_post_meta($id, AiChatObject::FIELD_ID, true);
        $aiChatObject = json_decode($aiChatString, true);
        return $aiChatObject['questionsLimit'];
    }
}
