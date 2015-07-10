<?php

/**
 * @file
 * Tests for ChapterThree\AppleNews.
 */

namespace ChapterThree\AppleNews;

/**
 * A test class for PushAPI.
 */
class BaseTest extends PHPUnit_Framework_TestCase {

  const API_KEY_ID = '1e3gfc5e-e9f8-4232-a6be-17bf40edad09';
  const CHANNEL_ID = '63a75491-2c4d-3530-af91-819be8c3ace0';
  const ENDPOINT = 'https://u48r14.digitalhub.com';

  const BASE64_1X1_GIF = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
  const JSON = '{"key": ["value1", "value2"]}';
  const BOUNDARY = '093dcc6f56dadd142436f172b5c2124c';
  const POSTDATE = '2015-07-10T12:34:56+00:00';

  private $http_client;
  private $PushAPI;
  private $fileroot;
  private $files = [];

  protected function setUp() {

  }

  /**
   * Test GET request.
   */
  public function testGet() {

  }

  /**
   * Test DELETE request.
   */
  public function testDelete() {

  }

  /**
   * Test POST request.
   */
  public function testPost() {

  }

}
