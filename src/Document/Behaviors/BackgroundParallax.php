<?php

/**
 * @file
 * An Apple News Document BackgroundParallax.
 */

namespace ChapterThree\AppleNews\Document\Behaviors;

/**
 * An Apple News Document BackgroundParallax.
 *
 * @property $type
 */
class BackgroundParallax extends Behavior {

  /**
   * Implements __construct().
   */
  public function __construct() {
    return parent::__construct('background_parallax');
  }

}
