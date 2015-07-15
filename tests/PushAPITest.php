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
class PushAPITest extends \PHPUnit_Framework_TestCase {

  /** @var (const) CRLF */
  const EOL = "\r\n";

  /** @var (string) API Key ID */
  private $api_key = '';

  /** @var (string) API Key Secret */
  private $api_key_secret = '';

  /** @var (string) API Endpoint full URL */
  private $endpoint = '';

  /** @var (string) Endpoint method to test */
  private $endpoint_method = '';

  /** @var (string) Endpoint path to test */
  private $endpoint_path = '';

  /** @var (object) PushAPI class object */
  private $PushAPI;

  /** @var (const) Contents of the test GIF file */
  const BASE64_1X1_GIF = 'R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';

  /** @var (string) File path generated via vfsStream. */
  private $fileroot;

  /** @var (array) Array of files to upload via multipart data. */
  private $files = [];

  /**
   *  Create objects against which we will test.
   */
  protected function setUp() {
    global $argv, $argc;

    $this->api_key = isset($argv[6]) ? $argv[6] : '';
    $this->api_key_secret = isset($argv[7]) ? $argv[7] : '';
    $this->endpoint = isset($argv[8]) ? $argv[8] : '';
    $this->endpoint_method = isset($argv[9]) ? strtolower($argv[9]) : '';
    $this->endpoint_path = isset($argv[10]) ? $argv[10] : '';

    if (empty($this->api_key) && empty($this->api_key_secret) && empty($this->endpoint)) {
      die('Please speciy PushAPI credentials. See documentation for more details about PushAPI unit tests.');
    }

    // Set up PushAPI object.
    $this->PushAPI = new \ChapterThree\AppleNews\PushAPI(
      $this->api_key,
      $this->api_key_secret,
      $this->endpoint
    );

    // Set up virtual file system.
    $this->fileroot = vfsStream::setup();

    // Generate file in vfs.
    $file = vfsStream::newFile('image.gif')
      ->withContent(base64_decode(static::BASE64_1X1_GIF))
      ->at($this->fileroot);

    // Add file path to files.
    $this->files[] = $file->url();

  }

  /**
   * Test PushAPI::get().
   *
   * Usage:
   *   ./vendor/bin/phpunit -v --colors=auto --bootstrap vendor/autoload.php tests/PushAPITest.php [API_KEY_ID] [API_SECRET_KEY] [ENDPOINT_URL] get /channels/{channel_id}
   */
  public function testGet() {

    if ($this->endpoint_method == 'get') {

      $response = $this->PushAPI->get($this->endpoint_path);
      if (isset($response->errors)) {
        $this->assertTrue(false);
      }
      else {
        $this->assertTrue(true);
      }

    }

  }

  /**
   * Test PushAPI::delete().
   *
   * Usage:
   *   ./vendor/bin/phpunit -v --colors=auto --bootstrap vendor/autoload.php tests/PushAPITest.php [API_KEY_ID] [API_SECRET_KEY] [ENDPOINT_URL] delete /articles/{article_id}
   */
  public function testDelete() {

    if ($this->endpoint_method == 'delete') {

      $response = $this->PushAPI->delete($this->endpoint_path);
      if (isset($response->errors)) {
        $this->assertTrue(false);
      }
      else {
        $this->assertTrue(true);
      }

    }

  }


  /**
   * Test PushAPI::post().
   *
   * Usage:
   *   ./vendor/bin/phpunit -v --colors=auto --bootstrap vendor/autoload.php tests/PushAPITest.php [API_KEY_ID] [API_SECRET_KEY] [ENDPOINT_URL] post /channels/{channel_id}/articles
   */
  public function testPost() {

    if ($this->endpoint_method == 'post') {

      $reflection = new \ReflectionClass('\ChapterThree\AppleNews\PushAPI\Curl');

      // Access protected method getFileInformation().
      $getFileInformation = $reflection->getMethod('getFileInformation');
      $getFileInformation->setAccessible(true);

      // Add test article.json file.
      $this->files[] = __DIR__ . '/PushAPI/article.json';

      $response = $this->PushAPI->post($this->endpoint_path, [],
        [
          'files' => $this->files,
          'json'  => '',
        ]
      );

      if (isset($response->errors)) {
        $this->assertTrue(false);
      }
      else {
        $this->assertTrue(true);
      }

    }

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

    $reflection = new \ReflectionClass('\ChapterThree\AppleNews\PushAPI\Curl');

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

    // Multiparts
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

    // Expected multipart content.
    $multipart2 = '--' . $boundary . static::EOL;
    $multipart2 .= 'Content-Type: image/gif'. static::EOL;
    $multipart2 .= 'Content-Disposition: form-data; filename=image.gif; name=image; size=42' . static::EOL;
    $multipart2 .= static::EOL . $file['contents'] . static::EOL;
    $multipart2 .= '--' . $boundary . '--' . static::EOL;

    // Test Multipart data headers and content.
    $this->assertEquals($multipart1, $multipart2);

  }

}
