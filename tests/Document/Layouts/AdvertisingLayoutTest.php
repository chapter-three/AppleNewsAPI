<?php

/**
 * @file
 * Tests for ChapterThree\AppleNewsAPI\Document\Layouts\AdvertisingLayout
 */

use ChapterThree\AppleNewsAPI\Document\Layouts\AdvertisingLayout;
use ChapterThree\AppleNewsAPI\Document\Margin;

class AdvertisingLayoutTest extends PHPUnit_Framework_TestCase {

  /**
   * Settings properties and outputting JSON.
   */
  public function testSetters() {
    $expected = '{"margin":{"top":10,"bottom":10}}';
    $obj = new AdvertisingLayout();
    $obj->setMargin(new Margin(10, 10));
    $this->assertEquals($expected, $obj->json());
  }
}
