<?php

/**
 * @file
 * An Apple News Document Container Base Class.
 *
 * Need this because we don't want to expose "role" attribute in classes that
 * extend Container.
 */

namespace ChapterThree\AppleNews\Document\Components;

/**
 * An Apple News Document Container Base Class.
 */
abstract class ContainerBase extends Component {

  protected $components;

  /**
   * Implements __construct().
   *
   * @param mixed $role
   *   Role.
   * @param mixed $identifier
   *   Identifier.
   */
  public function __construct($role, $identifier = NULL) {
    return parent::__construct($role, $identifier);
  }

  /**
   * Define optional properties.
   */
  protected function optional() {
    return array_merge(parent::optional(), array(
      'components',
    ));
  }

  /**
   * Getter for components.
   */
  public function getComponents() {
    return $this->components;
  }

  /**
   * Setter for components.
   *
   * @param Component $component
   *   Component.
   *
   * @return $this
   */
  public function addComponent(Component $component) {
    $this->components[] = $component;
    return $this;
  }

}
