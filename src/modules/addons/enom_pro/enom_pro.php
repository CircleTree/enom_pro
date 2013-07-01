<?php
/**
 * eNom Pro WHMCS Addon
 * @version @VERSION@
 * Copyright 2013 Orion IP Ventures, LLC. All Rights Reserved.
 * Licenses Resold by Circle Tree, LLC. Under Reseller Licensing Agreement
 * @codeCoverageIgnore
 */
defined("WHMCS") or die("This file cannot be accessed directly");
define("ENOM_PRO_VERSION",'@VERSION@');
define('ENOM_PRO_INCLUDES', ROOTDIR . '/modules/addons/enom_pro/includes/');
require_once ENOM_PRO_INCLUDES . 'class.enom_pro.php';
require_once ENOM_PRO_INCLUDES . 'class.enom_pro_license.php';

/**
 * @return multitype:string multitype:multitype:string  multitype:string number
 * @codeCoverageIgnore
 */
function enom_pro_config ()
{
    $spinner_help = " <br/><span class=\"textred\" >Make sure your active cart & domain checker templates have {\$namespinner} in them.</span>";
    $config = array(
            'name'=>'@NAME@' . (!isset($_GET['view']) ? '' : ' - Import'),
            'version'=>'@VERSION@',
            'author'=>'<a href="http://orionipventures.com/">Orion IP Ventures, LLC.</a>',
            'description'=>'Shows eNom Balance and active Transfers on the admin homepage in widgets. Adds a clientarea page that displays active transfers to clients.',
            'fields'=>array(
                    'quicklink'=>array('FriendlyName'=>"","Type"=>"null","Description"=>
                            '<span style="font-size:16pt;padding:0 10px; 0;" >@NAME@ Settings</span>
                            <a href="'.enom_pro::MODULE_LINK.'">Go to @NAME@ &rarr;</a>'),
                    'license'=>array('FriendlyName'=>"License Key","Type"=>"text","Size"=>"30"),
                    'debug'=>array('FriendlyName'=>"Debug Mode","Type"=>"yesno",
                            "Description"=>"Enable debug messages on frontend. Used for troubleshooting the namespinner,
                             for example."),
                    'ssl_days'=>array('FriendlyName'=>"Expiring SSL Days","Type"=>"dropdown",
                            "Options"=>"7,15,30,60,90,180,365,730","Default"=>"30",
                            "Description"=>"Number of days until SSL Certificate Expiration to show in Widget"),
                    'balance_warning'=>array('FriendlyName'=>"Credit Balance Warning Threshold","Type"=>"dropdown",
                            "Options"=>"Off,10,25,50,100,150,200,500,1000,5000","Default"=>"50",
                            "Description"=>"Turns the Credit Balance Widget into a RED flashing warning indicator"),
                    'import_per_page'=>array('FriendlyName'=>"Import Results","Type"=>"dropdown",
                            "Options"=>"5,10,25,50,75,100","Default"=>"25",
                            "Description"=>"Results Per Page on the Domain Import Page"),
                    'spinner_results'=>array('FriendlyName'=>"Namespinner Results","Type"=>"text","Default"=>10,
                            "Description"=>"Max Number of namespinner results to show".$spinner_help,'Size'=>10),
                    'spinner_columns'=>array('FriendlyName'=>"Namespinner Columns","Type"=>"dropdown",
                            "Options"=>"1,2,3,4","Default"=>"3","Description"=>"Number of columns to display results in.
                            Make sure it is divisible by the # of results above to make nice columns.",'Size'=>10),
                    'spinner_sortby'=>array('FriendlyName'=>"Sort Results","Type"=>"dropdown",
                            "Options"=>"score,domain","Default"=>"score",
                            "Description"=>"Sort namespinner results by score or domain name"),
                    'spinner_sort_order'=>array('FriendlyName'=>"Sort Order","Type"=>"dropdown",
                            "Options"=>"Ascending,Descending","Default"=>"Descending",
                            "Description"=>"Sort order for results"),
                    'spinner_checkout'=>array('FriendlyName'=>"Show Add to Cart Button?","Type"=>"yesno",
                            "Description"=>"Display checkout button at the bottom of namespinner results"),
                    'cart_css_class'=>array('FriendlyName'=>"Cart CSS Class","Type"=>"dropdown",
                            "Options"=>"btn,btn-primary,button,custom",
                            "Default"=>"btn-primary","Description"=>"Customize the Add to Cart button by CSS class"),
                    'custom_cart_css_class'=>array('FriendlyName'=>"Cart CSS Class","Type"=>"text",
                            "Description"=>"Add a custom cart CSS class"),
                    'spinner_css'=>array('FriendlyName'=>"Style Spinner?","Type"=>"yesno",
                            "Description"=>"Include Namespinner CSS File"),
                    'spinner_animation'=>array('FriendlyName'=>"Namespinner Result Animation Speed",
                            "Type"=>"dropdown","Default"=>"Medium","Options"=>"Off,Slow,Medium,Fast",
                            "Description"=>"Number of namespinner results to show",'Size'=>10),
                    'spinner_com'=>array('FriendlyName'=>".com","Type"=>"yesno",
                            "Description"=>"Display .com namespinner results"),
                    'spinner_net'=>array('FriendlyName'=>".net","Type"=>"yesno",
                            "Description"=>"Display .net namespinner results"),
                    'spinner_tv'=>array('FriendlyName'=>".tv","Type"=>"yesno",
                            "Description"=>"Display .tv namespinner results"),
                    'spinner_cc'=>array('FriendlyName'=>".cc","Type"=>"yesno",
                            "Description"=>"Display .cc namespinner results"),
                    'spinner_hyphens'=>array('FriendlyName'=>"Hyphens","Type"=>"yesno",
                            "Description"=>"Use hyphens (-) in namespinner results"),
                    'spinner_numbers'=>array('FriendlyName'=>"Numbers","Type"=>"yesno",
                            "Description"=>"Use numbers in namespinner results"),
                    'spinner_sensitive'=>array('FriendlyName'=>"Block sensitive content","Type"=>"yesno",
                            "Description"=>"Block sensitive content"),
                    'spinner_basic'=>array('FriendlyName'=>"Basic Results","Type"=>"dropdown",
                            "Default"=>"Medium","Description"=>"Higher values return suggestions that are built by 
                            adding prefixes, suffixes, and words to the original input",
                            "Options"=>"Off,Low,Medium,High"),
                    'spinner_related'=>array('FriendlyName'=>"Related Results","Type"=>"dropdown","Default"=>"High",
                            "Description"=>"Higher values return domain names by interpreting the input semantically 
                            and construct suggestions with a similar meaning.<br/>
                            <b>Related=High will find terms that are synonyms of your input.</b>",
                            "Options"=>"Off,Low,Medium,High"),
                    'spinner_similiar'=>array('FriendlyName'=>"Similiar Results","Type"=>"dropdown","Default"=>"Medium",
                            "Description"=>"Higher values return suggestions that are similar to the customer's input, 
                            but not necessarily in meaning.<br/>
                            <b>Similar=High will generate more creative terms, with a slightly looser 
                            relationship to your input, than Related=High.</b>","Options"=>"Off,Low,Medium,High"),
                    'spinner_topical'=>array('FriendlyName'=>"Topical Results","Type"=>"dropdown","Default"=>"High",
                            "Description"=>"Higher values return suggestions that reflect current topics 
                            and popular words.","Options"=>"Off,Low,Medium,High"),
                    'quicklink2'=>array('FriendlyName'=>"","Type"=>"null",
                            "Description"=>'<b><a href="'.enom_pro::MODULE_LINK.'">Go to @NAME@ &rarr;</a></b>'),
            )
    );

    return $config;
}

/**
 * @codeCoverageIgnore
 */
function enom_pro_activate ()
{
    mysql_query("BEGIN");
    $query = "CREATE TABLE `mod_enom_pro` (
            `id` int(1) NOT NULL,
            `local` text NOT NULL,
            PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    mysql_query($query);
    $query = "INSERT INTO `mod_enom_pro` VALUES(0, '');";
    mysql_query($query);
    //Delete the defaults so MySQL doesn't error out on duplicate insert
    $query = "	DELETE FROM `tbladdonmodules` WHERE `module` = 'enom_pro';";
    mysql_query($query);
    //Insert these defaults due to a bug in the WHMCS addon api with checkboxes
    $query = "
            INSERT INTO `tbladdonmodules` VALUES('enom_pro', 'spinner_net', 'on');";
    mysql_query($query);
    $query = "
            INSERT INTO `tbladdonmodules` VALUES('enom_pro', 'spinner_com', 'on');";
    mysql_query($query);
    $query = "
            INSERT INTO `tbladdonmodules` VALUES('enom_pro', 'spinner_css', 'on');";
    mysql_query($query);
    $query = "
            INSERT INTO `tbladdonmodules` VALUES('enom_pro', 'spinner_checkout', 'on');";
    mysql_query($query);
    mysql_query("COMMIT");
    if (mysql_error()) die (mysql_error());
}

/**
 * License Errors
 */
class LicenseExeption extends Exception {}
/**
 * cURL / XML Parsing errors
 */
class RemoteException extends Exception
{
    const RETRY_LIMIT = 1;
    const XML_PARSING_EXCEPTION = 2;
    const CURL_EXCEPTION = 3;
}

/**
 * @param unknown $vars
 * @return string
 * @codeCoverageIgnore
 */
function enom_pro_sidebar ($vars)
{
    $sidebar = '<span class="header">
            <img src="images/icons/domainresolver.png" class="absmiddle" width=16 height=16 />@NAME@</span>
                <ul class="menu">
                <li><a class="btn" href="'.enom_pro::MODULE_LINK.'">Home</a></li>
                    <li><a class="btn" href="'.enom_pro::MODULE_LINK.'&view=import">Domain Importer</a></li>
                    <li><a class="btn" href="configaddonmods.php#enom_pro">Settings</a></li>
                    </ul>
                    <span class="header">@NAME@ Meta</span>
                    <ul class="menu">
                    <li>
                    <a href="#">Version: '.ENOM_PRO_VERSION.'</a>
             </li>
             <li>
             <a href="http://mycircletree.com/client-area/knowledgebase.php?action=displayarticle&id=43"
                            target="_blank" >View Changelog</a></li>
             </li>
             <li>
                <a href="'.enom_pro::INSTALL_URL.'" target="_blank" >Install Service</a></h3>
                </li>
                </ul>';

    return $sidebar;
}
/**
 * @param unknown $vars
 * @codeCoverageIgnore
 */
function enom_pro_output ($vars)
{
    try {
        $enom = new enom_pro();
        $enom->getAvailableBalance();
        if (isset($_GET['view']) && 'import' == $_GET['view']) {
            $enom->render_domain_import_page();
    
            return;
        }
        $license = new enom_pro_license();
        if ($license->updateAvailable())
            echo $license->updateAvailable();
    ?>
        <div id="enom_faq">
            <p>
                Looks like you're connected to enom! Want to import some domains to
                WHMCS? <a class="btn btn-success large"
                    href="<?php echo $_SERVER['PHP_SELF'] . '?module=enom_pro&view=import'?>">Import
                    Domains!</a>
            </p>
            <h1>FAQ</h1>
            <h2>Where do I enter my eNom API info?</h2>
            <p>
                eNom PRO gets the registrar info directly from whmcs. To change your
                registrar info, <a class="btn" href="configregistrars.php#enom">click
                    here.</a>
            </p>
            <h2>No Admin Widgets?</h2>
            <p class="textred">
                Make sure you add the admin roles you want to see the widgets under <a
                    class="btn" href="configadminroles.php">WHMCS Admin Roles</a>.
            </p>
            <h1>Quick Start</h1>
            <h2>Client Area Transfers</h2>
            <p>You need to install the sample code included inside of
                enom_pro/templates/ into your active WHMCS template.</p>
            <h2>SRV Record Editor</h2>
            <p>Copy the enom_srv.php file to the /whmcs directory and add the
                enom_srv.tpl to your active template.</p>
            <p>Then, link to it inside of clientareadomaindetails.tpl with:</p>
            <pre class="code">
<?php echo htmlentities('{if $enom_srv }') . PHP_EOL;?>
    <?php echo htmlentities('<li><a href="enom_srv.php?id={$domainid}" id="enom_srv">SRV Records</a></li>') . PHP_EOL;?>
<?php echo htmlentities('{/if}'). PHP_EOL?>
            </pre>
            <div class="inline-wrap">
                You can also send a client a link to /whmcs/enom_srv.php
                <pre class="code inline">
                    <?php echo htmlentities('<a href="enom_srv.php">SRV Records</a>')?>
                </pre>
                and they will be able to choose the active enom domain to edit SRV
                records for.
            </div>
            <h2>NameSpinner</h2>
            <h3>Domain Checker</h3>
            <p>See the included domainchecker.tpl template for a working example.</p>
            <h3>Order Form Setup</h3>
            <div class="inline-wrap">
                <span>Include the </span>
                <pre class="code inline">{$namespinner}</pre>
                <span> template tag in your domain (domainchecker.tpl) and shopping
                    cart template files to include the enom name spinner!</span>
            </div>
            <b>Make sure you put it in the template as follows:</b>
<pre class="code">
{if $availabilityresults}
    <?php echo htmlentities('<form>').PHP_EOL;?>
    <?php echo htmlentities('<!-- IMPORTANT -->').PHP_EOL;?>
    <?php echo htmlentities('<!-- There will be WHMCS HTML here for the form. Put the tag below where you want the results to appear. -->').PHP_EOL;?>
    <?php echo htmlentities('<!-- See the included domainchecker.tpl for a working example -->').PHP_EOL;?>
        {$namespinner}
    <?php echo htmlentities('</form>').PHP_EOL;?>
{/if}
</pre>
            <p>The place you put the code is where the domain spinner suggestions
                will appear. See the included domainchecker.tpl for an example</p>
            <h3>
                Lost? Order our professional installation service here: <a
                    href="<?php echo enom_pro::INSTALL_URL;?>" target="_blank"
                    class="btn">Install Service</a>
            </h3>
        </div>
    <?php } catch (EnomException $e) { ?>
            <div class="alert alert-error">
                <h2>There was a problem communicating with the eNom API:</h2>
                <?php echo $e->getMessage(); ?>
            </div>
    <?php } catch (Exception $e) { ?>
            <div class="alert alert-error">
                <h2>Error</h2>
                <?php echo $e->getMessage(); ?>
            </div>
            <?php 
    }
}
