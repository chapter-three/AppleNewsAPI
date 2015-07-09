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
  private $boundary;
  private $contents;

  // Multipart data
  private $multipart = [];

  const EOL = "\r\n";

  /**
   * Authentication.
   */
  protected function Authentication(Array $args) {
    $content_type = sprintf('multipart/form-data; boundary=%s', $args['boundary']);
    $cannonical_request = strtoupper($this->method) . $this->Path() . strval($this->datetime) . $content_type . $args['contents'];
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
      $encoded .= '--' .  $this->boundary . static::EOL;
      $encoded .= sprintf('Content-Type: %s', $info['mimetype']) . static::EOL;
      $encoded .= sprintf('Content-Disposition: form-data; filename=%s; name=%s; size=%d', $info['filename'], $info['name'], $info['size']) . static::EOL;
      $encoded .= $info['contents'] . static::EOL;
    }
    $encoded .= '--' .  $this->boundary  . '--' . static::EOL;
    $encoded .= static::EOL;
    return $encoded;
  }

  protected function EncodeMetadata(Array $metadata) {
    $encoded = '';
    $encoded .= '--' .  $this->boundary . static::EOL;
    $encoded .= 'Content-Type: application/json' . static::EOL;
    $encoded .= 'Content-Disposition: form-data; name=metadata' . static::EOL;
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
      $this->boundary = md5(time());

      // Submit JSON string as an article.json file.
      if (!empty($data['json'])) {
        $this->multipart[] = $this->FileLoadFormdata([
          'name' => 'article',
          'filename' => 'article.json',
          'mimetype' => 'application/json',
          'contents' => $data['json'],
          'size' => strlen($data['json']),
        ]);
      }

      foreach ($data['files'] as $file) {
        $this->multipart[] = $this->FileLoadFormdata($file);
      }

      $this->contents .= !empty($data['metadata']) ? $this->EncodeMetadata($data['metadata']) : '';
      $this->contents .= $this->EncodeMultipartFormdata($this->multipart);

      $this->SetHeaders(
      	[
      	  'Authorization'   => $this->Authentication(
            [
              'boundary' => $this->boundary,
              'contents' => $this->contents,
            ]
          ),
      	  'Content-Type'    => sprintf('multipart/form-data; boundary=%s', $this->boundary),
          'Content-Length'  => strlen($this->contents),
      	]
      );
      $this->UnsetHeaders(['Expect']);
      //print_r($this->contents);exit;
      return $this->Request($this->contents);
    }
    catch (\Exception $e) {
      // Need to write ClientException handling
    }
  }

}
