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

  // data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7
  const BASE64_1X1_GIF = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
  const JSON = '{"key": ["value1", "value2"]}';

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
    $client = $this->getMockBuilder('\GuzzleHttp\Client')
      // ->setMethods(array('update'))
      ->getMock();
    $this->push = new \ChapterThree\AppleNews\Push(static::API_KEY_ID, static::CHANNEL_ID, static::ENDPOINT, $client);
  }

  /**
   * Constructor test.
   */
  public function testConstruct() {
    $this->assertEquals(static::API_KEY_ID, $this->push->api_key_id);
    $this->assertEquals(static::CHANNEL_ID, $this->push->channel_id);
    $this->assertEquals(static::ENDPOINT,   $this->push->endpoint);
  }

  /**
   * FileLoadFormdata test.
   *
   * @depends testConstruct
   */
  public function testFileLoadFormdata() {
    $formdata = $this->push->fileLoadFormdata($this->files[0]);

    $this->assertEquals(array (
      'name' => 'image',
      'filename' => 'image.gif',
      'mimetype' => 'image/gif',
      'contents' => base64_decode(static::BASE64_1X1_GIF),
      'size' => 42,
    ), $formdata);
  }

  /**
   * EncodeMultipartFormdata test.
   *
   * @depends testConstruct
   * @depends testFileLoadFormdata
   */
  public function testEncodeMultipartFormdata() {
    $boundary = md5(time());
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

    // Compile fields to encode.
    $multipart = array();
    $multipart['article'] = array(
      'name' => 'article',
      'filename' => 'article.json',
      'mimetype' => 'application/json',
      'contents' => $json,
      'size' => strlen($json),
    );
    $formdata = $this->push->fileLoadFormdata($this->files[0]);
    $multipart[$formdata['name']] = $formdata;

    // Encode fields.
    list($body, $content_type) = $this->push->encodeMultipartFormdata($multipart);

    // Test encoded data.
    $this->assertEquals('Content-Type: multipart/form-data; boundary=' . $boundary, $content_type);
    $this->assertEquals($expected_body, $body);
  }

  /**
   * Post test.
   *
   * @depends testConstruct
   * @depends testFileLoadFormdata
   * @depends EncodeMultipartFormdata
   */
  public function testPost() {

  }

}
