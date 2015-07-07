<?php

/**
 * @file
 * GET Apple News Article.
 */

namespace ChapterThree\AppleNews;

/**
 * Document me.
 */
class PushAPI_Post extends PushAPI_Base {

  /**
   * Authentication.
   */
  protected function Authentication(Array $args) {
    $content_type = sprintf('Content-Type: multipart/form-data; boundary=%s', $args['boundary']);
    $hashed = hash_hmac('sha256', strtoupper($this->method) . $this->Path() . $this->datetime . $content_type . $args['body'], base64_decode($this->api_key));
    return sprintf('HHMAC; key=%s; signature=%s; date=%s', $this->api_key, base64_encode($hashed), $this->datetime);
  }

  public function Headers(Array $args) {
    return [
      'Content-Type' => sprintf('multipart/form-data; boundary=%s', $args['boundary']),
      'Authorization' => $this->Authentication($args),
    ];
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
      $encoded .= static::EOL;
      $encoded .= sprintf('Content-Type: %s', $data['mimetype']) . "\r\n";
      $encoded .= sprintf('Content-Disposition: form-data; filename=%s; name=%s; size=%d', $data['filename'], $data['name'], $data['size']) . "\r\n";
      $encoded .= $data['contents'] . "\r\n";
    }
    $encoded .= '--' .  $boundary . "\r\n";
    $encoded .= "\r\n";
    return $encoded;
  }

  public function Post($path, Array $arguments = [], Array $data) {
    $this->method = __FUNCTION__;
    $this->arguments = $arguments;
    $this->path = $path;
    if (isset($data['date']) && $data['date'] === NULL) {
      $data['date'] = date('c');
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

      foreach (parent::Headers($data) as $prop => $val) {
        $this->curl->setHeader($prop, $val);
      }
      $this->curl->setHeader('Content-Type', sprintf('multipart/form-data; boundary=%s', $data['boundary']));

      $response = $this->curl->post($this->Path());
      $this->curl->close();
      return $this->Response($response);
    }
    catch (\ErrorException $e) {
      // Need to write ClientException handling
    }
  }

}
