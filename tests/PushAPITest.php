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

  /** @var (const) CRLF */
  const EOL = "\r\n";

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

  /** @var (array) Valid values for resource part Content-Type. */
  protected $valid_mimes = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/font-sfnt',
    'application/x-font-truetype',
    'application/font-truetype',
    'application/vnd.ms-opentype',
    'application/x-font-opentype',
    'application/font-opentype',
    'application/octet-stream'
  ];

  /** @var (string) Multipat data boundary unique string. */
  private $boundary;

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

    $request = $this->http_client->get('/channels/{channel_id}/sections',
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

    $request = $this->http_client->delete('/articles/{article_id}',
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

    $request = $this->http_client->post('/channels/{channel_id}/articles',
      [
        'channel_id' => static::CHANNEL_ID
      ],
      [
        'files' => $this->files
      ]
    );

  }

  private function getFileInformation($path) {
    $file = pathinfo($path);

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimetype = finfo_file($finfo, $path);

    if (!in_array($mimetype, $this->valid_mimes)) {
      if ($mimetype == 'text/plain') {
        $mimetype = 'application/octet-stream';
      }
      else {
        throw new \Exception('Unsupported mime type: ' . $mimetype);
      }
    }

    $contents = file_get_contents($path);

    return [
      'name'      => str_replace(' ', '-', $file['filename']),
      'filename'  => $file['basename'],
      'mimetype'  => ($file['extension'] == 'json') ? 'application/json' : $mimetype,
      'contents'  => $contents,
      'size'      => strlen($contents)
    ];
  }

  private function multipartPart(Array $attributes, $mimetype = null, $contents = null) {
    $multipart = '';
    $headers = [];
    foreach ($attributes as $name => $value) {
      $headers[] = $name . '=' . $value;
    }
    // Generate multipart data and contents.
    $multipart .= '--' . static::BOUNDARY . static::EOL;
    $multipart .= 'Content-Type: ' . $mimetype . static::EOL;
    $multipart .= 'Content-Disposition: form-data; ' . join('; ', $headers) . static::EOL;
    $multipart .= static::EOL . $contents . static::EOL;
    return $multipart;
  }

  private function multipartFinalize(Array $multiparts = []) {
    $contents = '';
    foreach ($multiparts as $multipart) {
      $contents .= $multipart;
    }
    $contents .= '--' . static::BOUNDARY  . '--';
    $contents .= static::EOL;
    return $contents;
  }

  public function testGetFileInformation() {

    // Process each file and generate multipart form data.
    foreach ($this->files as $path) {
      // Load file information.
      $file = $this->getFileInformation($path);
      $expected = 
  	    [
  	      'name'      => 'image',
  	      'filename'  => 'image.gif',
  	      'mimetype'  => 'image/gif',
  	      'contents'  => base64_decode(static::BASE64_1X1_GIF),
  	      'size'      => strlen(base64_decode(static::BASE64_1X1_GIF))
  	    ];
       $this->assertEquals(0, count(array_diff($file, $expected)));
    }

  }

  public function testMultipartPart() {

    // Process each file and generate multipart form data.
    foreach ($this->files as $path) {
      // Load file information.
      $file = $this->getFileInformation($path);
      $multiparts[] = $this->multipartPart(
        [
          'filename'   => $file['filename'],
          'name'       => $file['name'],
          'size'       => $file['size']
        ],
        $file['mimetype'],
        $file['contents']
      );
    }

  }

}
