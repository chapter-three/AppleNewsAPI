<?php

/**
 * @file
 * Tests for ChapterThree\AppleNews\Document\Components\Component.
 */

use ChapterThree\AppleNews\Document\Components\Component;
use ChapterThree\AppleNews\Document\Layouts\Layout;
use ChapterThree\AppleNews\Document\Layouts\ComponentLayout;
use ChapterThree\AppleNews\Document;

/**
 * A test class for Component.
 */
class ComponentTestClass extends Component {

  /**
   * {@inheritdoc}
   */
  public function __construct($identifier = NULL) {
    return parent::__construct('role', $identifier);
  }

}

/**
 * Tests for the Component class.
 */
class ComponentTest extends PHPUnit_Framework_TestCase {

  /**
   * Setting properties and outputting json.
   */
  public function testSetters() {

    $obj = new ComponentTestClass();

    $json = '{"role":"role"}';
    $this->assertEquals($json, $obj->json());

    // Test assigning document level objects.
    $json = '{"role":"role","layout":"key"}';
    $layout = new ComponentLayout();
    $document = new Document('1', 'title', 'en-us', new Layout(2, 512));
    $document->addComponentLayout('key', $layout);
    $obj->setLayout('key', $document);
    $this->assertEquals($json, $obj->json());
    @$obj->setLayout('invalid key', $document);
    $this->assertEquals($json, $obj->json());

  }

}
