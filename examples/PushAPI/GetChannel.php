<?php

/**
 * @file
 * Example: GET Channel
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

// Fetches information about a channel.
$response = $PushAPI->Get('/channels/{channel_id}',
  [
    'channel_id' => '[CHANNEL_ID]'
  ]
);
