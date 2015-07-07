<?php

/**
 * @file
 * GET Apple News Article.
 */

namespace ChapterThree\AppleNews;

/**
 * Document me.
 */
class PushAPI_Delete extends PushAPI_Base {

  public function Delete($path, Array $arguments) {
  	parent::PreprocessRequest(__FUNCTION__, $path, $arguments);
    try {
      foreach ($this->Headers() as $prop => $val) {
        $this->curl->setHeader($prop, $val);
      }
      $this->curl->unsetHeader('Content-Type');
      $response = $this->curl->delete($this->Path());
      $this->curl->close();
      return $this->Response($response);
    }
    catch (\ErrorException $e) {
      // Need to write ClientException handling
    }
  }

}
