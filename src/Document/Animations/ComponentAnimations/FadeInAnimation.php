<?php

/**
 * @file
 * An Apple News Document FadeInAnimation.
 */

namespace ChapterThree\AppleNews\Document\Animations\ComponentAnimations;

/**
 * An Apple News Document FadeInAnimation.
 */
class FadeInAnimation extends ComponentAnimation {

  protected $initialAlpha;

  /**
   * Implements __construct().
   */
  public function __construct() {
    return parent::__construct('fade_in');
  }

  /**
   * Define optional properties.
   */
  protected function optional() {
    return array_merge(parent::optional(), array(
      'initialAlpha',
    ));
  }

  /**
   * Getter for initialAlpha.
   */
  public function getInitialAlpha() {
    return $this->initialAlpha;
  }

  /**
   * Setter for initialAlpha.
   *
   * @param float|int $value
   *   initialAlpha.
   *
   * @return $this
   */
  public function setInitialAlpha($value) {
    if ($this->validateInitialAlpha($value)) {
      $this->initialAlpha = $value;
    }
    return $this;
  }

  /**
   * Implements JsonSerializable::jsonSerialize().
   */
  public function jsonSerialize() {
    $valid = !isset($this->initialAlpha) ||
      $this->validateInitialAlpha($this->initialAlpha);
    if (!$valid) {
      return NULL;
    }
    return parent::jsonSerialize();
  }

  /**
   * Validates the initialAlpha attribute.
   */
  protected function validateInitialAlpha($value) {
    if (!$this->isUnitInterval($value)) {
      $this->triggerError('initialAlpha is not a Unit Interval');
      return FALSE;
    }
    return TRUE;
  }

}
