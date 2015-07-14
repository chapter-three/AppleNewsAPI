<?php

/**
 * @file
 * Tests for ChapterThree\AppleNews\Document\Components\Body.
 */

use ChapterThree\AppleNews\Document\Components\Body;

/**
 * Tests for the Body class.
 */
class BodyTest extends PHPUnit_Framework_TestCase {

  /**
   * Setting properties and outputting json.
   */
  public function testSetters() {

    $obj = new Body('some body text.');

    // Optional properties.
    $expected = '{"role":"body","text":"some body text."}';
    $this->assertJsonStringEqualsJsonString($expected, $obj->json());

  }

}
