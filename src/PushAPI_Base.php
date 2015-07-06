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
  private $endpoint = '';
  private $path = '';
  private $method = 'GET';
  private $arguments = [];
  private $client;

  public function __construct($api_key, $client, \GuzzleHttp\ClientInterface $client) {
    $this->api_key = $api_key;
    $this->endpoint = $endpoint;
    $this->client = $client;
  }

  /**
   * Authentication.
   */
  protected function Authentication() {
    $date = date('c');
    $hashed = hash_hmac('sha256', $this->method . $this->Path() . $date, base64_decode($this->api_key));
    return sprintf('HHMAC; key=%s; signature=%s; date=%s', $this->api_key, base64_encode($hashed), $date);
  }

  /**
   * Build headers.
   */
  public function RequestParams() {
    return [
      'headers' => [
        'Accept' => 'application/json',
        'Authorization' => $this->Authentication(),
      ],
    ];
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
   * Create a request.
   */
  public function Request($method, $path, Array $arguments = []) {
    $this->method = $method;
    $this->arguments = $arguments;
    $this->path = $path;
    if ($method == 'GET') {
      $data = $this->client->get(
        $this->Path(),
        $this->RequestParams()
      );
      return $this->Response($data);
    }
  }

  /**
   * Get response.
   */
  protected function Response($data) {
    return $data;
  }

}
