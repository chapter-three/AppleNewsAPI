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

  // Valid values for resource part Content-Type
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

  // Post request data
  private $data = [];

  // Multipart data
  private $multipart = [];

  const EOL = "\r\n";

  /**
   * Authentication.
   */
  protected function Authentication(Array $args) {
    $content_type = sprintf('multipart/form-data; boundary=%s', $args['boundary']);
    $cannonical_request = strtoupper($this->method) . $this->Path() . strval($this->datetime) . $content_type . $args['body'];
    return parent::HHMAC($cannonical_request);
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

  protected function EncodeMultipartFormdata(Array $file, $encoding = 'binary') {
    $encoded = '';
    foreach ($file as $info) {
      $encoded .= '--' .  $this->data['boundary'] . static::EOL;
      $encoded .= static::EOL;
      $encoded .= sprintf('Content-Type: %s', $info['mimetype']) . static::EOL;
      $encoded .= sprintf('Content-Disposition: form-data; filename=%s; name=%s; size=%d', $info['filename'], $info['name'], $info['size']) . static::EOL;
      if ($info['mimetype'] == 'application/json') {
        $encoded .= static::EOL;
        $encoded .= $info['contents'] . static::EOL;
      }
      else {
        $encoded .= sprintf('Content-Transfer-Encoding: %s', $encoding) . static::EOL;
        $encoded .= static::EOL;
        if ($encoding == 'binary') {
          $encoded .= $info['contents'] . static::EOL;
        }
        else {
          $encoded .= chunk_split(base64_encode($info['contents'])) . static::EOL;
        }
      }
    }
    $encoded .= '--' .  $this->data['boundary']  . '--' . static::EOL;
    $encoded .= static::EOL;
    return $encoded;
  }

  protected function EncodeMetadata(Array $metadata) {
    $encoded = '';
    $encoded .= '--' .  $this->data['boundary'] . static::EOL;
    $encoded .= static::EOL;
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
    $this->data = $data;
    try {

      $this->data['date'] = !empty($this->data['date']) ? $this->data['date'] : $this->datetime;
      $this->data['boundary'] = !empty($this->data['boundary']) ? $this->data['boundary'] : md5(time());
      $this->data['body'] = !empty($this->data['body']) ? $this->data['body'] : '';

      foreach ($this->data['files'] as $file) {
        $this->multipart[] = $this->FileLoadFormdata($file);
      }

      $this->data['body'] .= !empty($this->data['metadata']) ? $this->EncodeMetadata($this->data['metadata']) : '';
      $this->data['body'] .= $this->EncodeMultipartFormdata($this->multipart, 'binary');

      $this->SetHeaders(
      	[
          'Accept'          => 'application/json',
      	  'Authorization'   => $this->Authentication($this->data),
      	  'Content-Type'    => sprintf('multipart/form-data; boundary=%s', $this->data['boundary']),
          'Content-Length'  => strlen($this->data['body']),
      	]
      );
      $this->UnsetHeaders(['Expect']);
      return $this->Request($this->data['body']);
    }
    catch (\Exception $e) {
      // Need to write ClientException handling
    }
  }

}
