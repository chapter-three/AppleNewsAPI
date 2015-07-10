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

  // PushAPI API Key ID.
  public $api_key_id = '';

  // Push API Secret Key.
  public $api_key_secret = '';

  // PushAPI Endpoint URL.
  public $endpoint = '';

  // HTTP client class.
  public $http_client;

  // Endpoint path.
  protected $path = '';

  // HTTP Method (GET/DELETE/POST).
  protected $method = '';

  // Endpoint path variables to replace.
  protected $path_args = [];

  // ISO 8601 datetime.
  protected $datetime;

  // Valid values for resource part Content-Type.
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

  // Content to POST to the API.
  private $contents;

  // Additional metadata to post to the API.
  private $metadata;

  // JSON string to be posted to PushAPI instead of article.json file.
  private $json;

  // Files to be posted to PushAPI.
  private $files = [];

  // Multipart data.
  private $multipart = [];

  /**
   * Initialize variables needed in the communication with the API.
   *
   * @param string $key
   *   API Key.
   * @param string $secret
   *   API Secret Key.
   * @param string $endpoint
   *   API endpoint URL.
   */
  public function __construct($key, $secret, $endpoint) {
    $this->api_key_id = $key;
    $this->api_key_secret = $secret;
    $this->endpoint = $endpoint;
    $this->http_client = new \Curl\Curl;
    $this->datetime = gmdate(\DateTime::ISO8601);
  }

  /**
   * Generate HMAC cryptographic hash.
   *
   * @param string $data
   *   Message to be hashed.
   *
   * @return string
   *   Authorization token used in the HTTP headers.
   */
  protected function HHMAC($data) {
    $key = base64_decode($this->api_key_secret);
    $hashed = hash_hmac('sha256', $data, $key, true);
    $encoded = base64_encode($hashed);
    $signature = rtrim($encoded, "\n");
    return sprintf('HHMAC; key=%s; signature=%s; date=%s',
      $this->api_key_id, strval($signature),
      $this->datetime
    );
  }

  /**
   * Create canonical version of the request as a byte-wise concatenation.
   *
   * @param string $string
   *   String to concatenate (see POST method).
   *
   * @return string
   *   HMAC cryptographic hash
   */
  protected function Authentication($string = '') {
    $data = strtoupper($this->method) . $this->Path() . strval($this->datetime) . $string;
    return $this->HHMAC($data);
  }

  /**
   * Generate URL to request.
   *
   * @return string
   *   URL to create request.
   */
  protected function Path() {
    $params = array();
    // Take arguments and pass them to the path by replacing {argument} tokens.
    foreach ($this->path_args as $argument => $value) {
      $params["{{$argument}}"] = $value;
    }
    $path = str_replace(array_keys($params), array_values($params), $this->path);
    return $this->endpoint . $path;
  }

  /**
   * Initialize variables needed to make a request.
   *
   * @param string $method
   *   Request method (POST/GET/DELETE).
   * @param string $path
   *   Path to API endpoint.
   * @param array $path_args
   *   Endpoint path arguments to replace tokens in the path.
   * @param array $vars
   *   Data to pass to the endpoint (expect for POST, see $this->Post()).
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
   * Set HTTP headers.
   *
   * @param array $headers
   *   Associative array [header field name => value].
   */
  protected function SetHeaders(Array $headers = []) {
    foreach ($headers as $property => $value) {
      $this->http_client->setHeader($property, $value);
    }
  }

  /**
   * Remove specified header names from HTTP request.
   *
   * @param array $headers
   *   Associative array [header1, header2, ..., headerN].
   */
  protected function UnsetHeaders(Array $headers = []) {
    foreach ($headers as $property) {
      $this->http_client->unsetHeader($property);
    }
  }

  /**
   * Create HTTP request.
   *
   * @param mixed $data
   *   Raw content of the request or associative array to pass to endpoints.
   *
   * @return object
   *   Structured object.
   */
  protected function Request($data) {
    $response = $this->http_client->{$this->method}($this->Path(), $data);
    $this->http_client->close();
    return $this->Response($response);
  }

  /**
   * Preprocess HTTP response.
   *
   * @param object $response
   *   Structured object.
   *
   * @return object
   *   Preprocessed structured object.
   */
  protected function Response($response) {
    return $response;
  }

  /**
   * Sets an option on the given cURL session handle.
   * 
   * @param string $name
   *   The CURLOPT_XXX option to set.
   * @param string $value
   *   The value to be set on option.
   */
  public function SetOption($name, $value) {
    $this->http_client->setOpt($name, $value);
  }

  /**
   * Create GET request to a specified endpoint.
   *
   * @param string $path
   *   Path to API endpoint.
   * @param string $path_args
   *   Endpoint path arguments to replace tokens in the path.
   * @param string $data
   *   Raw content of the request or associative array to pass to endpoints.
   *
   * @return object
   *   Preprocessed structured object.
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
   * Create DELETE request to a specified endpoint.
   *
   * @param string $path
   *   Path to API endpoint.
   * @param string $path_args
   *   Endpoint path arguments to replace tokens in the path.
   * @param string $data
   *   Raw content of the request or associative array to pass to endpoints.
   *
   * @return object
   *   Preprocessed structured object and returns 204 No Content
   *   on success, with no response body.
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
   * Create POST request to a specified endpoint.
   *
   * @param string $path
   *   Path to API endpoint.
   * @param string $path_args
   *   Endpoint path arguments to replace tokens in the path.
   * @param string $data
   *   Raw content of the request or associative array to pass to endpoints.
   *
   * @return object
   *   Preprocessed structured object.
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
      $data_string = $content_type . $this->contents;

      // Make sure no USERAGENET in headers.
      $this->SetOption(CURLOPT_USERAGENT, NULL);
      $this->SetHeaders(
        [
          'Accept'          => 'application/json',
          'Content-Type'    => $content_type,
          'Content-Length'  => strlen($this->contents),
          'Authorization'   => $this->Authentication($data_string)
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
   *
   * @param string $path
   *   Path to a file included in the POST request.
   *
   * @return array
   *   Associative array. The array contains information about a file.
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
   *
   * @param string $content_type
   *   HTTP header field name.
   * @param array $params
   *   HTTP header attributes.
   *
   * @return string
   *   Raw HTTP header for a multipart data.
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
   *
   * @param array $files
   *   Associative array with information about each file (mimetype, filename, size).
   *
   * @return string
   *   Raw HTTP multipart data formatted according to the RFC.
   *   https://www.ietf.org/rfc/rfc2388.txt
   */
  protected function EncodeMultipart(Array $files) {
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
    foreach ($files as $info) {
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
