# eNom PRO for WHMCS <a target='_blank' href='http://ci.mycircletree.com/job/enom_PRO'><img src='http://ci.mycircletree.com/buildStatus/icon?job=enom_PRO' title='Build Status'></a>

## Dev Setup

1. [Install WHMCS](http://docs.whmcs.com/Installing_WHMCS) 
	* Make sure to note the MySQL credentials for configuring PHPUnit.xml below
1. Install Composer Globally

  `curl -sS https://getcomposer.org/installer | php`
  
  `mv composer.phar /usr/local/bin/composer`
1. Run `composer install` from the project root
1. Run phing to build the working copy `vendor/bin/phing`
	* add a file watcher to automatically build after changes to the src/ directory tree
1. Symlink build/encoded_prep/modules/addons/enom_pro to the whmcs/modules/addons/enom_pro directory tree
1. Activate the addon in WHMCS (if you don't see it, the symlink is wrong) ![Addon Config](http://cl.circletr.ee/image/0r3d301r2u0o/Image%202015-03-18%20at%209.09.32.png)


## WHMCS Dev Setup

1. Create a local API user
	* Username: `api`
	* Password: `12345`
	* White-list your localhost IP address (127.0.0.1, ::1, etc.)
		* WHMCS Dev -> Admin
		* Setup -> General Settings
		* Security Tab -> **API IP Access Restriction** -> Add IP
		* Press save ;-)
1. Configure phpunit
	* Copy phpunit.dist.xml -> phpunit.xml
	* Specific constants that should be customized:
		* `WHMCS_API_URL` - Local WHMCS install API URL endpoint (which is located inside /whmcs/includes/api.php)
		* `MYSQL_HOST`, `MYSQL_PASS`, `MYSQL_USER`, `MYSQL_DB`
			* Use the same settings as your dev install of WHMCS, reference the /whmcs/configuration.php if necessary
			* This way you can preview states between the interface and failing tests
		* `ENOM_PRO_TEMP` - Absolute path to a tmp directory for writing cache files
		* `WHMCS_API_UN` / `WHMCS_API_PW` - Should be the same API credentials from above

### Running tests
 * Run the tests from the project root: `vendor/bin/phpunit -c phpunit.dist.xml`
 * To exclude groups, use `vendor/bin/phpunit -c phpunit.dist.xml --exclude-group slow`
 * To view groups use: `vendor/bin/phpunit --list-groups`
     - __nogroup__
     - domains
     - namespinner
     - pricing
     - remote
     - settings
     - slow
     - srv
     - ssl
     - stats
     - tests
     - tlds
     - transfers
     - views
     - whmcs
     - whois


