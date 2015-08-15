<?php

/**
 * @file
 * An Apple News Document Component with child components.
 */

namespace ChapterThree\AppleNews\Document\Components;

/**
 * An Apple News Document Component with child components.
 */
abstract class ComponentNested extends Component {

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
   * Gets nested components as a flattened list.
   *
   * @return array
   *   List of \ChapterThree\AppleNews\Document\Components\Component.
   */
  public function getComponentsFlattened() {
    $components = [];
    foreach ($this->getComponents() as $component) {
      $components[] = $component;
      /** @var \ChapterThree\AppleNews\Document\Components\ComponentNested $component */
      if (is_a($component, '\ChapterThree\AppleNews\Document\Components\ComponentNested')) {
        $descendants = $component->getComponentsFlattened();
        array_merge($components, $descendants);
      }
    }
    return $components;
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
