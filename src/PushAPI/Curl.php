<?php

/**
 * @file
 * PushAPI cURL HTTP client integration class.
 */

namespace ChapterThree\AppleNews\PushAPI;

/**
 * PushAPI cURL HTTP client.
 * 
 * @package    ChapterThree\AppleNews\PushAPI\Curl
 * @subpackage ChapterThree\AppleNews\PushAPI\Base
 */
class Curl extends Base {

  /** @var (const) PushAPI version */
  const VERSION = '1.0';

  /** @var (const) CRLF */
  const EOL = "\r\n";

  /** @var (array) Valid values for resource part Content-Type. */
  protected $valid_mimes = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/octet-stream'
  ];

  /** @var (string) Multipat data boundary unique string. */
  private $boundary;

  /**
   * Initialize variables needed in the communication with the API.
   *
   * @param (string) $key API Key.
   * @param (string) $secret API Secret Key.
   * @param (string) $endpoint API endpoint URL.
   */
  public function __construct($key, $secret, $endpoint) {
    parent::__construct($key, $secret, $endpoint);
    $this->boundary = md5(uniqid() . microtime());
  }

  /**
   * Setup HTTP client to make requests.
   */
  public function setHTTPClient() {
    // Use PHP Curl Class
    // @see https://github.com/php-curl-class/php-curl-class
    $this->client = new \Curl\Curl;
  }

  /**
   * Set HTTP headers.
   *
   * @param (array) $headers Associative array [header field name => value].
   */
  protected function setHeaders(Array $headers = []) {
    foreach ($headers as $property => $value) {
      $this->client->setHeader($property, $value);
    }
  }

  /**
   * Remove specified header names from HTTP request.
   *
   * @param (array) $headers Associative array [header1, header2, ..., headerN].
   */
  protected function unsetHeaders(Array $headers = []) {
    foreach ($headers as $property) {
      $this->client->unsetHeader($property);
    }
  }

  /**
   * Create HTTP request.
   *
   * @param (array|string) $data Raw content of the request or associative array to pass to endpoints.
   *
   * @return (object) HTTP Response object.
   */
  protected function request($data) {
    try {
      $response = $this->client->{$this->method}($this->path(), $data);
      $this->client->close();
    }
    catch (\Exception $e) {
      // Throw an expection if something goes wrong.
      $this->triggerError($e->getMessage());
    }
    finally {
      return $this->response($response);
    }
  }

  /**
   * Preprocess HTTP response.
   *
   * @param (object) $response Structured object.
   *
   * @return (object) HTTP Response object.
   */
  protected function response($response) {
    // Check for HTTP response error codes.
    if ($this->client->error) {
      $this->onErrorResponse(
        $this->client->error_code,
        $this->client->error_message,
        $response
      );
    }
    else {
      $this->onSuccessfulResponse($response);
    }
    return $response;
  }

  /**
   * Sets an option on the given cURL session handle.
   * 
   * @param (string) $name The CURLOPT_XXX option to set.
   * @param (string) $value The value to be set on option.
   */
  public function setOption($name, $value) {
    // cURL method to set options and it's values.
    $this->client->setOpt($name, $value);
  }

  /**
   * Create GET request to a specified endpoint.
   *
   * @param (string) $path API endpoint path.
   * @param (string) $path_args Endpoint path arguments to replace tokens in the path.
   * @param (string) $data Raw content of the request or associative array to pass to endpoints.
   *
   * @return object Preprocessed structured object.
   */
  public function get($path, Array $path_args = [], Array $data = []) {
    parent::get($path, $path_args, $data);
    $this->setHeaders(
      [
        'Authorization' => $this->auth()
      ]
    );
    return $this->request($data);
  }

  /**
   * Create DELETE request to a specified endpoint.
   *
   * @param (string) $path API endpoint path.
   * @param (string) $path_args Endpoint path arguments to replace tokens in the path.
   * @param (string) $data Raw content of the request or associative array to pass to endpoints.
   *
   * @return object Preprocessed structured object and returns 204 No Content on success, with no response body.
   */
  public function delete($path, Array $path_args = [], Array $data = []) {
    parent::delete($path, $path_args, $data);
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

  /**
   * Create POST request to a specified endpoint.
   *
   * @param (string) $path API endpoint path.
   * @param (array) $path_args Endpoint path arguments to replace tokens in the path.
   * @param (array) $data Raw content of the request or associative array to pass to endpoints.
   *
   * @return object Preprocessed structured object.
   */
  public function post($path, Array $path_args, Array $data = []) {
    parent::post($path, $path_args, $data);

    // JSON string to be posted to PushAPI instead of article.json file.
    $json = !empty($data['json']) ? $data['json'] : '';

    // Article assests (article.json, images, fonts etc...).
    $files = !empty($data['files']) ? $data['files'] : [];

    // Raw HTTP contents of the POST request.
    $multiparts = [];

    // Make sure you don't submit article.json if you passing json
    // as a parameter to Post method.
    if (!empty($json)) {
      $multiparts[] = $this->multipartPart(
        [
          'name'      => 'article',
          'filename'  => 'article.json',
          'mimetype'  => 'application/json',
          'size'      => strlen($json)
        ],
        'application/json',
        $json
      );
    }

    // Article metadata.
    if (!empty($data['metadata'])) {
      $multiparts[] = $this->multipartPart(
        [
          'name' => 'metadata'
        ],
        'application/json',
        $data['metadata']
      );
    }

    // Process each file and generate multipart form data.
    foreach ($files as $path) {
      // Load file information.
      $file = $this->getFileInformation($path);
      $multiparts[] = $this->multipartPart(
        [
          'filename'   => $file['filename'],
          'name'       => $file['name'],
          'size'       => $file['size']
        ],
        ($file['extension'] == 'json') ? 'application/json' : $file['mimetype'],
        $file['contents']
      );
    }

    // Set content type and boundary token.
    $content_type = sprintf('multipart/form-data; boundary=%s', $this->boundary);

    // Put together all the multipart data.
    $contents = $this->multipartFinalize($multiparts);

    // String to add to generate Authorization hash.
    $string = $content_type . $contents;

    // Make sure no USERAGENET in headers.
    $this->SetOption(CURLOPT_USERAGENT, NULL);
    $this->SetHeaders(
      [
        'Accept'          => 'application/json',
        'Content-Type'    => $content_type,
        'Content-Length'  => strlen($contents),
        'Authorization'   => $this->auth($string)
      ]
    );
    // Send POST request.
    return $this->request($contents);
  }

  /**
   * Get file information and its contents to upload.
   *
   * @param (string) $path Path to a file included in the POST request.
   *
   * @return (array) Associative array. The array contains information about a file.
   */
  protected function getFileInformation($path) {
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
      'extension' => $file['extension'],
      'mimetype'  => $mimetype,
      'contents'  => $contents,
      'size'      => strlen($contents)
    ];
  }

  /**
   * Generate individual multipart data parts.
   *
   * @param (array) $attributes Associative array with information about each file (mimetype, filename, size).
   * @param (string) $mimetype Multipart mime type.
   * @param (string) $contents Contents of the multipart content chunk.
   *
   * @return (string) Raw HTTP multipart chunk formatted according to the RFC.
   *
   * @see https://www.ietf.org/rfc/rfc2388.txt
   */
  protected function multipartPart(Array $attributes, $mimetype = null, $contents = null) {
    $multipart = '';
    $headers = [];
    foreach ($attributes as $name => $value) {
      $headers[] = $name . '=' . $value;
    }
    // Generate multipart data and contents.
    $multipart .= '--' . $this->boundary . static::EOL;
    $multipart .= 'Content-Type: ' . $mimetype . static::EOL;
    $multipart .= 'Content-Disposition: form-data; ' . join('; ', $headers) . static::EOL;
    $multipart .= static::EOL . $contents . static::EOL;
    return $multipart;
  }

  /**
   * Finalize multipart data.
   *
   * @param (array) $multiparts Multipart data with its headers.
   *
   * @return (string) Raw HTTP multipart data formatted according to the RFC.
   *
   * @see https://www.ietf.org/rfc/rfc2388.txt
   */
  protected function multipartFinalize(Array $multiparts = []) {
    $contents = '';
    foreach ($multiparts as $multipart) {
      $contents .= $multipart;
    }
    $contents .= '--' . $this->boundary  . '--';
    $contents .= static::EOL;
    return $contents;
  }

}
