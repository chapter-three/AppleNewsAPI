<?php

/**
 * @file
 * Tests for ChapterThree\AppleNews\Document\Markdown.
 */

use ChapterThree\AppleNews\Document\Markdown;

/**
 * Tests for the Markdown class.
 */
class MarkdownTest extends PHPUnit_Framework_TestCase {

  /**
   * Paragraphs.
   */
  public function testParagraphs() {
    $html = <<<'EOD'
<p>some paragraph content</p>
<p>some paragraph content with <i>italic</i> and <em>emphasized</em> text.</p>
<p>some paragraph content with <b>bold</b> and <strong>strong</strong> text.</p>
EOD;
    $expected = <<<'EOD'
some paragraph content

some paragraph content with *italic* and *emphasized* text.

some paragraph content with **bold** and **strong** text.
EOD;
    $markdown = Markdown::convert($html);
    $this->assertEquals(trim($expected), trim($markdown),
      'Convert HTML with paragraphs and inline elements to Markdown.');
  }

  /**
   * Headers.
   */
  public function testHeaders() {
    $html = <<<'EOD'
<h1>header 1</h1>
<p>some paragraph content</p>
<h2>header 2</h2>
<h3>header 3</h3>
<p>some paragraph content</p>
<h4>header 4</h4>
<h5>header 5</h5>
<h6>header 6</h6>
<p>some paragraph content</p>
EOD;
    $expected = <<<'EOD'
# header 1

some paragraph content

## header 2

### header 3

some paragraph content

#### header 4

##### header 5

###### header 6

some paragraph content
EOD;
    $markdown = Markdown::convert($html);
    $this->assertEquals(trim($expected), trim($markdown),
      'Convert HTML with headers to Markdown.');
  }

  /**
   * Headers.
   */
  public function testLinks() {
    $html = <<<'EOD'
<p>some paragraph content with <a href="http://apple.com">links</a>.</p>
<h2>header 2 with <a href="http://apple.com">link</a></h2>
<p>some paragraph content with <em><a href="http://apple.com">emphasized links</a></em> or <a href="http://apple.com"><em>emphasized</em> links</a> text.</p>
<p>some paragraph content with <strong><a href="http://apple.com">strong links</a></strong> or <a href="http://apple.com"><strong>strong</strong> links</a> text.</p>
EOD;
    $expected = <<<'EOD'
some paragraph content with [links](http://apple.com).

## header 2 with [link](http://apple.com)

some paragraph content with *[emphasized links](http://apple.com)* or [*emphasized* links](http://apple.com) text.

some paragraph content with **[strong links](http://apple.com)** or [**strong** links](http://apple.com) text.
EOD;
    $markdown = Markdown::convert($html);
    $this->assertEquals(trim($expected), trim($markdown),
      'Convert HTML with link elements to Markdown.');
  }

  /**
   * Horizontal rules.
   */
  public function testHorizontalRules() {
    $html = <<<'EOD'
<p>some paragraph content.</p><hr/>
<p>some paragraph content.</p>
<hr/>
<p>some paragraph content.</p>
<hr/><p>some paragraph content.</p>
<p>some paragraph content.</p>
<p>some paragraph <hr/>content.</p>
<p>some paragraph content.</p>
EOD;
    $expected = <<<'EOD'
some paragraph content.

---

some paragraph content.

---

some paragraph content.

---

some paragraph content.

some paragraph content.

some paragraph

---

content.

some paragraph content.
EOD;
    $markdown = Markdown::convert($html);
    $this->assertEquals(trim($expected), trim($markdown),
      'Convert HTML with hr elements to Markdown.');
  }

  /**
   * Horizontal rules.
   */
  public function testLists() {
    $html = <<<'EOD'
<p>a ul</p>
<ul>
<li>item 1</li>
<li>item 2</li>
<li>item 3</li>
</ul>
<p>an ol</p>
<ol>
<li>item 1</li>
<li>item 2</li>
<li>item 3</li>
</ol>
EOD;
    $expected = <<<'EOD'
a ul

 - item 1
 - item 2
 - item 3

an ol

 1. item 1
 1. item 2
 1. item 3
EOD;
    $markdown = Markdown::convert($html);
    $this->assertEquals(trim($expected), trim($markdown),
      'Convert HTML with list elements to Markdown.');
  }

  /**
   * Ignored tags.
   */
  public function testIgnored() {
    $html = <<<'EOD'
<p>some paragraph content</p>
<p>some paragraph content with <xxx>ignored</xxx> inline tags.</p>
<p>some paragraph content with <xxx>ignored inline tags.</xxx></p>
<p><xxx>some paragraph content with ignored</xxx> inline tags.</p>
<p><xxx>some paragraph content with ignored inline tags.</xxx></p>
<xxx>some content inside an ignored block tag.</xxx>
<p>some paragraph content</p>
EOD;
    $expected = <<<'EOD'
some paragraph content

some paragraph content with ignored inline tags.

some paragraph content with ignored inline tags.

some paragraph content with ignored inline tags.

some paragraph content with ignored inline tags.

some content inside an ignored block tag.

some paragraph content
EOD;
    $markdown = Markdown::convert($html);
    $this->assertEquals(trim($expected), trim($markdown),
      'Convert HTML with ignored elements to Markdown.');
  }

}
