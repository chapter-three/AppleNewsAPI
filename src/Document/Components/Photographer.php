<?php

/**
 * @file
 * An Apple News Document Photographer.
 */

namespace ChapterThree\AppleNews\Document\Components;

/**
 * An Apple News Document Photographer Component.
 */
class Photographer extends Text {

  /**
   * Implements __construct().
   *
   * @param mixed $text
   *   Text.
   * @param mixed $identifier
   *   Identifier.
   */
  public function __construct($text, $identifier = NULL) {
    return parent::__construct('photographer', $text, $identifier);
  }

}
