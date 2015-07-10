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

  // CRLF
  const EOL = "\r\n";

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
  // Multipat form data boundary unique string.
  private $boundary;
  // Content to POST to the API
  private $contents;
  // Additional metadata to post to the API.
  private $metadata;
  // JSON string to be posted to PushAPI instead of article.json file.
  private $json;
  // Files to be posted to PushAPI
  private $files = [];
  // Multipart data
  private $multipart = [];

  /**
   * Implements Authentication().
   */
  protected function Authentication() {
    $content_type = sprintf('multipart/form-data; boundary=%s', $this->boundary);
    $cannonical_request = strtoupper($this->method) . $this->Path() . strval($this->datetime) . $content_type . $this->contents;
    return parent::HHMAC($cannonical_request);
  }

  /**
   * Implements PreprocessData().
   */
  protected function PreprocessData($method, $path, Array $path_args = [], Array $vars = []) {
    $this->method = $method;
    $this->path_args = $path_args;
    $this->path = $path;
    $this->boundary = md5(uniqid() . microtime());
    $this->metadata = !empty($vars['metadata']) ? $vars['metadata'] : '';
    $this->json = !empty($vars['json']) ? $vars['json'] : '';
    $this->files = !empty($vars['files']) ? $vars['files'] : array();
  }

  /**
   * Open and load file information and prepare data for multipart data.
   */
  protected function AddToMultipart($path) {
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

  /**
   * Generate Multipart data headers.
   */
  protected function BuildMultipartHeaders($content_type, Array $params) {
    $headers = 'Content-Type: ' . $content_type . static::EOL;
    $attributes = [];
    foreach ($params as $name => $value) {
      $attributes[] = $name . '=' . $value;
    }
    $headers .= 'Content-Disposition: form-data; ' . join('; ', $attributes) . static::EOL;
    return $headers;
  }

  /**
   * Generate Multipart form data chunks.
   */
  protected function EncodeMultipart(Array $file) {
    $multipart = '';
    // Adding metadata to multipart
    if (!empty($this->metadata)) {
      $multipart .= '--' . $this->boundary . static::EOL;
      $multipart .= $this->BuildMultipartHeaders('application/json',
        [
          'name' => 'metadata'
        ]
      );
      $multipart .= static::EOL . $this->metadata . static::EOL;
    }
    // Add files
    foreach ($file as $info) {
      $multipart .= '--' . $this->boundary . static::EOL;
      $multipart .= $this->BuildMultipartHeaders($info['mimetype'],
        [
          'filename'   => $info['filename'],
          'name'       => $info['name'],
          'size'       => $info['size']
        ]
      );
      $multipart .= static::EOL . $info['contents'] . static::EOL;
    }
    $multipart .= '--' . $this->boundary  . '--';
    $multipart .= static::EOL;
    return $multipart;
  }

  /**
   * Implements Response().
   */
  protected function Response($response) {
    //print_r($this->contents);
    print_r($response);exit;
  }

  /**
   * Implements Post().
   */
  public function Post($path, Array $path_args, Array $data = []) {
    $this->PreprocessData(__FUNCTION__, $path, $path_args, $data);
    try {

      // Submit JSON string as an article.json file.
      // Make sure you don't submit article.json if you passing json
      // as a parameter to Post method.
      if (!empty($this->json)) {
        $this->multipart[] = [
          'name'      => 'article',
          'filename'  => 'article.json',
          'mimetype'  => 'application/json',
          'contents'  => $this->json,
          'size'      => strlen($this->json),
        ];
      }

      // Process each file and generate multipart form data.
      foreach ($this->files as $file) {
        $this->multipart[] = $this->AddToMultipart($file);
      }

      // Generated multipart data to POST.
      $this->contents = $this->EncodeMultipart($this->multipart);

      // Make sure no USERAGENET in headers.
      $this->SetOption(CURLOPT_USERAGENT, NULL);
      $this->SetHeaders(
      	[
          'Accept'          => 'application/json',
          'Content-Type'    => sprintf('multipart/form-data; boundary=%s', $this->boundary),
          'Content-Length'  => strlen($this->contents),
      	  'Authorization'   => $this->Authentication(),
      	]
      );
      // Send POST request.
      return $this->Request($this->contents);
    }
    catch (\Exception $e) {
      // Need to write ClientException handling
    }
  }

}
