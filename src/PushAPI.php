<?php

/**
 * @file
 * GET Apple News Article.
 */

namespace ChapterThree\AppleNews;

/**
 * Document me.
 */
class PushAPI extends PushAPI_Base {

  protected function Authentication() {
    return parent::Authentication();
  }

  protected function Response($response) {
    return parent::Response($response);
  }

  protected function Debug($response, $display = false) {
    print_r($response);exit;
  }

  public function Get($path, Array $arguments = []) {
    $response = parent::Get($path, $arguments);
    return self::Response($response);
  }

}
