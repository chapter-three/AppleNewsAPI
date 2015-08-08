<?php

/**
 * @file
 * An Apple News Document Chapter.
 */

namespace ChapterThree\AppleNews\Document\Components;

use ChapterThree\AppleNews\Document\Base;
use ChapterThree\AppleNews\Document\Animations\Scenes\Scene;

/**
 * An Apple News Document Chapter.
 */
class Chapter extends ContainerBase {

  protected $scene;

  /**
   * Implements __construct().
   *
   * @param mixed $identifier
   *   Identifier.
   */
  public function __construct($identifier = NULL, $role = NULL) {
    return parent::__construct('chapter', $identifier);
  }

  /**
   * {@inheritdoc}
   */
  protected function optional() {
    return array_merge(parent::optional(), array(
      'scene',
    ));
  }

  /**
   * Getter for scene.
   */
  public function getScene() {
    return $this->scene;
  }

  /**
   * Setter for scene.
   *
   * @param \ChapterThree\AppleNews\Document\Animations\Scenes\Scene $scene
   *   Scene.
   *
   * @return $this
   */
  public function setScene(Scene $scene) {
    $this->scene = $scene;
    return $this;
  }

}
