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

  public $api_key_id = '';
  public $api_key_secret = '';
  public $endpoint = '';
  protected $path = '';
  protected $method = '';
  protected $arguments = [];
  public $curl;
  protected $datetime;

  /**
   *
   */
  public function __construct($key, $secret, $endpoint) {
    $this->api_key_id = $key;
    $this->api_key_secret = $secret;
    $this->endpoint = $endpoint;
    $this->curl = new \Curl\Curl;
    $this->datetime = gmdate(\DateTime::ISO8601);
  }

  /**
   * Generate HMAC cryptographic hash.
   */
  protected function HHMAC($cannonical_request) {
    $key = base64_decode($this->api_key_secret);
    $hashed = hash_hmac('sha256', $cannonical_request, $key, true);
    $signature = rtrim(base64_encode($hashed), "\n");
    return sprintf('HHMAC; key=%s; signature=%s; date=%s', $this->api_key_id, strval($signature), $this->datetime);
  }

  /**
   * Authentication.
   */
  protected function Authentication() {
    $cannonical_request = strtoupper($this->method) . $this->Path() . strval($this->datetime);
    return $this->HHMAC($cannonical_request);
  }

  /**
   * Build a path by replacing path arguments.
   */
  protected function Path() {
    $params = array();
    foreach ($this->arguments as $argument => $value) {
      $params["{{$argument}}"] = $value;
    }
    $path = str_replace(array_keys($params), array_values($params), $this->path);
    return $this->endpoint . $path;
  }

  /**
   * Preprocess request
   */
  protected function PreprocessRequest($method, $path, Array $path_args = []) {
    $this->method = $method;
    $this->arguments = $path_args;
    $this->path = $path;
  }

  /**
   * Set request headers.
   */
  protected function SetHeaders(Array $headers = []) {
    foreach ($headers as $property => $value) {
      $this->curl->setHeader($property, $value);
    }
  }

  /**
   * Unset headers.
   */
  protected function UnsetHeaders(Array $headers = []) {
    foreach ($headers as $property) {
      $this->curl->unsetHeader($property);
    }
  }

  /**
   * Request URL.
   */
  protected function Request($data) {
    $method = strtoupper($this->method);
    $response = $this->curl->{$method}($this->Path(), $data);
    $this->curl->close();
    return $this->Response($response);
  }

  /**
   * Get response.
   */
  protected function Response($response) {
    return $response;
  }

  /**
   * GET Request.
   */
  public function Get($path, Array $arguments = [], Array $data = []) {
    $this->PreprocessRequest(__FUNCTION__, $path, $arguments);
    try {
      $this->SetHeaders(['Authorization' => $this->Authentication()]);
      return $this->Request($data);
    }
    catch (\Exception $e) {
      // Need to write ClientException handling
    }
  }

  /**
   * DELETE Request.
   */
  public function Delete($path, Array $arguments = [], Array $data = []) {
    $this->PreprocessRequest(__FUNCTION__, $path, $arguments);
    try {
      $this->SetHeaders(['Authorization' => $this->Authentication()]);
      $this->UnsetHeaders(['Content-Type']);
      return $this->Request($data);
    }
    catch (\Exception $e) {
      // Need to write ClientException handling
    }
  }

  /**
   * POST Request.
   */
  public function Post($path, Array $arguments = [], Array $data = []) {
    $this->PreprocessRequest(__FUNCTION__, $path, $arguments);
    try {
      return $this->Request($data);
    }
    catch (\Exception $e) {
      // Need to write ClientException handling
    }
  }

}
