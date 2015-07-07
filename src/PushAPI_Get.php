<?php

/**
 * @file
 * GET Apple News Article.
 */

namespace ChapterThree\AppleNews;

/**
 * Document me.
 */
class PushAPI_Get extends PushAPI_Base {

  protected function Debug($response) {
    print_r($response);exit;
  }

}
