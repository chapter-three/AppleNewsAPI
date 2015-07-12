<?php

/**
 * @file
 * AppleNews PushAPI abstract class.
 */

namespace ChapterThree\AppleNews;

/**
 * PushAPI Abstract class
 * 
 * @package    ChapterThree\AppleNews\Base
 */
abstract class Base {

  /** @var (string) PushAPI API Key ID. */
  public $api_key_id = '';

  /** @var (string) Push API Secret Key. */
  public $api_key_secret = '';

  /** @var (string) PushAPI Endpoint URL. */
  public $endpoint = '';

  /** @var (object) HTTP client class. */
  public $http_client;

  /** @var (string) Endpoint path. */
  protected $path = '';

  /** @var (string) HTTP Method (GET/DELETE/POST). */
  protected $method = '';

  /** @var (array) Endpoint path variables to replace. */
  protected $path_args = [];

  /** @var (datetime) ISO 8601 datetime. */
  protected $datetime;

  /**
   * Initialize variables needed in the communication with the API.
   *
   * @param (string) $key API Key.
   * @param (string) $secret API Secret Key.
   * @param (string) $endpoint API endpoint URL.
   */
  public function __construct($key, $secret, $endpoint) {
    // Set API required variables.
    $this->api_key_id = $key;
    $this->api_key_secret = $secret;
    $this->endpoint = $endpoint;
    // ISO 8601 date and time format.
    $this->datetime = gmdate(\DateTime::ISO8601);
    // Initialize HTTP client.
    $this->SetHTTPClient();
  }

  /**
   * Setup HTTP client to make requests.
   */
  public function setHTTPClient() {
    // Example: $this->http_client = new \Curl\Curl;
    $this->triggerError('No HTTP Client found', E_USER_ERROR);
  }

  /**
   * Generate HMAC cryptographic hash.
   *
   * @param (string) $data Message to be hashed.
   *
   * @return (string) Authorization token used in the HTTP headers.
   */
  final protected function hhmac($data = '') {
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
   * @param (string) $string String to concatenate (see POST method).
   *
   * @return (string) HMAC cryptographic hash
   */
  final protected function auth($string = '') {
    $data = strtoupper($this->method) . $this->path() . strval($this->datetime) . $string;
    return $this->hhmac($data);
  }

  /**
   * Generate HTTP request URL.
   *
   * @return (string) URL to create request.
   */
  protected function path() {
    $params = [];
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
   * @param (string) $method Request method (POST/GET/DELETE).
   * @param (string) $path Path to API endpoint.
   * @param (array) $path_args Endpoint path arguments to replace tokens in the path.
   * @param (array) $data Data to pass to the endpoint.
   *
   * @see PushAPI::post().
   */
  protected function initVars($method, $path, Array $path_args, Array $data) {
    $this->method = $method;
    $this->path_args = $path_args;
    $this->path = $path;
  }

  /**
   * Set HTTP headers.
   *
   * @param (array) $headers Associative array [header field name => value].
   */
  protected function setHeaders(Array $headers = []) {
    foreach ($headers as $property => $value) {
      $this->http_client->setHeader($property, $value);
    }
  }

  /**
   * Remove specified header names from HTTP request.
   *
   * @param (array) $headers Associative array [header1, header2, ..., headerN].
   */
  protected function unsetHeaders(Array $headers = []) {
    foreach ($headers as $property) {
      $this->http_client->unsetHeader($property);
    }
  }

  /**
   * Create HTTP request.
   *
   * @param (array|string) $data Raw content of the request or associative array to pass to endpoints.
   *
   * @return (object) Structured object.
   */
  protected function request($data) {
    try {
      $response = $this->http_client->{$this->method}($this->path(), $data);
      $this->http_client->close();
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
   * @return (object) Preprocessed structured object.
   */
  protected function response($response) {
    // Check for HTTP response error codes.
    if ($this->http_client->error) {
      $this->onErrorResponse(
        $this->http_client->error_code,
        $this->http_client->error_message,
        $response
      );
    }
    return $response;
  }

  /**
   * Log HTTP response error messages.
   *
   * @param (int) $error_code HTTP status code.
   * @param (string) $error_message HTTP status message.
   * @param (object) $response Structured object.
   */
  protected function onErrorResponse($error_code, $error_message, $response) {
    $message = print_r(
      [
        'code'      => $error_code,
        'message'   => $error_message,
        'response'  => $response
      ],
      true
    );
    $this->triggerError($message);
  }

  /**
   * Sets an option on the given cURL session handle.
   * 
   * @param (string) $name The CURLOPT_XXX option to set.
   * @param (string) $value The value to be set on option.
   */
  public function setOption($name, $value) {
    // cURL method to set options and it's values.
    $this->http_client->setOpt($name, $value);
  }

  /**
   * Create GET request to a specified endpoint.
   *
   * @param (string) $path Path to API endpoint.
   * @param (array) $path_args Endpoint path arguments to replace tokens in the path.
   * @param (array) $data Raw content of the request or associative array to pass to endpoints.
   *
   * @return object Preprocessed structured object.
   */
  public function get($path, Array $path_args, Array $data) {
    $this->initVars(__FUNCTION__, $path, $path_args, $data);
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
  public function post($path, Array $path_args, Array $data) {
    $this->initVars(__FUNCTION__, $path, $path_args, $data);
  }

  /**
   * Create DELETE request to a specified endpoint.
   *
   * @param (string) $path Path to API endpoint.
   * @param (array) $path_args Endpoint path arguments to replace tokens in the path.
   * @param (array) $data Raw content of the request or associative array to pass to endpoints.
   *
   * @return object Preprocessed structured object and returns 204 No Content on success, with no response body.
   */
  public function delete($path, Array $path_args, Array $data) {
    $this->initVars(__FUNCTION__, $path, $path_args, $data);
  }

  /**
   * Implements __get().
   *
   * @param (mixed) $name Property name.
   */
  public function __get($name) {
    return $this->$name;
  }

  /**
   * Implements __set().
   *
   * @param (mixed) $name Property name.
   * @param (mixed) $value Property value. 
   */
  public function __set($name, $value) {
    $this->triggerError('Undefined property via __set(): ' . $name);
    return NULL;
  }

  /**
   * Implements __isset().
   *
   * @param (mixed) $name Property name.
   */
  public function __isset($name) {
    return isset($this->$name);
  }

  /**
   * Implements __unset().
   *
   * @param (mixed) $name Property name.
   */
  public function __unset($name) {
    unset($this->$name);
  }

  /**
   * Error handler.
   *
   * @param (string) $message Error message to display.
   * @param (const) $message_type Predefined Constants
   *
   * @see http://php.net/manual/en/errorfunc.constants.php
   */
  public function triggerError($message, $message_type = E_USER_NOTICE) {
    $trace = next(debug_backtrace());
    $message = sprintf('%s in %s on line %d', $message, $trace['file'], $trace['line']);
    trigger_error($message, $message_type);
  }

}
