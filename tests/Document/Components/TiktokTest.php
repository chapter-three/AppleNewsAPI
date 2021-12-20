<?php

/**
 * @file
 * Tests for ChapterThree\AppleNewsAPI\Document\Components\Tiktok.
 */

use ChapterThree\AppleNewsAPI\Document\Components\Tiktok;

/**
 * Tests for the Tiktok class.
 */
class TiktokTest extends PHPUnit_Framework_TestCase {

  /**
   * Setting properties and outputting json.
   */
  public function testSetters() {

    $obj = new Tiktok('https://www.tiktok.com/@applemusic/video/6564018881122276367');

    // Optional properties.
    $expected = '{"role":"tiktok","URL":"https://www.tiktok.com/@applemusic/video/6564018881122276367"}';
    $this->assertJsonStringEqualsJsonString($expected, $obj->json());

  }

}
