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

  /**
   * Authentication.
   */
  protected function Authentication(Array $args) {
    $content_type = sprintf(' Content-Type: multipart/form-data; boundary=%s ', $args['boundary']);
    $cannonical_request = strtoupper($this->method) . $this->Path() . strval($this->datetime) . $content_type . $args['body'];
    $key = base64_decode($this->api_key_secret);
    $hashed = hash_hmac('sha256', $cannonical_request, $key, true);
    $signature = rtrim(base64_encode($hashed), "\n");
    return sprintf('HHMAC; key=%s; signature=%s; date=%s', $this->api_key_id, strval($signature), $this->datetime);
  }

  protected function FileLoadFormdata($path) {
    $pathinfo = pathinfo($path);

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimetype = finfo_file($finfo, $path);

    $contents = file_get_contents($path);
    $size = strlen($contents);

    return [
      'name' => str_replace(' ', '-', $pathinfo['filename']),
      'filename' => $pathinfo['basename'],
      'mimetype' => ($pathinfo['extension'] == 'json') ? 'application/json' : $mimetype,
      'contents' => $contents,
      'size' => $size,
    ];
  }

  protected function EncodeMultipartFormdata(Array $fields, $boundary) {
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
    if (empty($data['date'])) {
      $data['date'] = $this->datetime;
    }
    if (empty($data['boundary'])) {
      $data['boundary'] = md5(time());
    }
    try {

      $multipart = array();
      foreach ($data['files'] as $file) {
        $formdata = $this->FileLoadFormdata($file);
        $multipart[$formdata['name']] = $formdata;
      }

      $data['body'] = $this->EncodeMultipartFormdata($multipart, $data['boundary']);

      $this->SetHeaders(
      	[
      	  'Authorization'   => $this->Authentication($data),
      	  'Content-Type'    => sprintf('multipart/form-data; boundary=%s', $data['boundary']),
          'Content-Length'  => strlen($data['body']),
      	]
      );
      $this->UnsetHeaders(['Expect']);
      return $this->Request(['data' => $data['body']]);
    }
    catch (\ErrorException $e) {
      // Need to write ClientException handling
    }
  }

}
