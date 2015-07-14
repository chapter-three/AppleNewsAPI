<?php

/**
 * @file
 * Handling of Apple News Document markdown.
 */

namespace ChapterThree\AppleNews\Document;

/**
 * Handling of Apple News Document markdown.
 */
class Markdown {

  /**
   * Convert HTML to Apple News Markdown.
   *
   * @param string $html
   *   HTML to convert. Value is not validated, it is caller's responsibility
   *   to validate.
   *
   * @return string
   *   Markdown representation of the HTML.
   */
  public static function convert($html) {

    // Ensure a root element.
    $html = '<html>' . trim($html) . '</html>';

    $reader = new \XMLReader();
    if (!$reader->XML($html)) {
      return FALSE;
    }

    // All necessary state.
    $context = array(
      'a' => '',
      'l' => array(),
    );

    // The current line is always initialized with empty string.
    $line = 0;
    $markdown = array('');

    // Initialize a new line if current line is not empty.
    $next_line = function() use(&$line, &$markdown) {
      if ($markdown[$line] !== '') {
        $markdown[++$line] = '';
      }
    };

    // Insert a blank line and initialize next.
    $next_block = function() use(&$line, &$markdown, &$next_line) {
      $next_line();
      if (isset($markdown[$line - 1]) && $markdown[$line - 1] !== '') {
        $markdown[++$line] = '';
      }
    };

    // Main event loop.
    while ($reader->read()) {

      switch ($reader->nodeType) {

        case \XMLREADER::ELEMENT:
          switch ($reader->localName) {

            case 'p':
              $next_block();
              break;

            case 'h1':
              $next_block();
              $markdown[$line] .= '# ';
              break;

            case 'h2':
              $next_block();
              $markdown[$line] .= '## ';
              break;

            case 'h3':
              $next_block();
              $markdown[$line] .= '### ';
              break;

            case 'h4':
              $next_block();
              $markdown[$line] .= '#### ';
              break;

            case 'h5':
              $next_block();
              $markdown[$line] .= '##### ';
              break;

            case 'h6':
              $next_block();
              $markdown[$line] .= '###### ';
              break;

            case 'i':
            case 'em':
              $markdown[$line] .= '*';
              break;

            case 'b':
            case 'strong':
              $markdown[$line] .= '**';
              break;

            case 'a':
              $reader->moveToAttribute('href');
              $context['a'] = $reader->value;
              $markdown[$line] .= '[';
              break;

            case 'hr':
              $next_block();
              $markdown[$line] = '---';
              $next_block();
              break;

            case 'ol':
            case 'ul':
              $next_block();
              $context['l'][] = $reader->localName;
              break;

            case 'li':
              $next_line();
              $type = $context['l'][count($context['l']) - 1];
              $ol = $type == 'ol';
              $markdown[$line] = $ol ? ' 1. ' : ' - ';
              break;

          }
          break;

        case \XMLReader::END_ELEMENT:
          switch ($reader->localName) {

            case 'p':
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
              $next_block();
              break;

            case 'i':
            case 'em':
              $markdown[$line] .= '*';
              break;

            case 'b':
            case 'strong':
              $markdown[$line] .= '**';
              break;

            case 'a':
              $markdown[$line] .= '](' . $context['a'] . ')';
              $context['a'] = '';
              break;

            case 'ol':
            case 'ul':
              array_pop($context['l']);
              $next_block();
              break;

          }
          break;

        case \XMLReader::TEXT:
          $markdown[$line] .= preg_replace('/\s\s+/', ' ', $reader->value);
          break;

      }

    }

    $markdown = array_map('rtrim', $markdown);
    return implode("\n", array_values($markdown));
  }

}
