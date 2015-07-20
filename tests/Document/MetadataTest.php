<?php

/**
 * @file
 * Tests for ChapterThree\AppleNews\Document\Metadata.
 */

use ChapterThree\AppleNews\Document\Metadata;

/**
 * Tests for the Metadata class.
 */
class MetadataTest extends PHPUnit_Framework_TestCase {

  /**
   * Setting properties and outputting json.
   */
  public function testSetters() {

    $obj = new Metadata();

    $json = '{}';
    $this->assertEquals($json, $obj->json());

    // Test validation.
    for ($i = 0; $i < 50; $i++) {
      $obj->addKeyword('')
    }
    @$obj->setTop('67rndm');
    @$obj->setBottom('57rndm');
    $this->assertEquals($json, $obj->json());


  }

}
