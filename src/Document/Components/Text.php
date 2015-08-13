<?php

/**
 * @file
 * An Apple News Document Text.
 */

namespace ChapterThree\AppleNews\Document\Components;

use ChapterThree\AppleNews\Document;
use ChapterThree\AppleNews\Document\Styles\InlineTextStyle;

/**
 * An Apple News Document Text.
 */
abstract class Text extends Component {

  protected $text;

  protected $format;
  protected $textStyle;
  protected $inlineTextStyles;

  /**
   * Implements __construct().
   *
   * @param mixed $role
   *   Role.
   * @param mixed $text
   *   Text.
   * @param mixed $identifier
   *   Identifier.
   */
  public function __construct($role, $text, $identifier = NULL) {
    parent::__construct($role, $identifier);
    $this->setText($text);
  }

  /**
   * {@inheritdoc}
   */
  protected function optional() {
    return array_merge(parent::optional(), array(
      'format',
      'textStyle',
      'inlineTextStyles',
    ));
  }

  /**
   * Getter for text.
   */
  public function getText() {
    return $this->text;
  }

  /**
   * Setter for text.
   *
   * @param mixed $value
   *   Text.
   *
   * @return $this
   */
  public function setText($value) {
    $this->text = (string) $value;
    return $this;
  }

  /**
   * Getter for format.
   */
  public function getFormat() {
    return $this->format;
  }

  /**
   * Setter for format.
   *
   * @param mixed $value
   *   Format.
   *
   * @return $this
   */
  public function setFormat($value) {
    $this->format = (string) $value;
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
   * @param \ChapterThree\AppleNews\Document\Styles\ComponentTextStyle|string $text_style
   *   Either a ComponentTextStyle object, or a string reference to one defined
   *   in $document.
   * @param \ChapterThree\AppleNews\Document|NULL $document
   *   If required by first parameter.
   *
   * @return $this
   */
  public function setTextStyle($text_style, Document $document = NULL) {
    $class = 'ChapterThree\AppleNews\Document\Styles\ComponentTextStyle';
    if (is_string($text_style)) {
      // Check that text_style exists.
      if (!$document) {
        $this->triggerError("Missing second argument");
        return $this;
      }
      if (empty($document->getComponentTextStyles()[$text_style])) {
        $this->triggerError("No ComponentTextStyle \"${text_style}\" found.");
        return $this;
      }
    }
    elseif (!is_a($text_style, $class)) {
      $this->triggerError("Style not of class ${class}.");
      return $this;
    }
    $this->textStyle = $text_style;
    return $this;
  }

  /**
   * Getter for inlineTextStyles.
   */
  public function getInlineTextStyles() {
    return $this->inlineTextStyles;
  }

  /**
   * Setter for inlineTextStyles.
   *
   * @param \ChapterThree\AppleNews\Document\Styles\InlineTextStyle $inline_text_style
   *   InlineTextStyle.
   *
   * @return $this
   */
  public function addInlineTextStyles(InlineTextStyle $inline_text_style) {
    $this->inlineTextStyles[] = $inline_text_style;
    return $this;
  }

}
