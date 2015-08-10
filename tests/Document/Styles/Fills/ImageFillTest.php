<?php

/**
 * @file
 * Tests for ChapterThree\AppleNews\Document\Styles\Fills\ImageFill.
 */
use ChapterThree\AppleNews\Document\Base;
use ChapterThree\AppleNews\Document\Styles\Fills;
use ChapterThree\AppleNews\Document\Styles\Fills\ImageFill;

/**
 * Tests for the Fill class.
 */
class ImageFillTest extends PHPUnit_Framework_TestCase {

  /**
   * Setting properties and outputting json.
   */
  public function testSetters() {

    $obj = new ImageFill('bundle://header-image.png');

    $json = '{"URL":"bundle:\/\/header-image.png","type":"image"}';

    $this->assertJsonStringEqualsJsonString($json, $obj->json());

    // Optional properties.
    $json = '{"URL":"bundle:\/\/header-image.png","fillMode":"cover","verticalAlignment":"top","horizontalAlignment":"center","type":"image","attachment":"fixed"}';
    $obj->setAttachment('fixed')
      ->setFillMode('cover')
      ->setVerticalAlignment('top')
      ->setHorizontalAlignment('center');
    $this->assertJsonStringEqualsJsonString($json, $obj->json());

  }

}
