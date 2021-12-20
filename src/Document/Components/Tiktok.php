<?php

/**
 * @file
 * An Apple News Document Tiktok Post.
 */

namespace ChapterThree\AppleNewsAPI\Document\Components;

/**
 * An Apple News Document Tiktok Post.
 */
class Tiktok extends Component {

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
    parent::__construct('tiktok', $identifier);
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
   * @param mixed $value
   *   Url.
   *
   * @return $this
   */
  public function setUrl($value) {
    $this->URL = $value;
    return $this;
  }

}
