<?php

/**
 * @file
 * GET Apple News Article.
 */

namespace ChapterThree\AppleNews;

use GuzzleHttp\Psr7\Request;

/**
 * Document me.
 */
class PushAPI extends PushAPI_Base {

  protected function Authentication($method, $path, Array $arguments = []) {
    return parent::Authentication();
  }

  public function Request($method, $path, Array $arguments = []) {
    $data = parent::Request($method, $path, $arguments);
    return self::Response($data);
  }

  protected function Response($data) {
    return parent::Response($data);
  }

}
