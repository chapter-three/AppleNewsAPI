<?php

/**
 * @file
 * Tests for ChapterThree\AppleNews\Push.
 */

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\content\LargeFileContent;
use Psr\Http\Message\RequestInterface;


/**
 * Document me.
 */
class PushTest extends PHPUnit_Framework_TestCase {

  const API_KEY_ID = '1e3gfc5e-e9f8-4232-a6be-17bf40edad09';
  const CHANNEL_ID = '63a75491-2c4d-3530-af91-819be8c3ace0';
  const ENDPOINT = 'https://u48r14.digitalhub.com';

  const BASE64_1X1_GIF = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
  const JSON = '{"key": ["value1", "value2"]}';
  const BOUNDARY = '093dcc6f56dadd142436f172b5c2124c';
  const POSTDATE = '2015-07-02T13:09:16+00:00';

  private $client;
  private $push;
  private $fileroot;
  private $files = array();

  protected function setUp() {
    // Set up virtual file system.
    $this->fileroot = vfsStream::setup();

    // Generate file in vfs.
    $file = vfsStream::newFile('image.gif')
      ->withContent(base64_decode(static::BASE64_1X1_GIF))
      ->at($this->fileroot);
    // Add file path to files.
    $this->files[] = $file->url();

    // Set up Push object.
    $this->client = $this->getMockBuilder('\GuzzleHttp\Client')
      ->setMethods(array('post'))
      ->getMock();
    $this->push = new \ChapterThree\AppleNews\Push(static::API_KEY_ID, static::CHANNEL_ID, static::ENDPOINT, $this->client);
  }

  /**
   * Constructor test.
   */
  public function testConstruct() {
    $this->assertEquals(static::API_KEY_ID, $this->push->api_key_id);
    $this->assertEquals(static::CHANNEL_ID, $this->push->channel_id);
    $this->assertEquals(static::ENDPOINT,   $this->push->endpoint);
  }

  protected function getExpectedBody($boundary) {
    $json = static::JSON;
    $gif  = base64_decode(static::BASE64_1X1_GIF);

    // Compile expected body.
    $eol = \ChapterThree\AppleNews\Push::EOL;
    $expected_body  = "--{$boundary}" . $eol;
    $expected_body .= $eol;
    $expected_body .= "Content-Type: application/json" . $eol;
    $expected_body .= "Content-Disposition: form-data; filename=article.json; name=article; size=29" . $eol;
    $expected_body .= "{$json}" . $eol;
    $expected_body .= "--{$boundary}" . $eol;
    $expected_body .= $eol;
    $expected_body .= "Content-Type: image/gif" . $eol;
    $expected_body .= "Content-Disposition: form-data; filename=image.gif; name=image; size=42" . $eol;
    $expected_body .= "{$gif}" . $eol;
    $expected_body .= "--{$boundary}" . $eol;
    $expected_body .= $eol;

    return $expected_body;
  }

  protected function getExpectedHeaders($boundary, $date) {
    return array(
      'Content-Type' => 'Content-Type: multipart/form-data; boundary=' . static::BOUNDARY,
      'Authorization' => 'HHMAC; key=1e3gfc5e-e9f8-4232-a6be-17bf40edad09; signature=ZDYyY2VmZmM3MTRlYjliMWYyODllMGIwYzIzN2Y1NGE2OTRlZWNmYzE5OTc3NjM4ZWE2NjAwNTczYWI0MWE0YQ==; date=' . $date,
    );
  }

  /**
   * Post test.
   *
   * @depends testConstruct
   */
  public function testPost() {
    $boundary     = static::BOUNDARY;
    $date         = static::POSTDATE;
    $body         = $this->getExpectedBody($boundary);
    $headers      = $this->getExpectedHeaders($boundary, $date);

    // Set up the expectation for the post() method to be called only once and
    // with certain expected parameters.
    $this->client
      ->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo(static::ENDPOINT . '/channels/' . static::CHANNEL_ID . '/articles'),
        $this->equalTo(array(
          'synchronous' => TRUE,
          'headers' => $headers,
          'body' => $body,
        ))
      );

    $request = $this->push->post(static::JSON, $this->files, $date, $boundary);
  }

}
