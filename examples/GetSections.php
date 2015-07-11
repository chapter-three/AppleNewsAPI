<?php

/**
 * @file
 * Example: GET Sections
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

// Fetches a list of all sections for a channel.
$response = $PushAPI->Get('/channels/{channel_id}/sections',
  [
    'channel_id' =>'[CHANNEL_ID]'
  ]
);
