<?php

/**
 * @file
 * Base class for AppleNews classes.
 */

namespace ChapterThree\AppleNews;

/**
 * Base class for AppleNews classes.
 */
class PushAPI extends Base {

  // CRLF
  const EOL = "\r\n";

  // PushAPI API Key ID
  public $api_key_id = '';
  // Push API Secret Key
  public $api_key_secret = '';
  // PushAPI Endpoint URL
  public $endpoint = '';

  // HTTP client class.
  public $http_client;
  // Endpoint path
  protected $path = '';
  // HTTP Method (GET/DELETE/POST)
  protected $method = '';
  // Endpoint path variables to replace
  protected $path_args = [];
  // ISO 8601 datetime
  protected $datetime;

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
   * Implements __construct().
   */
  public function __construct($key, $secret, $endpoint) {
    $this->api_key_id = $key;
    $this->api_key_secret = $secret;
    $this->endpoint = $endpoint;
    $this->http_client = new \Curl\Curl;
    $this->datetime = gmdate(\DateTime::ISO8601);
  }

  /**
   * Implements HHMAC().
   * Generate HMAC cryptographic hash.
   */
  protected function HHMAC($cannonical_request) {
    $key = base64_decode($this->api_key_secret);
    $hashed = hash_hmac('sha256', $cannonical_request, $key, true);
    $signature = rtrim(base64_encode($hashed), "\n");
    return sprintf('HHMAC; key=%s; signature=%s; date=%s', $this->api_key_id, strval($signature), $this->datetime);
  }

  /**
   * Implements Authentication().
   */
  protected function Authentication($string = '') {
    $cannonical_request = strtoupper($this->method) . $this->Path() . strval($this->datetime) . $string;
    return $this->HHMAC($cannonical_request);
  }

  /**
   * Implements Path().
   */
  protected function Path() {
    $params = array();
    foreach ($this->path_args as $argument => $value) {
      $params["{{$argument}}"] = $value;
    }
    $path = str_replace(array_keys($params), array_values($params), $this->path);
    return $this->endpoint . $path;
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
   * Implements SetHeaders().
   */
  protected function SetHeaders(Array $headers = []) {
    foreach ($headers as $property => $value) {
      $this->http_client->setHeader($property, $value);
    }
  }

  /**
   * Implements UnsetHeaders().
   */
  protected function UnsetHeaders(Array $headers = []) {
    foreach ($headers as $property) {
      $this->http_client->unsetHeader($property);
    }
  }

  /**
   * Implements Request().
   */
  protected function Request($data) {
    $response = $this->http_client->{$this->method}($this->Path(), $data);
    $this->http_client->close();
    return $this->Response($response);
  }

  /**
   * Implements Response().
   */
  protected function Response($response) {
    return $response;
  }

  /**
   * Implements SetOption().
   */
  public function SetOption($name, $value) {
    $this->http_client->setOpt($name, $value);
  }

  /**
   * Implements Get().
   */
  public function Get($path, Array $path_args = [], Array $data = []) {
    $this->PreprocessData(__FUNCTION__, $path, $path_args, $data);
    try {
      $this->SetHeaders(
        [
          'Authorization' => $this->Authentication()
        ]
      );
      return $this->Request($data);
    }
    catch (\Exception $e) {
      // Need to write ClientException handling
    }
  }

  /**
   * Implements Delete().
   */
  public function Delete($path, Array $path_args = [], Array $data = []) {
    $this->PreprocessData(__FUNCTION__, $path, $path_args, $data);
    try {
      $this->SetHeaders(
        [
          'Authorization' => $this->Authentication()
        ]
      );
      $this->UnsetHeaders(
        [
          'Content-Type'
        ]
      );
      return $this->Request($data);
    }
    catch (\Exception $e) {
      // Need to write ClientException handling
    }
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
          'size'      => strlen($this->json)
        ];
      }

      // Process each file and generate multipart form data.
      foreach ($this->files as $file) {
        $this->multipart[] = $this->AddToMultipart($file);
      }

      // Set content type and boundary token.
      $content_type = sprintf('multipart/form-data; boundary=%s', $this->boundary);

      // Generated multipart data to POST.
      $this->contents = $this->EncodeMultipart($this->multipart);
      // String to add to generate Authorization hash.
      $hash_string = $content_type . $this->contents;

      // Make sure no USERAGENET in headers.
      $this->SetOption(CURLOPT_USERAGENT, NULL);
      $this->SetHeaders(
        [
          'Accept'          => 'application/json',
          'Content-Type'    => $content_type,
          'Content-Length'  => strlen($this->contents),
          'Authorization'   => $this->Authentication($hash_string)
        ]
      );
      // Send POST request.
      return $this->Request($this->contents);
    }
    catch (\Exception $e) {
      // Need to write ClientException handling
    }
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
      'name'      => str_replace(' ', '-', $pathinfo['filename']),
      'filename'  => $pathinfo['basename'],
      'mimetype'  => ($pathinfo['extension'] == 'json') ? 'application/json' : $mimetype,
      'contents'  => $contents,
      'size'      => strlen($contents)
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

}
