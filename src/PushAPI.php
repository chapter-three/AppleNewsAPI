<?php

/**
 * @file
 * AppleNews integration class.
 */

namespace ChapterThree\AppleNews;

/**
 * AppleNews PushAPI
 *
 * The PushAPI is a PHP library that allows you to publish articles.
 * You can also retrieve and delete articles you've already published, 
 * and get basic information about your channel and sections.
 * 
 * @package    ChapterThree\AppleNews\PushAPI
 * @subpackage ChapterThree\AppleNews\PushAPI\Base
 */
class PushAPI extends \ChapterThree\AppleNews\PushAPI\Curl {

  /** @var (const) PushAPI version */
  const VERSION = '1.0';

}
