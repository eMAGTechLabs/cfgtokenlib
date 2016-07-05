#!/bin/bash

if [[ "$TRAVIS_PHP_VERSION" != "7.0" && "$TRAVIS_PHP_VERSION" != "hhvm" ]]; then 
  wget https://scrutinizer-ci.com/ocular.phar -O ocular.phar && \
  php ocular.phar code-coverage:upload --format=php-clover build/logs/clover.xml
fi
