## Dev Setup
1. Install Composer Globally

  `curl -sS https://getcomposer.org/installer | php`
  
  `mv composer.phar /usr/local/bin/composer`
1. Run composer install from the project root
1. Build the working copy `vendor/bin/phing` (add a file watcher to automatically build after changes to the src/ directory tree)
1. Symlink build/encoded(?_prep)/modules/addons/enom_pro to the whmcs/modules/addons/enom_pro directory tree
1. Activate the addon in WHMCS (if you don't see it, the symlink is wrong) ![Addon Config](http://cl.circletr.ee/image/0r3d301r2u0o/Image%202015-03-18%20at%209.09.32.png)
