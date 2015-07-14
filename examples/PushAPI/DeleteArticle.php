<?php

/**
 * @file
 * Example: Delete articles
 */

require '../../src/PushAPI.php';

use \ChapterThree\AppleNews;

$api_key_id = "";
$api_key_secret = "";
$endpoint = "https://endpoint_url";

$PushAPI = new PushAPI(
  $api_key_id,
  $api_key_secret,
  $endpoint
);

// Deletes an article.
$response = $PushAPI->Delete('/articles/{article_id}',
  [
    'article_id' => '[ARTICLE_ID]'
  ]
);
