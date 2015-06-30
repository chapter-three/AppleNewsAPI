# AppleNews

## Install

```shell
git clone git@github.com:chapter-three/ApppleNews.git
cd AppleNews
curl -sS https://getcomposer.org/installer | php
./composer.phar install

# run tests
./vendor/bin/phpunit -v --colors=auto --bootstrap vendor/autoload.php tests
```

## Unit Tests

```shell
./vendor/bin/phpunit -v --colors=auto --bootstrap vendor/autoload.php tests
```
