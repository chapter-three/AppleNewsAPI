<?php

/**
 * @file
 * Example: POST Article
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

// An optional metadata part may also be included, to provide additional
// non-Native data about the article. The metadata part also specifies any
// sections for the article, by URL. If this part is omitted,
// the article will be published to the channel's default section.
$metadata =  [
  'data' => [
    'isCandidateToBeFeatured' => true,
    'isSponsored' => true,
    'links' => [
      'sections' => [
        'https://endpoint_url/sections/f4706267-95fa-3571-9a26-273903e0b1ed',
      ],
    ],
    'revision' => REVISION_ID // required.
  ],
];

// Updates an existing article.
// See $response variable to get a new revision ID.
$response = $PushAPI->post('/articles/{article_id}',
  [
    'article_id' => ARTICLE_ID
  ],
  [
    // List of files to POST
    'files' => [
      __DIR__ . '/files/article.json',
    ], // not required when `json` not empty
    // JSON metadata string
    'metadata' => json_encode($metadata, JSON_UNESCAPED_SLASHES), // optional
    // Submit contents of the article.json file if
    // the file isn't provied in the `files` array
    'json' => '', // optional
  ]
);
