<?php

/**
 * @file
 * Tests for ChapterThree\AppleNewsAPI\Document\AdvertisingSettings.
 */

use ChapterThree\AppleNewsAPI\Document\AdvertisingSettings;
use ChapterThree\AppleNewsAPI\Document\Layouts\AdvertisingLayout;
use ChapterThree\AppleNewsAPI\Document\Margin;

class AdvertisingSettingsTest extends PHPUnit_Framework_TestCase {

  /**
   * Setting properties and outputting JSON.
   */
  public function testSetters() {
    $obj = new AdvertisingSettings();

    $expected = '{}';
    $this->assertEquals($expected, $obj->json());

    $expected = '{"bannerType":"large","frequency":5,"layout":{"margin":{"top":10,"bottom":10}}}';
    $obj->setBannerType('any');
    $obj->setBannerType('double_height');
    $obj->setBannerType('standard');
    $obj->setBannerType('large');

    $obj->setFrequency(0);
    $obj->setFrequency(10);
    $obj->setFrequency(5);

    $ad_layout = new AdvertisingLayout();
    $ad_layout->setMargin(new Margin(10, 10));
    $obj->setLayout($ad_layout);
    $this->assertEquals($expected, $obj->json());
  }
}
