<?php

/**
 * @file
 * Example: Delete articles
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

// Deletes an article.
$response = $PublisherAPI->Delete('/articles/{article_id}',
  [
    'article_id' => '[ARTICLE_ID]'
  ]
);
