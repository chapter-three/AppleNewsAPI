# AppleNews

## Install

```shell
git clone git@github.com:chapter-three/ApppleNews.git
cd AppleNews
curl -sS https://getcomposer.org/installer | php
./composer.phar install
```

## Unit Tests

```shell
./vendor/bin/phpunit -v --colors=auto --bootstrap vendor/autoload.php tests
```

## Usage examples

```php
$api_key_id = "";
$api_key_secret = "";
$endpoint = "https://endpoint_url"; // No trailing slash.
```

### GET Methods

```php
$get = new ChapterThree\AppleNews\PushAPI\Get($api_key_id, $api_key_secret, $endpoint);
```

1. GET Channel

```php
$response = $get->Get('/channels/{channel_id}', ['channel_id' => CHANNEL_ID]);
```

2. GET Sections

```php
$response = $get->Get('/channels/{channel_id}/sections', ['channel_id' => CHANNEL_ID]);
```

3. GET Section

```php
$response = $get->Get('/sections/{section_id}', ['section_id' => SECTION_ID]);
```

4. GET Article

```php
$response = $get->Get('/articles/{article_id}', ['article_id' => ARTICLE_ID]);
```

### POST Methods

```php
$post = new ChapterThree\AppleNews\PushAPI\Post($api_key_id, $api_key_secret, $endpoint);
```

1. POST Article

```php
$response = $post->Post('/channels/{channel_id}/articles', ['channel_id' => CHANNEL_ID],
      [
        'files' => [], // List of files to POST
        'metadata' => '', // JSON metadata string
        'json' => '', // Submit contents of article.json file if the file isn't provied in the `files` array
      ]
    );
```

### DELETE Methods

```php
$delete = new ChapterThree\AppleNews\PushAPI\Delete($api_key_id, $api_key_secret, $endpoint);
```

1. DELETE Article

```php
$response = $delete->Delete('/articles/{article_id}', ['article_id' => ARTICLE_ID]);
```
