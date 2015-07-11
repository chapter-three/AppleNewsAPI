<?php

/**
 * @file
 * Tests for ChapterThree\AppleNews.
 */

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\content\LargeFileContent;
use Psr\Http\Message\RequestInterface;

/**
 * A test class for PushAPI.
 */
class PushAPITest extends PHPUnit_Framework_TestCase {

  const API_KEY_ID = '1e3gfc5e-e9f8-4232-a6be-17bf40edad09';
  const API_KEY_SECRET = 'qygOz6+eUsIr1j/YkStHUFP2Wv0SbNZ5RStxQ+lagoA=';
  const CHANNEL_ID = '63a75491-2c4d-3530-af91-819be8c3ace0';
  const ARTICLE_ID = 'fdc30273-f053-46a5-b6e5-84b3a9036dc6';
  const ENDPOINT = 'https://u48r14.digitalhub.com';

  const BASE64_1X1_GIF = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
  const JSON = '{"key": ["value1", "value2"]}';
  const BOUNDARY = '093dcc6f59daad142436f172b5c2124c';
  const POSTDATE = '2015-07-10T12:34:56+00:00';

  private $http_client;
  private $PushAPI;
  private $fileroot;
  private $files = [];

  protected function setUp() {

    // Set up virtual file system.
    $this->fileroot = vfsStream::setup();

    // Generate file in vfs.
    $file = vfsStream::newFile('image.gif')
      ->withContent(base64_decode(static::BASE64_1X1_GIF))
      ->at($this->fileroot);

    // Add file path to files.
    $this->files[] = $file->url();

    // Set up cURL client.
    $this->http_client = $this->getMockBuilder('\Curl\Curl')
      ->setMethods([
      	  'post',
      	  'get',
      	  'delete',
      	  'setHeader',
      	  'unsetHeader',
      	  'setOpt',
      	  'close'
      	]
      )
      ->getMock();

    // Set up PushAPI object.
    $this->PushAPI = new \ChapterThree\AppleNews\PushAPI(
      static::API_KEY_ID,
      static::API_KEY_SECRET,
      static::ENDPOINT
    );

  }

  /**
   * Constructor test.
   */
  public function testConstruct() {
    $this->assertEquals(static::API_KEY_ID, $this->PushAPI->api_key_id);
    $this->assertEquals(static::API_KEY_SECRET, $this->PushAPI->api_key_secret);
    $this->assertEquals(static::ENDPOINT,   $this->PushAPI->endpoint);
  }

  /**
   * Test GET request.
   */
  public function testGet() {

    // Set up the expectation for the Get() method to be called only once and
    // with certain expected parameters.
    $this->http_client
      ->expects($this->once())
      ->method('Get')
      ->with(
        $this->equalTo('/channels/{channel_id}/sections'),
        $this->equalTo([
          'channel_id' => static::CHANNEL_ID
        ])
      );

    $request = $this->http_client->Get('/channels/{channel_id}/sections',
      [
        'channel_id' => static::CHANNEL_ID
      ]
    );

  }

  /**
   * Test DELETE request.
   */
  public function testDelete() {

    // Set up the expectation for the Delete() method to be called only once and
    // with certain expected parameters.
    $this->http_client
      ->expects($this->once())
      ->method('Delete')
      ->with(
        $this->equalTo('/articles/{article_id}'),
        $this->equalTo([
          'article_id' => static::ARTICLE_ID
        ])
      );

    $request = $this->http_client->Delete('/articles/{article_id}',
      [
        'article_id' => static::ARTICLE_ID
      ]
    );

  }

  /**
   * Test POST request.
   */
  public function testPost() {

    // Set up the expectation for the Post() method to be called only once and
    // with certain expected parameters.
    $this->http_client
      ->expects($this->once())
      ->method('Post')
      ->with(
        $this->equalTo('/channels/{channel_id}/articles'),
        $this->equalTo([
          'channel_id' => static::CHANNEL_ID
        ]),
        $this->equalTo([
          'files' => $this->files
        ])
      );

    $request = $this->http_client->Post('/channels/{channel_id}/articles',
      [
        'channel_id' => static::CHANNEL_ID
      ],
      [
        'files' => $this->files
      ]
    );

  }

}
