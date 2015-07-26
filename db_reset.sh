#!/bin/bash
#resets the database to the same state as the bootstrap.php
#used to allow manually changing the schema using the WHMCS interface, and dumping again
vendor/bin/phpunit -c phpunit.dist.xml --filter "test_refill_balance"