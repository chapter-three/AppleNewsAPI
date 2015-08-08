<?php

/**
 * @file
 * An Apple News Document Music.
 */

namespace ChapterThree\AppleNews\Document\Components;

/**
 * An Apple News Document Music.
 */
class Music extends Audio {

  /**
   * Implements __construct().
   *
   * @param mixed $role
   *   Role.
   * @param mixed $url
   *   URL.
   * @param mixed $identifier
   *   Identifier.
   */
  public function __construct($role, $url, $identifier = NULL) {
    parent::__construct('music', $identifier);
    $this->setUrl($url);
  }

}
