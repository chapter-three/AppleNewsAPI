<?php

/**
 * @file
 * GET Apple News Article.
 */

namespace ChapterThree\AppleNews;

/**
 * Document me.
 */
class PushAPI_Post extends PushAPI_Base {

  public function Post($path, Array $arguments = [], Array $data) {
    $this->method = __FUNCTION__;
    $this->arguments = $arguments;
    $this->path = $path;
    if ($date === NULL) {
      $date = date('c');
    }
    if ($boundary === NULL) {
      $boundary = md5(time());
    }
    try {
      foreach (parent::Headers() as $prop => $val) {
        $this->curl->setHeader($prop, $val);
      }
      $this->curl->setHeader('Content-Type', sprintf('multipart/form-data; boundary=%s', $data['boundary']));
      $response = $this->curl->post($this->Path());
      print_r($this->curl->request_headers);
      $this->curl->close();
      return $this->Response($response);
    }
    catch (\ErrorException $e) {
      // Need to write ClientException handling
    }
  }

}
