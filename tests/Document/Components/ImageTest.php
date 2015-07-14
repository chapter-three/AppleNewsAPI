<?php

/**
 * @file
 * Tests for ChapterThree\AppleNews\Document\Components\Image.
 */

use ChapterThree\AppleNews\Document\Components\Image;

/**
 * Tests for the Image class.
 */
class ImageTest extends PHPUnit_Framework_TestCase {

  /**
   * Setting properties and outputting json.
   */
  public function testSetters() {

    $obj = new Image('http://www.freeimages.com/img/freeimages_logo.jpg');

    // Optional properties.
    $expected = '{"role":"image","url":"http://www.freeimages.com/img/freeimages_logo.jpg"}';
    $this->assertJsonStringEqualsJsonString($expected, $obj->json());

  }

}
