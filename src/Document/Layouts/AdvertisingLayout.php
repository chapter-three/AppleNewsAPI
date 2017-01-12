<?php

/**
 * @file
 * An Apple News Document Advertising Layout.
 */

namespace ChapterThree\AppleNewsAPI\Document\Layouts;

use ChapterThree\AppleNewsAPI\Document\Base;
use ChapterThree\AppleNewsAPI\Document\Margin;

/**
 * An Apple News Document Advertising Layout.
 */
class AdvertisingLayout extends Base {

  protected $margin;

  /**
   * Getter for margin.
   */
  public function getMargin() {
    return $this->margin;
  }

  /**
   * Setter for margin.
   *
   * @param Margin $value
   *   Margin.
   *
   * @return $this
   */
  public function setMargin($value) {
    if (is_object($value) && !$value instanceof Margin) {
      $this->triggerError('Object not of type Margin');
    }
    else {
      $this->margin = $value;
    }
    return $this;
  }
}
