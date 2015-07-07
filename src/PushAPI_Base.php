<?php

/**
 * @file
 * Base class for AppleNews classes.
 */

namespace ChapterThree\AppleNews;

/**
 * Base class for AppleNews classes.
 */
class PushAPI_Base extends PushAPI_Abstract {

  private $api_key = '';
  protected $endpoint = '';
  protected $path = '';
  protected $method = '';
  protected $arguments = [];
  protected $curl;

  public function __construct($api_key, $endpoint) {
    $this->api_key = $api_key;
    $this->endpoint = $endpoint;
    $this->curl = new \Curl\Curl;
  }

  /**
   * Authentication.
   */
  protected function Authentication() {
    $date = new \DateTime();
    $date->setTimezone(new \DateTimeZone('America/Los_Angeles'));
    $datetime = $date->format('c'); // 'Y-m-d\TH:i:s\Z'
    $hashed = hash_hmac('sha256', strtoupper($this->method) . $this->Path() . $datetime, base64_decode($this->api_key));
    return sprintf('HHMAC; key=%s; signature=%s; date=%s', $this->api_key, base64_encode($hashed), $datetime);
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
   * Build headers.
   */
  public function Headers() {
    $headers = [
      'GET' => [
        'Accept' => 'application/json',
        'Authorization' => $this->Authentication(),
      ],
      'POST' => [
        'Authorization' => $this->Authentication(),
      ],
      'DELETE' => [
        'Authorization' => $this->Authentication(),
      ],
    ];
    $method = strtoupper($this->method);
    return isset($headers[$method]) ? $headers[$method] : $headers;
  }

  /**
   * Get response.
   */
  protected function Response($response) {
    $this->Debug($response);
    return $response;
  }

  /**
   * Debug HTTP response, implement this method to see debugging information.
   */
  protected function Debug($response, $display = false) {
    if ($display) {
      var_dump($response);
    }
  }

  /**
   * Create GET request.
   */
  public function Get($path, Array $arguments = []) {
    $this->method = __FUNCTION__;
    $this->arguments = $arguments;
    $this->path = $path;
    try {
      foreach ($this->Headers() as $prop => $val) {
        $this->curl->setHeader($prop, $val);
      }
      $response = $this->curl->get($this->Path());
      $this->curl->close();
      return $this->Response($response);
    }
    catch (\ErrorException $e) {
      // Need to write ClientException handling
    }
  }

  public function Post($path, Array $arguments = [], Array $files = [], $date = NULL, $boundary = NULL) {
    $this->method = __FUNCTION__;
    $this->arguments = $arguments;
    $this->path = $path;
  }

  public function Delete($path, Array $arguments = []) {
    $this->method = __FUNCTION__;
    $this->arguments = $arguments;
    $this->path = $path;
  }

}
