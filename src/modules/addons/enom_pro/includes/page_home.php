<?php
/**
 * Project: enom_pro
 * @license GPL v2
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */
?>
<div class="well well-sm">
	<h2>Welcome to eNom PRO!</h2>

	<p class="text-center"><strong>Interactive widgets are available on the WHMCS admin homepage.</strong>
		<br/>
		<br/>
		<a class="btn btn-default" href="index.php">Click here to view Admin homepage</a>
	</p>
	
	<br/>

	<a class="btn btn-success large"
	   href="<?php echo enom_pro::MODULE_LINK . '&view=domain_import' ?>">Import
	                                                                      Domains
		<span class="enom-pro-icon enom-pro-icon-domains"></span>
	</a>
	
	<a class="btn btn-success large"
	   href="<?php echo enom_pro::MODULE_LINK . '&view=pricing_import' ?>">Import TLD Pricing
		<span class="enom-pro-icon enom-pro-icon-tag"></span>
	</a>
	
	<a class="btn btn-primary large"
	   href="<?php echo enom_pro::MODULE_LINK . '&view=help' ?>">View Help
		<span class="enom-pro-icon enom-pro-icon-question"></span>
	</a>
</div>
