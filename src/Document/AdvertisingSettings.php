<?php

/**
 * @file
 * Apple News Document Advertising Settings.
 */

namespace ChapterThree\AppleNewsAPI\Document;
use ChapterThree\AppleNewsAPI\Document\Layouts\AdvertisingLayout;

/**
 * Apple News Document Advertising Settings.
 */
class AdvertisingSettings extends Base {

  protected $bannerType;
  protected $frequency;
  protected $layout;

  const BANNER_TYPE_ANY = 'any';
  const BANNER_TYPE_STANDARD = 'standard';
  const BANNER_TYPE_DOUBLE = 'double_height';
  const BANNER_TYPE_LARGE = 'large';

  /**
   * Define optional properties.
   */
  protected function optional() {
    return array_merge(parent::optional(), array(
      'bannerType',
      'frequency',
      'layout',
    ));
  }

  /**
   * Getter for bannerType.
   */
  public function getBannerType() {
    return $this->bannerType;
  }

  /**
   * Setting for bannerType.
   *
   * @param string $bannerType
   *   The banner type that should be shown. One of 'any', 'standard',
   *   'double_height', and 'large'.
   *
   * @return $this
   */
  public function setBannerType($bannerType) {
    if (!in_array($bannerType, [
        self::BANNER_TYPE_ANY,
        self::BANNER_TYPE_DOUBLE,
        self::BANNER_TYPE_LARGE,
        self::BANNER_TYPE_STANDARD,
      ])) {
      $this->triggerError('Invalid value for bannerType advertisingSettings.');
    }
    else {
      $this->bannerType = $bannerType;
    }
    return $this;
  }

  /**
   * Getter for frequency.
   */
  public function getFrequency() {
    return $this->frequency;
  }

  /**
   * Setter for frequency.
   *
   * @param int $frequency
   *   A number between 0 and 10 defining the frequency for automatically
   *   inserting advertising components into articles.
   *
   * @return $this
   */
  public function setFrequency($frequency) {
    if ($frequency >= 0 && $frequency <= 10) {
      $this->frequency = $frequency;
    }
    else {
      $this->triggerError('Invalid value for frequency advertisingSettings.');
    }
    return $this;
  }

  /**
   * Getter for layout.
   */
  public function getLayout() {
    return $this->layout;
  }

  /**
   * Setter for layout.
   *
   * @param AdvertisingLayout $layout
   *   Layout object that currently supports only margin.
   *
   * @return $this
   */
  public function setLayout(AdvertisingLayout $layout) {
    $this->layout = $layout;
    return $this;
  }
}
