<?php

/**
 * @file
 * Tests for ChapterThree\AppleNews\Document\Components\Header.
 */

use ChapterThree\AppleNews\Document;
use ChapterThree\AppleNews\Document\Components\Header;

/**
 * Tests for the Header class.
 */
class HeaderTest extends PHPUnit_Framework_TestCase {

  /**
   * Setting properties and outputting json.
   */
  public function testSetters() {

    $obj = new Header();

    $expected = '{"role":"header"}';
    $this->assertJsonStringEqualsJsonString($expected, $obj->json());

  }

}
