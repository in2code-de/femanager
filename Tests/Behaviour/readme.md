# How to start behavior tests for femanager?

## Preperations

* First of all, do a `composer install` in extension root folder
* You have to install a local TYPO3-instance (8.7) next and it should be available under `femanager.localhost.de`
* A dump is available under http://powermail.in2code.ws/fileadmin/behat/femanager.sql.gz

## Command line

### Start Selenium

* Open a console and go to `EXT:femanager/Tests/Behavior/`
* Start a selenium server with `sh selenium.sh`
* As an alternative, you could specify which browser version should be used (if you installed a second firefox - probably older then quantum) - in my case: 
`java -jar ../../.Build/vendor/se/selenium-server-standalone/bin/selenium-server-standalone.jar -Dwebdriver.firefox.bin="/var/www/Webtools/firefox/42/firefox"`

### Start Behat

* Open another console and go to `EXT:femanager/Tests/Behavior/`
* Start behat with `sh behat.sh` or with `sh behats.sh` (for stopping on first failure)
* As an alternative, you could specify a single test by its tag like `../../.Build/vendor/behat/behat/bin/behat --tags=Mod1Basic`
