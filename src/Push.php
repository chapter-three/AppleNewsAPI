<?php

/**
 * @file
 * Document me.
 */

namespace ChapterThree\AppleNews;

use GuzzleHttp\Psr7\Request;


/**
 * Document me.
 */
class Push {

  const EOL = "\r\n";

  public $api_key_id;
  public $channel_id;
  public $endpoint;

  protected $method = 'POST';
  protected $client;

  public function __construct($api_key_id, $channel_id, $endpoint, \GuzzleHttp\ClientInterface $client) {
    $this->api_key_id = $api_key_id;
    $this->channel_id = $channel_id;
    $this->endpoint = $endpoint;

    $this->client = $client;
  }

  protected function getPath() {
    return str_replace('{channel_id}', $this->channel_id, '/channels/{channel_id}/articles');
  }

  protected function getHeaders($body, $content_type) {
    $date = date('c');
    $canonical_request = $this->method . $this->endpoint . '/' . $this->getPath() . $date . $content_type . $body;
    $key = base64_decode($this->api_key_id);
    $hashed = hash_hmac('sha256', $canonical_request, $key);
    $signature = base64_encode($hashed);
    $authorization = sprintf('HHMAC; key=%s; signature=%s; date=%s', $this->api_key_id, $signature, $date);

    return array(
      'Content-Type' => $content_type,
      'Authorization' => $authorization,
    );
  }

  public function fileLoadFormdata($path) {
    $pathinfo = pathinfo($path);

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimetype = finfo_file($finfo, $path);

    $contents = file_get_contents($path);
    $size = strlen($contents);

    return array(
      'name' => str_replace(' ', '-', $pathinfo['filename']),
      'filename' => $pathinfo['basename'],
      'mimetype' => $mimetype,
      'contents' => $contents,
      'size' => $size,
    );
  }

  public function encodeMultipartFormdata(array $fields, $boundary = NULL) {
    if ($boundary === NULL) {
      $boundary = md5(time());
    }

    $encoded = '';
    foreach ($fields as $name => $data) {
      $encoded .= '--' .  $boundary . static::EOL;
      $encoded .= static::EOL;
      $encoded .= sprintf('Content-Type: %s', $data['mimetype']) . static::EOL;
      $encoded .= sprintf('Content-Disposition: form-data; filename=%s; name=%s; size=%d', $data['filename'], $data['name'], $data['size']) . static::EOL;
      $encoded .= $data['contents'] . static::EOL;
    }

    $encoded .= '--' .  $boundary . static::EOL;
    $encoded .= static::EOL;

    return array(
      $encoded,
      sprintf('Content-Type: multipart/form-data; boundary=%s', $boundary),
    );
  }

  public function post($json, $files = array()) {
    $multipart = array();

    $multipart['article'] = array(
      'name' => 'article',
      'filename' => 'article.json',
      'mimetype' => 'application/json',
      'contents' => $json,
      'size' => strlen($json),
    );

    foreach ($files as $file) {
      $formdata = $this->fileLoadFormdata($file);
      $multipart[$formdata['name']] = $formdata;
    }

    list($body, $content_type) = $this->encodeMultipartFormdata($multipart);
    $headers = $this->getHeaders($body, $content_type);

    try {
      $response = $this->client->post($this->endpoint . '/' . $this->getPath(), array(
        'synchronous' => TRUE,
        'headers' => $headers,
        'body' => $body,
      ));
    }
    catch(ClientException $e) {
      dpm($e->getRequest());
      dpm($e->getResponse());
    }

    return $response;
  }

}
