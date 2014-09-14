<?php
/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */
defined('WHMCS') or die('RESTRICTED ACCESS');

$message = <<<EOL
eNom PRO Version: %VERSION%
PHP Version: %PHPVERSION%
WHMCS Version: %WHMCSVERSION%
Current Page: %CURRPAGE%

Please list the steps to reproduce the issue:
1 -
2 -
3 -

Bug Behavior / Additional Info


Thank you for your help!
EOL;
return $message;