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

  protected $url;

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
    return $this->url;
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
    $this->url = $url;
    return $this;
  }

}
