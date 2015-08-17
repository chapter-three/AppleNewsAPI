<?php

/**
 * @file
 * Example: Get article
 */

require '../../src/PublisherAPI.php';

use \ChapterThree\AppleNews;

$api_key_id = "";
$api_key_secret = "";
$endpoint = "https://endpoint_url";

$PublisherAPI = new PublisherAPI(
  $api_key_id,
  $api_key_secret,
  $endpoint
);

// Fetches an article.
$response = $PublisherAPI->Get('/articles/{article_id}',
  [
    'article_id' => '[ARTICLE_ID]'
  ]
);
