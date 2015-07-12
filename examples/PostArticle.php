<?php

/**
 * @file
 * Example: POST Article
 */

require '../src/PushAPI.php';

use \ChapterThree\AppleNews;

$api_key_id = "";
$api_key_secret = "";
$endpoint = "https://endpoint_url";

$PushAPI = new PushAPI(
  $api_key_id,
  $api_key_secret,
  $endpoint
);

// Publishes a new article to a channel.
$response = $PushAPI->Post('/channels/{channel_id}/articles',
  [
    'channel_id' => '[CHANNEL_ID]'
  ],
  [
    // List of files to POST
    'files' => [
      __DIR__ . '/files/article.json',
    ], // not required when `json` not empty
    // JSON metadata string
    'metadata' => '', // optional
    // Submit contents of the article.json file if
    // the file isn't provied in the `files` array
    'json' => '', // optional
  ]
);
