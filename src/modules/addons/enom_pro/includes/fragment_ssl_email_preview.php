<?php
/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */ ?>
<link rel="stylesheet" href="../modules/addons/enom_pro/css/admin.min.css" />
<link rel="stylesheet" href="../modules/addons/enom_pro/css/bootstrap.min.css" />
<link rel="stylesheet" href="../modules/addons/enom_pro/css/bootstrap-theme.min.css" />
<div class="alert alert-warning">
	<h3>Preview only</h3>

	<p>
		WHMCS doesn't support email previews &mdash;
		<a href="https://requests.whmcs.com/responses/e-mail-template-preview"
		   target="_blank">please vote on this feature request
		</a>
		. <br />
		As such, this preview will <b>only provide a sample of the <em>eNom specific merge data</em> that will be used.
		</b>
	<div class="alert alert-danger">
		<p>WHMCS Merge fields such as {$signature} will not be parsed.</p>
	</div>
	</p>
</div>
<pre>Subject: <?php echo $subject; ?></pre>
<pre>Message: <br /><?php echo $merged; ?></pre>

