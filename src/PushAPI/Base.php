<?php

/**
 * @file
 * Base class for AppleNews classes.
 */

namespace ChapterThree\AppleNews\PushAPI;

/**
 * Base class for AppleNews classes.
 */
class Base extends PushAPI {

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
  protected function Authentication() {
    $cannonical_request = strtoupper($this->method) . $this->Path() . strval($this->datetime);
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
  public function Post($path, Array $path_args = [], Array $data = []) {
    $this->PreprocessData(__FUNCTION__, $path, $path_args, $data);
    try {
      return $this->Request($data);
    }
    catch (\Exception $e) {
      // Need to write ClientException handling
    }
  }

}
