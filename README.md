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
$endpoint = "https://endpoint_url";

$PushAPI = new ChapterThree\AppleNews\PushAPI(
  $api_key_id,
  $api_key_secret,
  $endpoint
);
```

### GET Methods

#### GET Channel

```php
$response = $PushAPI->Get('/channels/{channel_id}',
  [
    'channel_id' => CHANNEL_ID
  ]
);
```

#### GET Sections

```php
$response = $PushAPI->Get('/channels/{channel_id}/sections',
  [
    'channel_id' => CHANNEL_ID
  ]
);
```

#### GET Section

```php
$response = $PushAPI->Get('/sections/{section_id}',
  [
    'section_id' => SECTION_ID
  ]
);
```

#### GET Article

```php
$response = $PushAPI->Get('/articles/{article_id}',
  [
    'article_id' => ARTICLE_ID
  ]
);
```

### POST Methods

#### POST Article

```php
$response = $PushAPI->Post('/channels/{channel_id}/articles',
  [
    'channel_id' => CHANNEL_ID
  ],
  [
    // List of files to POST
    'files' => [], // required
    // JSON metadata string
    'metadata' => '', // optional
    // Submit contents of the article.json file if
    // the file isn't provied in the `files` array
    'json' => '', // optional
  ]
);
```

### DELETE Methods

#### DELETE Article

```php
$response = $PushAPI->Delete('/articles/{article_id}',
  [
    'article_id' => ARTICLE_ID
  ]
);
```
