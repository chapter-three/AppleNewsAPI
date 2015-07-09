<?php

/**
 * @file
 * Apple News POST method.
 */

namespace ChapterThree\AppleNews\PushAPI;

/**
 * PushAPI POST method.
 */
class Post extends Base {

  protected $valid_mimes = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/font-sfnt',
    'application/x-font-truetype',
    'application/font-truetype',
    'application/vnd.ms-opentype',
    'application/x-font-opentype',
    'application/font-opentype',
    'application/octet-stream'
  ];

  const EOL = "\r\n";

  /**
   * Authentication.
   */
  protected function Authentication(Array $args) {
    $content_type = sprintf('multipart/form-data; boundary=%s', $args['boundary']);
    $cannonical_request = strtoupper($this->method) . $this->Path() . strval($this->datetime) . $content_type . $args['body'];
    return $this->HHMAC($cannonical_request);
  }

  protected function FileLoadFormdata($path) {
    $pathinfo = pathinfo($path);

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimetype = finfo_file($finfo, $path);
    if (!in_array($mimetype, $this->valid_mimes)) {
      $mimetype = 'application/octet-stream';
    }

    $contents = file_get_contents($path);

    return [
      'name' => str_replace(' ', '-', $pathinfo['filename']),
      'filename' => $pathinfo['basename'],
      'mimetype' => ($pathinfo['extension'] == 'json') ? 'application/json' : $mimetype,
      'contents' => $contents,
      'size' => strlen($contents),
    ];
  }

  protected function EncodeMultipartFormdata(Array $file, $boundary) {
    $encoded = '';
    foreach ($file as $name => $data) {
      $encoded .= '--' .  $boundary . static::EOL;
      $encoded .= sprintf('Content-Type: %s', $data['mimetype']) . static::EOL;
      $encoded .= sprintf('Content-Disposition: form-data; filename=%s; name=%s; size=%d', $data['filename'], $data['name'], $data['size']) . static::EOL;
      $encoded .= static::EOL;
      $encoded .= $data['contents'] . static::EOL;
    }
    $encoded .= '--' .  $boundary . static::EOL;
    $encoded .= static::EOL;
    return $encoded;
  }

  protected function EncodeMetadata(Array $metadata, $boundary) {
    $encoded = '';
    $encoded .= '--' .  $boundary . static::EOL;
    $encoded .= 'Content-Type: application/json' . static::EOL;
    $encoded .= 'Content-Disposition: form-data; name=metadata' . static::EOL;
    $encoded .= static::EOL;
    $encoded .= stripslashes(json_encode($metadata, JSON_PRETTY_PRINT)) . static::EOL;
    $encoded .= static::EOL;
    return $encoded;
  }

  protected function Response($response) {
    print_r($response);exit;
  }

  public function Post($path, Array $arguments = [], Array $data = []) {
    parent::PreprocessRequest(__FUNCTION__, $path, $arguments);
    try {

      if (empty($data['date'])) {
        $data['date'] = $this->datetime;
      }
      if (empty($data['boundary'])) {
        $data['boundary'] = md5(time());
      }

      $multipart = [];
      foreach ($data['files'] as $file) {
        $formdata = $this->FileLoadFormdata($file);
        $multipart[$formdata['name']] = $formdata;
      }

      $body = '';
      if (!empty($data['metadata'])) {
        $body .= $this->EncodeMetadata($data['metadata'], $data['boundary']);
      }
      $body .= $this->EncodeMultipartFormdata($multipart, $data['boundary']);
      $data['body'] = $body;

      $this->SetHeaders(
      	[
          'Accept'          => 'application/json',
      	  'Authorization'   => $this->Authentication($data),
      	  'Content-Type'    => sprintf('multipart/form-data; boundary=%s', $data['boundary']),
          'Content-Length'  => strlen($data['body']),
      	]
      );
      return $this->Request($data['body']);
    }
    catch (\ErrorException $e) {
      // Need to write ClientException handling
    }
  }

}
