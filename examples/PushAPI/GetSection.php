<?php

/**
 * @file
 * Example: GET Section
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

// Fetches information about a single section.
$response = $PushAPI->Get('/sections/{section_id}',
  [
    'section_id' => '[SECTION_ID]'
  ]
);
