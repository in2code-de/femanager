# How to start unit tests for femanager?

## On command line

* First of all, go to extension folder in console
* Then do a `composer update`
* After that you can call `/usr/bin/php .Build/vendor/phpunit/phpunit/phpunit --configuration phpunit.xml.dist`

## In PhpStorm

* First of all go to extension folder in console
* Then do a `composer update`
* After this, you should open the PhpStorm Settings and go to `Languages & Frameworks > PHP > Test frameworks`
* Choose `use composer autoloader`
* Add folder on `path to script` to `EXT:femanager/.Build/vendor/autoload.php`
* Finish: Right-Click on file `phpunit.xml.dist` with `Run 'phpunit.xml.dist'`

## With code coverage

* You need to have xdebug installed and configured on your test environment
* On command line you can run it like `/usr/bin/php -dxdebug.coverage_enable=1 .Build/vendor/phpunit/phpunit/phpunit --configuration phpunit.xml.dist --coverage-text`
* In PhpStorm you could simply right-click on file `phpunit.xml.dist` with `Run 'phpunit.xml.dist with Coverage'`


Example:

<img src="https://s.nimbus.everhelper.me/attachment/1403605/9ugfz1b792wcm798i08p/262407-2PlZAEN2PlWD7B9A/screen.png" />
