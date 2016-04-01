<?php

/**
 * @file
 * Tests for ChapterThree\AppleNewsAPI\Document\Styles\OffsetTest.
 */

use ChapterThree\AppleNewsAPI\Document\Styles\Offset;

/**
 * Tests for the OffsetTest class.
 */
class OffsetTest extends PHPUnit_Framework_TestCase {

  /**
   * Setting properties and outputting json.
   */
  public function testSetters() {

    $obj = new Offset();

    $json = '{}';
    $this->assertEquals($json, $obj->json());

    // Test Validation.
    @$obj->setX('asdf');
    $this->assertEquals($json, $obj->json());
    @$obj->setY('asdf');
    $this->assertEquals($json, $obj->json());

    $json = '{"x":50,"y":-50}';

    $obj->setX('50');
    $obj->setY('-50');
    $this->assertJsonStringEqualsJsonString($json, $obj->json());

  }

}
