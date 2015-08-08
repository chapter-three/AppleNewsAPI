<?php

/**
 * @file
 * An Apple News Document Instagram.
 */

namespace ChapterThree\AppleNews\Document\Components;

/**
 * An Apple News Document Instagram.
 */
class Instagram extends Component {

  protected $URL;

  /**
   * Implements __construct().
   *
   * @param string $url
   *   Role.
   * @param mixed $identifier
   *   Identifier.
   */
  public function __construct($url, $identifier = NULL) {
    parent::__construct('instagram', $identifier);
    $this->setUrl($url);
  }

  /**
   * Getter for url.
   */
  public function getUrl() {
    return $this->URL;
  }

  /**
   * Setter for url.
   *
   * @param mixed $url
   *   Url.
   *
   * @return $this
   */
  public function setUrl($url) {
    $this->URL = $url;
    return $this;
  }

}
