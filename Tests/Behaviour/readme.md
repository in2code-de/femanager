# How to start behavior tests for femanager?

## DDEV

1. Start the ddev project with `DDEV start`
1. Import the DB / fileadmin with `DDEV initialize`
1. Start the tests with `DDEV composer run test:behaviour:ddev`

Wanna watch chrome? Open a VNC to `127.0.0.1:15722`. The PW is `secret`
