<?php

namespace MainGPT;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use MainGPT\PostType\AiCampaign;
use MainGPT\PostMeta\AiMemory\EmbedModelObject;
use MainGPT\PostMeta\AiMemory\AmountOfSpaceObject;
use MainGPT\PostMeta\AiChatter\ModelNameObject;
use MainGPT\PostMeta\AiChatter\AiChatObject;
use JsonSchema\Validator;
use Exception;

/**
 * Validation for different types of data
 */
final class Validation
{
    /**
     * Validate post ids
     *
     * @param string $input
     * @return bool
     */
    static function validatePostIds(string $input): bool
    {
        $post_ids = explode(',', $input);

        foreach ($post_ids as $post_id) {
            if (get_post_status($input) === 'publish' || get_post_status($input) === 'draft' || empty($post_id)) {
                return true;
            }

            return false;
        }

        return false;
    }

    static function validatePostTypes(array | null $input): bool
    {
        if (is_null($input)) {
            return true;
        }

        foreach ($input as $post_type) {
            if (post_type_exists($post_type)) {
                return true;
            }
        }

        return false;
    }

    static function validateLowercaseWithHyphens(string | null $input): bool
    {
        if (is_null($input) || '' === $input) {
            return true;
        }

        $pattern = '/^[a-z0-9-]+$/';

        return preg_match($pattern, $input) === 1;
    }

    static function validateLowerUpperWithHyphens(string | null $input): bool
    {
        if (is_null($input) || '' === $input) {
            return true;
        }

        $pattern = '/^[a-zA-Z0-9-]+$/';

        return preg_match($pattern, $input) === 1;
    }

    static function validateEmbeddingModel(string $input): bool
    {
        $models = EmbedModelObject::MODELS;

        if (!in_array($input, $models)) {
            return false;
        }

        return true;
    }

    static function validatePodType(string $input): bool
    {
        $pattern = '/^(s1|p1|p2)\.(x1|x2|x4|x8)$/';

        return preg_match($pattern, $input) === 1;
    }

    static function validateModelName(string $input): bool
    {
        $models = ModelNameObject::VALUES;

        if (!in_array($input, $models)) {
            return false;
        }

        return true;
    }

    static function validateDurationCampaign(string $input): bool
    {
        // parse the input to an integer
        $input = (int) $input;

        if ($input < 6 || $input > 72) {
            return false;
        }

        return true;
    }

    static function validateCampaignIds(array $input): bool
    {
        foreach ($input as $campaign_id) {
            if (get_post_type($campaign_id) === AiCampaign::POST_TYPE || "" === $campaign_id) {
                return true;
            }

            return false;
        }

        return false;
    }

    static function validateAmountOfSpace(string $input): bool
    {
        $options = AmountOfSpaceObject::OPTIONS;

        if (!in_array($input, $options)) {
            return false;
        }

        return true;
    }

    static function validate_true_or_false_string(string $input): bool
    {
        if ($input === 'true' || $input === 'false') {
            return true;
        }

        return false;
    }

    /**
     * Validates a given AI Chat object.
     *
     * This function takes a JSON string as input and validates it against a predefined JSON schema.
     * The JSON string is expected to represent an AI Chat object. If the JSON string is coming from 
     * the database, it will have backslashes that need to be removed. If it's coming from a $_POST 
     * request, it won't have backslashes.
     *
     * @param string $input The JSON string representing the AI Chat object.
     * @param bool $stripslashes Optional. Whether to remove backslashes from the JSON string. 
     *                           Default is true, indicating that backslashes should be removed.
     *                           Set to false if the JSON string is coming from a $_POST request.
     *
     * @return bool Returns true if the AI Chat object is valid, false otherwise.
     *
     * @throws Exception If the JSON string cannot be decoded or if it doesn't validate against the schema.
     */
    static function isValidAiChatObject(string $input, bool $stripslashes = true): bool
    {
        $jsonObject = json_decode($input);

        if ($stripslashes) {
            // Remove backslashes from the JSON string
            $unescapedJsonString = stripslashes($input);
            $jsonObject = json_decode($unescapedJsonString);
        }

        if ($jsonObject === null && json_last_error_msg() !== JSON_ERROR_NONE) {
            error_log(__FILE__ . ':' . __LINE__ . ' | JSON decoding failed with error: ' . json_last_error_msg());
            return false;
        }

        $validator = new Validator;

        $validator->validate(
            $jsonObject,
            AiChatObject::jsonSchema()
        );

        if ($validator->isValid()) {
            return true;
        }

        $errorString =  "<strong>JSON does not validate</strong>. Violations:<br/>";

        foreach ($validator->getErrors() as $error) {
            $errorString .= "<b>{$error['property']}</b>: {$error['message']}<br/>";
        }

        error_log(__FILE__ . ':' . __LINE__ . ' | ' .  $errorString);

        return false;
    }
}
