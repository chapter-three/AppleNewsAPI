<?php

/**
 * @file
 * An Apple News Document Header.
 */

namespace ChapterThree\AppleNews\Document\Components;

/**
 * An Apple News Document Header.
 */
class Header extends ContainerBase {

  /**
   * Implements __construct().
   *
   * @param mixed $identifier
   *   Identifier.
   */
  public function __construct($identifier = NULL) {
    return parent::__construct('header', $identifier);
  }

}
