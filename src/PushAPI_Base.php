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

  private $api_key_id = '';
  private $api_key_secret = '';
  private $endpoint = '';
  protected $path = '';
  protected $method = '';
  protected $arguments = [];
  protected $curl;
  protected $datetime;

  public function __construct(Array $settings) {
    $this->api_key_id = $settings['key'];
    $this->api_key_secret = $settings['secret'];
    $this->endpoint = $settings['endpoint'];
    $this->curl = new \Curl\Curl;
    $this->datetime = gmdate(\DateTime::ISO8601);
  }

  /**
   * Authentication.
   */
  protected function Authentication(Array $args = []) {
    $cannonical_request = strtoupper($this->method) . $this->Path() . strval($this->datetime);
    $key = base64_decode($this->api_key_secret);
    $signature = rtrim(base64_encode(hash_hmac('sha256', $cannonical_request, $key)), "\n");
    return sprintf('HHMAC; key=%s; signature=%s; date=%s', $this->api_key_id, strval($signature), $this->datetime);
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
  public function Headers(Array $args = []) {
    return [
      'Authorization' => $this->Authentication($args),
    ];
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
  protected function Debug($response) {
    // Debugging happenes in this method.
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

  public function Post($path, Array $arguments = [], Array $data = []) {
    // See implementation in PushAPI_Post.php
    // $response = $this->curl->post($this->Path());
  }

  public function Delete($path, Array $arguments = []) {
    // See implementation in PushAPI_Delete.php
    // $response = $this->curl->delete($this->Path());
  }

}
