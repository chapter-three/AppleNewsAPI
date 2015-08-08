<?php

/**
 * @file
 * An Apple News Document FadingStickyHeader.
 */

namespace ChapterThree\AppleNews\Document\Animations\Scenes;

use ChapterThree\AppleNews\Document\Base;

/**
 * An Apple News Document FadingStickyHeader.
 *
 * @property $type
 */
class FadingStickyHeader extends Scene {

  /**
   * Implements __construct().
   */
  public function __construct() {
    parent::__construct('fading_sticky_header');
  }

}
