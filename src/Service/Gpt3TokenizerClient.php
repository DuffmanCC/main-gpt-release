<?php

namespace MainGPT\Service;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use Exception;
use Gioni06\Gpt3Tokenizer\Gpt3TokenizerConfig;
use Gioni06\Gpt3Tokenizer\Gpt3Tokenizer;
use Ramsey\Uuid\Uuid;

class Gpt3TokenizerClient
{
    private $tokenizer;

    public function __construct()
    {
        $config = new Gpt3TokenizerConfig();
        $this->tokenizer = new Gpt3Tokenizer($config);
    }

    private function tiktokenLen($text)
    {
        $tokens = $this->tokenizer->encode($text);
        return count($tokens);
    }

    private function splitText($text, $chunkSize, $chunkOverlap, $separators)
    {
        $parts = preg_split('/(' . implode('|', array_map('preg_quote', $separators)) . ')/', $text, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

        $chunks = [];
        $currentPart = '';
        $currentTokens = [];

        foreach ($parts as $part) {
            $currentPart .= $part;
            $currentTokens = $this->tokenizer->encode($currentPart);

            while (count($currentTokens) >= $chunkSize) {
                $segment = array_slice($currentTokens, 0, $chunkSize);
                $chunks[] = $this->tokenizer->decode($segment);
                $currentTokens = array_slice($currentTokens, $chunkSize - $chunkOverlap);
                $currentPart = $this->tokenizer->decode($currentTokens);
            }
        }

        if (!empty($currentTokens)) {
            $chunks[] = $this->tokenizer->decode($currentTokens);
        }

        return $chunks;
    }

    /**
     * @param int $size
     * @param int $overlap
     * @param string[] $separators
     * @param array<int, array<string, string>> $data
     * @param int $postId
     * @return array
     * @throws Exception
     */
    public function tokenize(
        int $size,
        int $overlap,
        array $separators,
        array $data,
        int $postId
    ): array {
        $response = [];
        $totalNumberOfTokens = 0;
        $percentage = 0;
        $counter = 0;
        $chunks = [];

        update_post_meta($postId, 'tokenizing-percentage', $percentage);

        foreach ($data as $record) {
            $totalNumberOfTokens += $this->tiktokenLen($record['text']);
            $texts = $this->splitText($record['text'], $size, $overlap, $separators);

            // Process each text chunk
            foreach ($texts as $i => $text) {
                $chunks[] = [
                    'id' => Uuid::uuid4()->toString(),
                    'text' => $text,
                    'chunk' => $i,
                    'url' => $record['url']
                ];
            }

            $counter++;
            $percentage = round(($counter / count($data)) * 100, 2);
            update_post_meta($postId, 'tokenizing-percentage', $percentage);
            error_log(__FILE__ . ':' . __LINE__ . " - tokenizing: $percentage%");
        }

        $response['total_number_of_tokens'] = $totalNumberOfTokens;
        $response['chunks'] = $chunks;

        return $response;
    }
}
