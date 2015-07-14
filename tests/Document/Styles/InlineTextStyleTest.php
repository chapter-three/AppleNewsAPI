<?php

/**
 * @file
 * Tests for ChapterThree\AppleNews\Document\Styles\StrokeStyle.
 */

use ChapterThree\AppleNews\Document\Document;
use ChapterThree\AppleNews\Document\Styles\StrokeStyle;
use ChapterThree\AppleNews\Document\Styles\TextStyle;

/**
 * Tests for the TextStrokeStyle class.
 */
class InlineStrokeStyleTest extends PHPUnit_Framework_TestCase {

  /**
   * Setting properties and outputting json.
   */
  public function testSetters() {

    $obj = new StrokeStyle();

    $json = '{}';
    $this->assertEquals($json, $obj->json());

    // Test Validation.
    @$obj->setColor('000000');
    $this->assertEquals($json, $obj->json());
    @$obj->setColor('#00000');
    $this->assertEquals($json, $obj->json());
    @$obj->setColor('blue');
    $this->assertEquals($json, $obj->json());
    @$obj->setStyle('asdf');
    $this->assertEquals($json, $obj->json());

    // Optional properties.
    $json = '{"color":"#12345678","width":100,"style":"dashed"}';
    $obj = new StrokeStyle();
    $obj->setColor('#12345678')
      ->setWidth(100)
      ->setStyle('dashed');
    $this->assertJsonStringEqualsJsonString($json, $obj->json());

  }

}
