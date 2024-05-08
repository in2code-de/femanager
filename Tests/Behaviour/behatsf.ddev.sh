#!/bin/sh

echo 'start behat for femanager and stop on the first failure - please provide a tag which should be tested'
../../.Build/vendor/behat/behat/bin/behat --stop-on-failure  --config /app/Tests/Behaviour/behat.ddev.yml --tags $1
