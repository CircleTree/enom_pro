<?php if (isset($_GET['upgraded'])) :?>
	<div class="alert alert-success">
		Upgrade Successful. Running version <?php echo ENOM_PRO_VERSION;?>.
	</div>
<?php endif;?>
<?php if (isset($_GET['dismissed'])) :?>
	<div class="alert alert-success slideup">
		<p>Dismissed</p>
	</div>
<?php endif;?>
<?php if (isset($_GET['checked'])):?>
	<div class="alert <?php echo enom_pro_license::is_update_available() ? 'alert-warning' : 'alert-success';?>">
		<h4>Checked for updates.</h4>
		<?php if (! enom_pro_license::is_update_available()):?>
			You are running the latest release.
		<?php else:?>
			Upgrade available.
		<?php endif;?>
	</div>
<?php endif;?>
<?php if (enom_pro_license::is_update_available()) :?>
	<?php $status = $enom->license->get_supportandUpdates();?>
	<?php if ($status['status'] != 'active') :?>
		<?php //Support & updates expired ?>
		<div class="alert alert-danger">
			<div class="floatright">
				<a class="btn btn-warning btn-lg" href="<?php echo enom_pro::MODULE_LINK?>&action=do_upgrade_check">
					Already renewed? Click here to refresh.
				</a>
			</div>
			<p>Update Subscription Expired. Expired on <?php echo $status['duedate'];?></p>
			<h1><a target="_blank" href="https://mycircletree.com/client-area/cart.php?gid=addons" class="btn btn-inverse" >Renew Now</a>
				to enjoy these great new features:</h1>
			<div id="enom_pro_changelog"></div>
		</div>
	<?php else://active & update available?>
		<?php if (! enom_pro::is_upgrader_compatible()):?>
			<div class="errorbox">
				<h3>Please upgrade PHP to use our auto-upgrade feature.
					Current PHP Version: <code><?php echo PHP_VERSION?></code> Recommended: ><code>5.3.6</code></h3>
			</div>
		<?php else: //Compatible?>
			<div class="alert alert-success">
				<h2>Upgrade available!</h2>
				<span class="badge" >Update using our 1-click upgrade system.</span>
				<a id="doUpgrade" class="btn btn-lg btn-success" href="<?php echo enom_pro_license::DO_UPGRADE_URL;?>">
					Upgrade to Version <?php echo enom_pro_license::get_latest_version();?> now!
				</a> -or- <a href="<?php echo $enom->get_upgrade_zip_url()?>">Download Now</a>
				<div id="enom_pro_changelog"></div>
			</div>
		<?php endif; //End upgrader compat. check?>
	<?php endif;//End Support & updates expired?>
<?php endif;//End Update is Available?>