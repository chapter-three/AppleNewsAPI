<?php

/**
 * @file
 * An Apple News Document BannerAdvertisement.
 */

namespace ChapterThree\AppleNews\Document\Components\Advertisements;

use ChapterThree\AppleNews\Document\Base;
use ChapterThree\AppleNews\Document\Components;

/**
 * An Apple News Document BannerAdvertisement.
 */
class BannerAdvertisement extends Component {

  protected $bannerType;

  /**
   * Implements __construct().
   *
   * @param mixed $identifier
   *   Identifier.
   */
  public function __construct($identifier = NULL) {
    parent::__construct('banner_advertisement', $identifier);
  }

  /**
   * {@inheritdoc}
   */
  protected function optional() {
    return array_merge(parent::optional(), array(
      'bannerType',
    ));
  }

  /**
   * Getter for bannerType.
   */
  public function getBannerType() {
    return $this->bannerType;
  }

  /**
   * Setter for bannerType.
   *
   * @param mixed $bannerType
   *   bannerType.
   *
   * @return $this
   */
  public function setBannerType($bannerType) {
    $this->bannerType = $bannerType;
    return $this;
  }

}
