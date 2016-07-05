#!/bin/bash

mkdir -p build/logs
php vendor/bin/phpunit --coverage-clover build/logs/clover.xml
