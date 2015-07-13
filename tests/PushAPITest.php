<?php

/**
 * @file
 * Tests for ChapterThree\AppleNews.
 */

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\content\LargeFileContent;

/**
 * A test class for PushAPI.
 */
class PushAPITest extends PHPUnit_Framework_TestCase {

  /** @var (const) CRLF */
  const EOL = "\r\n";

  /** @var (const) API Key ID */
  const API_KEY_ID = '1e3gfc5e-e9f8-4232-a6be-17bf40edad09';
  /** @var (const) API Key Secret */
  const API_KEY_SECRET = 'qygOz6+eUsIr1j/YkStHUFP2Wv0SbNZ5RStxQ+lagoA=';
  /** @var (const) API Endpoint full URL */
  const ENDPOINT = 'https://endpoint_url.com';

  const CHANNEL_ID = '63a75491-2c4d-3530-af91-819be8c3ace0';
  const ARTICLE_ID = 'fdc30273-f053-46a5-b6e5-84b3a9036dc6';

  const BASE64_1X1_GIF = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

  /** @var (object) HTTP client. */
  private $http_client;
  /** @var (class) PushAPI class. */
  private $PushAPI;
  /** @var (string) File path generated via vfsStream. */
  private $fileroot;
  /** @var (array) Array of files to upload via multipart data. */
  private $files = [];

  /**
   *  Create objects against which we will test.
   */
  protected function setUp() {

    // Set up virtual file system.
    $this->fileroot = vfsStream::setup();

    // Generate file in vfs.
    $file = vfsStream::newFile('image.gif')
      ->withContent(base64_decode(static::BASE64_1X1_GIF))
      ->at($this->fileroot);

    // Add file path to files.
    $this->files[] = $file->url();

    // Setup cURL client.
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
   * Test __constructor().
   */
  public function testConstruct() {
    $this->assertEquals(static::API_KEY_ID, $this->PushAPI->api_key_id);
    $this->assertEquals(static::API_KEY_SECRET, $this->PushAPI->api_key_secret);
    $this->assertEquals(static::ENDPOINT, $this->PushAPI->endpoint);
  }

  /**
   * Test PushAPI::get().
   */
  public function testGet() {

    // Set up the expectation for the Get() method to be called only once and
    // with certain expected parameters.
    $this->http_client
      ->expects($this->once())
      ->method('get')
      ->with(
        $this->equalTo('/channels/{channel_id}/sections'),
        $this->equalTo([
          'channel_id' => static::CHANNEL_ID
        ])
      );

    $request = $this->http_client->get('/channels/{channel_id}/sections',
      [
        'channel_id' => static::CHANNEL_ID
      ]
    );

  }

  /**
   * Test PushAPI::delete().
   */
  public function testDelete() {

    // Set up the expectation for the Delete() method to be called only once and
    // with certain expected parameters.
    $this->http_client
      ->expects($this->once())
      ->method('delete')
      ->with(
        $this->equalTo('/articles/{article_id}'),
        $this->equalTo([
          'article_id' => static::ARTICLE_ID
        ])
      );

    $request = $this->http_client->delete('/articles/{article_id}',
      [
        'article_id' => static::ARTICLE_ID
      ]
    );

  }

  /**
   * Test PushAPI::post().
   */
  public function testPost() {

    // Set up the expectation for the Post() method to be called only once and
    // with certain expected parameters.
    $this->http_client
      ->expects($this->once())
      ->method('post')
      ->with(
        $this->equalTo('/channels/{channel_id}/articles'),
        $this->equalTo([
          'channel_id' => static::CHANNEL_ID
        ]),
        $this->equalTo([
          'files' => $this->files
        ])
      );

    $request = $this->http_client->post('/channels/{channel_id}/articles',
      [
        'channel_id' => static::CHANNEL_ID
      ],
      [
        'files' => $this->files
      ]
    );

  }

  /**
   * Test PushAPI::getFileInformation().
   */
  public function testGetFileInformation() {

  	$reflection = new \ReflectionClass('\ChapterThree\AppleNews\PushAPI');
    $method = $reflection->getMethod('getFileInformation');
    $method->setAccessible(true);

    // Process each file and generate multipart form data.
    foreach ($this->files as $path) {
      // Load file information.
      $file = $method->invokeArgs($this->PushAPI, [$path]);
      $expected = 
  	    [
  	      'name'      => 'image',
  	      'filename'  => 'image.gif',
  	      'extension' => 'gif',
  	      'mimetype'  => 'image/gif',
  	      'contents'  => base64_decode(static::BASE64_1X1_GIF),
  	      'size'      => strlen(base64_decode(static::BASE64_1X1_GIF))
  	    ];
  	    // Check file information
       $this->assertEquals(0, count(array_diff($file, $expected)));
    }

  }

  /**
   * Test PushAPI::getFileInformation().
   * Test PushAPI::multipartPart().
   * Test PushAPI::multipartFinalize().
   */
  public function testMultipartPart() {

  	$reflection = new \ReflectionClass('\ChapterThree\AppleNews\PushAPI');

  	// Access protected method getFileInformation().
    $getFileInformation = $reflection->getMethod('getFileInformation');
    $getFileInformation->setAccessible(true);

    // Access protected method multipartPart().
    $multipartPart = $reflection->getMethod('multipartPart');
    $multipartPart->setAccessible(true);

    // Access protected method multipartFinalize().
    $multipartFinalize = $reflection->getMethod('multipartFinalize');
    $multipartFinalize->setAccessible(true);

    // Get private property.
    $getBoundary = $reflection->getProperty('boundary');
    $getBoundary->setAccessible(true);
    $boundary = $getBoundary->getValue($this->PushAPI);

    $multiparts = [];

    // Process each file and generate multipart form data.
    foreach ($this->files as $path) {
      // Load file information.
      $file = $getFileInformation->invokeArgs($this->PushAPI, [$path]);
      $multiparts[] = $multipartPart->invokeArgs(
      	$this->PushAPI,
      	[
          [
            'filename'   => $file['filename'],
            'name'       => $file['name'],
            'size'       => $file['size']
          ],
          $file['mimetype'],
          $file['contents']
        ]
      );
    }

    // Generate finalized version of the multipart data.
    $contents = $multipartFinalize->invokeArgs($this->PushAPI, [$multiparts]);
    // Get rid of first boundary.
    $multipart1 = '--' . $boundary . static::EOL .  preg_replace('/^.+\n/', '', $contents);

    // Load test file.
    $file = $getFileInformation->invokeArgs($this->PushAPI, [$this->files[0]]);

    $multipart2 = '--' . $boundary . static::EOL;
    $multipart2 .= 'Content-Type: image/gif'. static::EOL;
    $multipart2 .= 'Content-Disposition: form-data; filename=image.gif; name=image; size=42' . static::EOL;
    $multipart2 .= static::EOL . $file['contents'] . static::EOL;
    $multipart2 .= '--' . $boundary . '--' . static::EOL;

    // Test Multipart data headers and content.
    $this->assertEquals($multipart1, $multipart2);

  }

}
