<?php

/**
 * @file
 * An Apple News Document InlineTextStyle.
 */

namespace ChapterThree\AppleNews\Document\Styles;

use ChapterThree\AppleNews\Document\Base;
use ChapterThree\AppleNews\Document;

/**
 * An Apple News Document InlineTextStyle.
 */
class InlineTextStyle extends Base {

  protected $rangeStart;
  protected $rangeLength;
  protected $textStyle;

  /**
   * Getter for rangeStart.
   */
  public function getRangeStart() {
    return $this->rangeStart;
  }

  /**
   * Setter for rangeStart.
   *
   * @param int $range_start
   *   RangeStart.
   *
   * @return $this
   */
  public function setRangeStart($range_start) {
    $this->rangeStart = $range_start;
    return $this;
  }

  /**
   * Getter for rangeLength.
   */
  public function getRangeLength() {
    return $this->rangeLength;
  }

  /**
   * Setter for rangeLength.
   *
   * @param int $range_length
   *   RangeLength.
   *
   * @return $this
   */
  public function setRangeLength($range_length) {
    $this->rangeLength = $range_length;
    return $this;
  }

  /**
   * Getter for textStyle.
   */
  public function getTextStyle() {
    return $this->textStyle;
  }

  /**
   * Setter for textStyle.
   *
   * @param string|\ChapterThree\AppleNews\Document\Styles\TextStyle $value
   *   Either a TextStyle object, or a string reference to one defined
   *   in $document.
   * @param \ChapterThree\AppleNews\Document|NULL $document
   *   If required by first parameter.
   *
   * @return $this
   */
  public function setTextStyle($value, Document $document = NULL) {
    $class = 'ChapterThree\AppleNews\Document\Styles\TextStyle';
    if (is_string($value)) {
      // Check that value exists.
      if (!$document) {
        $this->triggerError("Missing second argument");
        return $this;
      }
      if (empty($document->getTextStyles()[$value])) {
        $this->triggerError("No TextStyle \"${value}\" found.");
        return $this;
      }
    }
    elseif (!is_a($value, $class)) {
      $this->triggerError("Style not of class ${class}.");
      return $this;
    }
    $this->textStyle = $value;
    return $this;
  }

  /**
   * Implements JsonSerializable::jsonSerialize().
   */
  public function jsonSerialize() {

    if (isset($this->rangeStart) && !isset($this->rangeLength)) {
      $msg = "If rangeStart is specified, rangeLength is required.";
      $this->triggerError($msg);
      return NULL;
    }

    return parent::jsonSerialize();
  }

}
