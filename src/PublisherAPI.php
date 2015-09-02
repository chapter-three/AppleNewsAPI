<?php

/**
 * @file
 * AppleNews PublisherAPI library.
 */

namespace ChapterThree\AppleNews;

/**
 * AppleNews PublisherAPI
 *
 * The PublisherAPI is a PHP library that allows you to publish content to Apple News.
 * You can also retrieve, update and delete articles you've already published,
 * and get basic information about your channel and sections.
 *
 * @package    ChapterThree\AppleNews\PublisherAPI
 * @subpackage ChapterThree\AppleNews\PublisherAPI\Base
 */
class PublisherAPI extends \ChapterThree\AppleNews\PublisherAPI\Curl {

  /** @var (const) PublisherAPI version */
  const VERSION = '0.3.3';

}
