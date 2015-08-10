<?php

/**
 * @file
 * An Apple News Document GradientFill.
 */

namespace ChapterThree\AppleNews\Document\Styles\Fills\Gradients;

use ChapterThree\AppleNews\Document\Base;
use ChapterThree\AppleNews\Document\Styles\Fills;
use ChapterThree\AppleNews\Document\Styles\Fills\Fill;

/**
 * An Apple News Document GradientFill.
 */
abstract class GradientFill extends Fill {

  protected $colorStops;

  /**
   * Implements __construct().
   *
   * @param string $type
   *   The type of gradient; e.g., linear_gradient.
   * @param array|\ChapterThree\AppleNews\Document\Styles\Fills\Gradients\ColorStop $colorStops
   *   URL.
   */
  public function __construct($type, array $colorStops) {
    parent::__construct($type);
    $this->setColorStops($colorStops);
  }

  /**
   * Getter for url.
   */
  public function getColorStops() {
    return $this->colorStops;
  }

  /**
   * Setter for url.
   *
   * @param array|\ChapterThree\AppleNews\Document\Styles\Fills\Gradients\ColorStop $items
   *   An array of color stops. Each stop sets a color and percentage.
   *
   * @return $this
   */
  public function setColorStops(array $items) {
    if (isset($items[0]) && 
        is_object($items[0]) &&
        !is_a($items[0], '\ChapterThree\AppleNews\Document\Styles\Fills\Gradients\ColorStop')
    ) {
      $this->triggerError('Object not of type Gradients\ColorStop');
    }
    else {
      $this->colorStops = $items;
    }
    return $this;
  }

}
