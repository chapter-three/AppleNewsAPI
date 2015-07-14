<?php

/**
 * @file
 * Tests for ChapterThree\AppleNews\Document\Behaviors\BackgroundMotion.
 */

use ChapterThree\AppleNews\Document\Behaviors\BackgroundMotion;

/**
 * Tests for the BackgroundMotion class.
 */
class BackgroundMotionTest extends PHPUnit_Framework_TestCase {

  /**
   * Setting properties and outputting json.
   */
  public function testSetters() {

    $expected = '{"type":"background_motion"}';

    $obj = new BackgroundMotion();
    $this->assertJsonStringEqualsJsonString($expected, $obj->json());

  }

}
