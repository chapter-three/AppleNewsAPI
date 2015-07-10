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

$PushAPI = new ChapterThree\AppleNews\PushAPI(
  $api_key_id,
  $api_key_secret,
  $endpoint
);
```

### GET Methods

1. GET Channel

```php
$response = $PushAPI->Get('/channels/{channel_id}', ['channel_id' => CHANNEL_ID]);
```

2. GET Sections

```php
$response = $PushAPI->Get('/channels/{channel_id}/sections', ['channel_id' => CHANNEL_ID]);
```

3. GET Section

```php
$response = $PushAPI->Get('/sections/{section_id}', ['section_id' => SECTION_ID]);
```

4. GET Article

```php
$response = $PushAPI->Get('/articles/{article_id}', ['article_id' => ARTICLE_ID]);
```

### POST Methods

1. POST Article

```php
$response = $PushAPI->Post('/channels/{channel_id}/articles', ['channel_id' => CHANNEL_ID],
      [
        // List of files to POST
        'files' => [], // required
        // JSON metadata string
        'metadata' => '', // optional
        // Submit contents of article.json file if the file isn't provied in the `files` array
        'json' => '', // optional
      ]
    );
```

### DELETE Methods

1. DELETE Article

```php
$response = $PushAP->Delete('/articles/{article_id}', ['article_id' => ARTICLE_ID]);
```
