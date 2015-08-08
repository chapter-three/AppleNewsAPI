<?php

/**
 * @file
 * An Apple News Document Tweet.
 */

namespace ChapterThree\AppleNews\Document\Components;

/**
 * An Apple News Document Tweet.
 */
class Tweet extends Component {

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
    parent::__construct('tweet', $identifier);
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
