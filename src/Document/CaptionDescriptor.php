<?php

/**
 * @file
 * Caption Descriptor referenced property.
 */
namespace ChapterThree\AppleNewsAPI\Document;

use ChapterThree\AppleNewsAPI\Document\Components\Text;

/**
 * An Apple News Document Caption Descriptor. Used with Galleries to support
 * HTML.
 */
class CaptionDescriptor extends Text {

  protected $additions;

  /**
   * CaptionDescriptor constructor.
   *
   * @param string $text
   *   Contains the raw caption text that needs to be displayed.
   */
  public function __construct($text) {
    $this->setText($text);
  }

  /**
   * {@inheritdoc}
   */
  protected function optional() {
    return array_merge(parent::optional(), array(
      // Note that CaptionDescriptor from Text components in that it is a
      // referenced property, not a component. Thus we mark role as optional.
      'role',
      'additions',
    ));
  }

  /**
   * @return mixed
   */
  public function getAdditions() {
    return $this->additions;
  }

  /**
   * @param mixed $additions
   */
  public function setAdditions($additions) {
    $this->additions = $additions;
  }
}
