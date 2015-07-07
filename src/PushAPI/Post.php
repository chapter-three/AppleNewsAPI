<?php

/**
 * @file
 * GET Apple News Article.
 */

namespace ChapterThree\AppleNews\PushAPI;

/**
 * Document me.
 */
class Post extends Base {

  /**
   * Authentication.
   */
  protected function Authentication(Array $args) {
    $content_type = sprintf('Content-Type: multipart/form-data; boundary=%s', $args['boundary']);
    $cannonical_request = strtoupper($this->method) . $this->Path() . strval($this->datetime) . $content_type . $args['body'];
    $key = base64_decode($this->api_key_secret);
    $hashed = hash_hmac('sha256', $cannonical_request, $key, true);
    $signature = rtrim(base64_encode($hashed), "\n");
    return sprintf('HHMAC; key=%s; signature=%s; date=%s', $this->api_key_id, strval($signature), $this->datetime);
  }

  protected function fileLoadFormdata($path) {
    $pathinfo = pathinfo($path);

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimetype = finfo_file($finfo, $path);

    $contents = file_get_contents($path);
    $size = strlen($contents);

    return [
      'name' => str_replace(' ', '-', $pathinfo['filename']),
      'filename' => $pathinfo['basename'],
      'mimetype' => $mimetype,
      'contents' => $contents,
      'size' => $size,
    ];
  }

  protected function encodeMultipartFormdata(Array $fields, $boundary) {
    $encoded = '';
    foreach ($fields as $name => $data) {
      $encoded .= '--' .  $boundary . "\r\n";
      $encoded .= "\r\n";
      $encoded .= sprintf('Content-Type: %s', $data['mimetype']) . "\r\n";
      $encoded .= sprintf('Content-Disposition: form-data; filename=%s; name=%s; size=%d', $data['filename'], $data['name'], $data['size']) . "\r\n";
      $encoded .= $data['contents'] . "\r\n";
    }
    $encoded .= '--' .  $boundary . "\r\n";
    $encoded .= "\r\n";
    return $encoded;
  }

  protected function Response($response) {
    print_r($response);exit;
  }

  public function Post($path, Array $arguments = [], Array $data) {
    parent::PreprocessRequest(__FUNCTION__, $path, $arguments);
    if (isset($data['date']) && $data['date'] === NULL) {
      $data['date'] = $this->datetime;
    }
    if (isset($data['boundary']) && $data['boundary'] === NULL) {
      $data['boundary'] = md5(time());
    }
    try {

      $multipart = array();

      $multipart['article'] = [
        'name' => 'article',
        'filename' => 'article.json',
        'mimetype' => 'application/json',
        'contents' => $data['json'],
        'size' => strlen($data['json']),
      ];

      foreach ($data['files'] as $file) {
        $formdata = $this->fileLoadFormdata($file);
        $multipart[$formdata['name']] = $formdata;
      }

      $data['body'] = $this->encodeMultipartFormdata($multipart, $data['boundary']);

      $this->SetHeaders(
      	[
      	  'Authorization'  =>   $this->Authentication($data),
      	  'Content-Type'   =>   sprintf('multipart/form-data; boundary=%s', $data['boundary']),
      	]
      );
      return $this->Request();
    }
    catch (\ErrorException $e) {
      // Need to write ClientException handling
    }
  }

}
