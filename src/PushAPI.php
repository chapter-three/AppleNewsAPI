<?php

/**
 * @file
 * AppleNews integration class.
 */

namespace ChapterThree\AppleNews;

/**
 * PushAPI
 *
 * The Push API is a RESTful API that allows you to publish articles.
 * You can also retrieve and delete articles you've already published, 
 * and get basic information about your channel and sections.
 * 
 * @package    ChapterThree\AppleNews\PushAPI
 * @subpackage ChapterThree\AppleNews\Base
 */
class PushAPI extends Base {

  /** @var (const) CRLF */
  const EOL = "\r\n";

  /** @var (array) Valid values for resource part Content-Type. */
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

  /** @var (string) Multipat form data boundary unique string. */
  private $boundary;

  /** @var (string) Raw HTTP request Content to POST to the API. */
  private $contents;

  /** @var (string) Additional metadata to post to the API. */
  private $metadata;

  /** @var (string) JSON string to be posted to PushAPI instead of article.json file. */
  private $json;

  /** @var (array) Array of files paths to submit. Article assets e.g. images, fonts etc.. */
  private $files = [];

  /** @var (array) Multipart data. */
  private $multipart = [];

  /**
   * Setup HTTP client to make requests.
   */
  public function setHTTPClient() {
    // Use PHP Curl Class
    // @see https://github.com/php-curl-class/php-curl-class
    $this->http_client = new \Curl\Curl;
  }

  /**
   * Initialize variables needed to make a request.
   *
   * @param (string) $method Request method (POST/GET/DELETE).
   * @param (string) $path Path to API endpoint.
   * @param (array) $path_args Endpoint path arguments to replace tokens in the path.
   * @param (array) $data Data to pass to the endpoint (expect for POST, see $this->Post()).
   */
  protected function initVars($method, $path, Array $path_args, Array $data) {
    // Set endpoint paths defined in abstract class and used to create requests.
    parent::initVars($method, $path, $path_args, $data);
    $this->boundary = md5(uniqid() . microtime());
    $this->metadata = !empty($data['metadata']) ? $data['metadata'] : '';
    $this->json = !empty($data['json']) ? $data['json'] : '';
    $this->files = !empty($data['files']) ? $data['files'] : [];
  }

  /**
   * Create GET request to a specified endpoint.
   *
   * @param (string) $path Path to API endpoint.
   * @param (string) $path_args Endpoint path arguments to replace tokens in the path.
   * @param (string) $data Raw content of the request or associative array to pass to endpoints.
   *
   * @return object Preprocessed structured object.
   */
  public function get($path, Array $path_args = [], Array $data = []) {
    parent::get($path, $path_args, $data);
    try {
      $this->setHeaders(
        [
          'Authorization' => $this->auth()
        ]
      );
      return $this->request($data);
    }
    catch (Exception $e) {
      $this->triggerError($e->getMessage());
    }
  }

  /**
   * Create DELETE request to a specified endpoint.
   *
   * @param (string) $path Path to API endpoint.
   * @param (string) $path_args Endpoint path arguments to replace tokens in the path.
   * @param (string) $data Raw content of the request or associative array to pass to endpoints.
   *
   * @return object Preprocessed structured object and returns 204 No Content on success, with no response body.
   */
  public function delete($path, Array $path_args = [], Array $data = []) {
    parent::delete($path, $path_args, $data);
    try {
      $this->setHeaders(
        [
          'Authorization' => $this->auth()
        ]
      );
      $this->unsetHeaders(
        [
          'Content-Type'
        ]
      );
      return $this->request($data);
    }
    catch (Exception $e) {
      $this->triggerError($e->getMessage());
    }
  }

  /**
   * Create POST request to a specified endpoint.
   *
   * @param (string) $path Path to API endpoint.
   * @param (array) $path_args Endpoint path arguments to replace tokens in the path.
   * @param (array) $data Raw content of the request or associative array to pass to endpoints.
   *
   * @return object Preprocessed structured object.
   */
  public function post($path, Array $path_args, Array $data = []) {
    parent::post($path, $path_args, $data);
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
        $this->multipart[] = $this->addToMultipart($file);
      }

      // Set content type and boundary token.
      $content_type = sprintf('multipart/form-data; boundary=%s', $this->boundary);

      // Generated multipart data to POST.
      $this->contents = $this->encodeMultipart($this->multipart);
      // String to add to generate Authorization hash.
      $data_string = $content_type . $this->contents;

      // Make sure no USERAGENET in headers.
      $this->SetOption(CURLOPT_USERAGENT, NULL);
      $this->SetHeaders(
        [
          'Accept'          => 'application/json',
          'Content-Type'    => $content_type,
          'Content-Length'  => strlen($this->contents),
          'Authorization'   => $this->auth($data_string)
        ]
      );
      // Send POST request.
      return $this->request($this->contents);
    }
    catch (Exception $e) {
      $this->triggerError($e->getMessage());
    }
  }

  /**
   * Load files and prepare them for multipart form data request.
   *
   * @param (string) $path Path to a file included in the POST request.
   *
   * @return (array) Associative array. The array contains information about a file.
   */
  protected function addToMultipart($path) {
    $file = pathinfo($path);

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimetype = finfo_file($finfo, $path);
    if (!in_array($mimetype, $this->valid_mimes)) {
      if ($mimetype == 'text/plain') {
        $mimetype = 'application/octet-stream';
      }
      else {
        $this->triggerError('Unsupported mime type: ' . $mimetype);
      }
    }

    $contents = file_get_contents($path);

    return [
      'name'      => str_replace(' ', '-', $file['filename']),
      'filename'  => $file['basename'],
      'mimetype'  => ($file['extension'] == 'json') ? 'application/json' : $mimetype,
      'contents'  => $contents,
      'size'      => strlen($contents)
    ];
  }

  /**
   * Generate Multipart data headers.
   *
   * @param (string) $content_type HTTP header field name.
   * @param (array) $params HTTP header attributes.
   *
   * @return string Raw HTTP header for a multipart data.
   */
  protected function buildMultipartHeaders($content_type, Array $params) {
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
   * @param (array) $files Associative array with information about each file (mimetype, filename, size).
   *
   * @return (string) Raw HTTP multipart data formatted according to the RFC.
   *
   * @see https://www.ietf.org/rfc/rfc2388.txt
   */
  protected function encodeMultipart(Array $files) {
    $multipart = '';
    // Adding metadata to multipart
    if (!empty(json_decode($this->metadata))) {
      $multipart .= '--' . $this->boundary . static::EOL;
      $multipart .= $this->buildMultipartHeaders('application/json',
        [
          'name' => 'metadata'
        ]
      );
      $multipart .= static::EOL . $this->metadata . static::EOL;
    }
    // Add files
    foreach ($files as $file) {
      $multipart .= '--' . $this->boundary . static::EOL;
      $multipart .= $this->buildMultipartHeaders($file['mimetype'],
        [
          'filename'   => $file['filename'],
          'name'       => $file['name'],
          'size'       => $file['size']
        ]
      );
      $multipart .= static::EOL . $file['contents'] . static::EOL;
    }
    $multipart .= '--' . $this->boundary  . '--';
    $multipart .= static::EOL;
    return $multipart;
  }

}
