<?php

/**
 * @file
 * Tests for ChapterThree\AppleNews\Document\Components\Image.
 */

use ChapterThree\AppleNews\Document;
use ChapterThree\AppleNews\Document\Components;
use ChapterThree\AppleNews\Document\Components\Image;

/**
 * A test class for Text.
 */
class ImageTestClass extends Image {

  /**
   * {@inheritdoc}
   */
  public function __construct($url, $identifier = NULL) {
    parent::__construct('logo', $url, $identifier);
  }

}

/**
 * Tests for the Image class.
 */
class ImageTest extends PHPUnit_Framework_TestCase {

  /**
   * Setting properties and outputting json.
   */
  public function testSetters() {

    $obj = new ImageTestClass('bundle://test-image.jpg');

    // Optional properties.
    $expected = '{"role":"logo","URL":"bundle://test-image.jpg"}';
    $this->assertJsonStringEqualsJsonString($expected, $obj->json());

  }

}
