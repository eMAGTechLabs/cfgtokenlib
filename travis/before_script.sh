#!/bin/bash

if [[ "$TRAVIS_PHP_VERSION" == "5.3.3" ]]; then
  composer config --global disable-tls true
else 
  composer config --global disable-tls false
fi

composer --prefer-source install
