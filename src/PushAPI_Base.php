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
  private $client;

  public function __construct(Array $arguments, \GuzzleHttp\ClientInterface $client) {
    $this->client = $client;
    foreach ($arguments as $argument => $value) {
      $this->{$argument} = $value;
    }
  }

  /**
   * Authentication.
   */
  protected function Authentication($method, $path, Array $arguments = []) {
    $date = date('c');
    $canonical_request = $method . $this->Path() . $date;
    $key = base64_decode($this->api_key);
    $hashed = hash_hmac('sha256', $canonical_request, $key);
    $signature = base64_encode($hashed);
    return sprintf('HHMAC; key=%s; signature=%s; date=%s', $this->api_key, $signature, $date);
  }

  /**
   * Build headers.
   */
  public function RequestData($method, $path, Array $arguments = []) {
    return [
      'headers' => [
        'Accept' => 'application/json',
        'Authorization' => $this->Authentication($method, $path, $arguments),
      ],
    ];
  }

  /**
   * Build a path by replacing path arguments.
   */
  protected function Path($path, Array $arguments = []) {
    $params = array();
    foreach ($arguments as $argument => $value) {
      $params["{{$argument}}"] = $value;
    }
    $path = str_replace(array_keys($params), array_values($params), $path);
    return $this->endpoint . $path;
  }

  /**
   * Create a request.
   */
  public function Request($method, $path, Array $arguments = []) {
    if ($method == 'GET') {
      $data = $this->client->get(
        $this->Path($path, $arguments),
        $this->RequestData($method, $path, $arguments)
      );
      return $this->Response($data);
    }
  }

  /**
   * Get response.
   */
  protected function Response($data) {
    return $data->getBody();
  }

}
