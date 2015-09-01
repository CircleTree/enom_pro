**********************************************************
*  eNom PRO WHMCS Addon Module                           *
*  Compatible with WHMCS 5.3+                            *
*                                                        *
*  README                                                *
*  (c)@YEAR@ Orion IP Ventures, LLC - ALL RIGHTS RESERVED  *
*  If you like this addon, please support us!            *
*  This software must be licensed, please only use       *
*  it if you pay for it. There is an open source version *
*  please keep the license check intact, and share any   *
*  improvements you make to the software.                *
*                                                        *
*  This software is provided as is with no warranty,     *
*  either expressed or implied. Support may be obtained  *
*  by visiting www.myCircleTree.com                      *
*                                                        *
**********************************************************

/**
* REQUIREMENTS
**/
    WHMCS 6+
    IonCube Loaders 4.6+ (Latest 4.8.x+ recommended)
    PHP 5.3.x+ (PHP 5.4 Recommended)
    MySQL 5.1.x+

/**
* Installation Instructions *
**/

Backup your files & DB
Unzip this package to your local machine
Upload the addon directory to the /whmcs/modules/addons/ directory
--You should be uploading the "enom_pro" directory to the 
--  same directory on the server
--  For example, the server should have a /whmcs/modules/addons/enom_pro/
--  Folder, and inside that folder, there should be an enom_pro.php file

Upload enom_srv.php to the /whmcs/ directory 
Copy the templates to your active WHMCS template.
	If you have customized your templates, see http://mycircletree.com/client-area/knowledgebase.php?action=displayarticle&id=57
Go to the admin -> addons panel & activate
Enter License key & Select the Admin Groups you want
	to give access to the addon
For widgets to appear MAKE SURE YOU SET THE ADMIN ROLES!
See the admin page for additional install instructions!