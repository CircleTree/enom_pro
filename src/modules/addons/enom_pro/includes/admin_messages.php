<?php if ( ! is_writable( ENOM_PRO_TEMP ) ) : ?>
	<div class="alert alert-danger">
		<p>Temp Directory is unwriteable. Please CHMOD 777 <?php echo ENOM_PRO_TEMP; ?> to continue.</p>
	</div>
<?php endif; ?>

<?php if ( ! enom_pro::is_ssl_email_installed() ) : ?>
	<div class="alert alert-danger">
		<p>
			SSL Email template is not installed.
			<a class="btn btn-danger"
			   href="<?php echo enom_pro::MODULE_LINK ?>&action=install_ssl_template">Install Now
			</a>
		</p>
	</div>
<?php endif; ?>

<?php //SSL Email Installed. Edit Link ?>
<?php if ( isset( $_GET['ssl_email'] ) && (int) $_GET['ssl_email'] > 0 ) : ?>
	<div class="alert alert-success">
		<p>Installed.
			<a class="btn btn-default"
			   onclick="javascript:$('#edit_ssl_sidebar').trigger('click');return false;"
			   href="configemailtemplates.php?action=edit&id=<?php echo (int) $_GET['ssl_email'] ?>">
				Edit Now
			</a>
		</p>
	</div>
<?php endif; ?>
<?php if ( ! enom_pro_controller::isDismissed( 'pro-install' ) ) : ?>
	<div class="alert alert-success fade in">
		<button type="button"
		        class="close"
		        data-dismiss="alert"
		        data-alert="pro-install"
		        aria-hidden="true">&times;</button>
		<?php $license = new enom_pro_license(); ?>
		<h1>Thank you<?php echo( false !== ( $name = $license->getCustomerName() ) ? ', ' . $name : '' ); ?>, for your support!</h1>

		<p>We appreciate your support of <?php echo ENOM_PRO; ?>, and hope it continues to provide a valuable service to you.</p>
		<p>If you're just getting started, please view our comprehensive suite of
			<a href="<?php echo enom_pro::MODULE_LINK ?>&view=help">help articles</a>
		   .
		</p>
		<h3>Get Help with Integration/Installation of <?php echo ENOM_PRO; ?></h3>

		<p>
			Order our professional installation &amp; Integration service <br />
			<a
				href="<?php echo enom_pro::INSTALL_URL; ?>" target="_blank"
				class="btn btn-default">Order Install/Integration Service
				<span class="enom-pro-icon enom-pro-icon-secure"></span>
			</a>
		</p>
		<p>
			<em>PS &mdash; You can dismiss these new messages permanently by clicking the
				<button class="btn btn-default disabled">&times;</button>
			    in the top right.</em>
		</p>
	</div>
<?php endif; ?>

<?php //Auto-upgrade manual overwrite files  //TODO test $_SESSION works properly in WHMCS v6?>
<?php if ( isset( $_SESSION['manual_files'] ) ) : ?>
	<?php $fileToolTip = 'Click to view full path'; ?>
	<?php if ( ! empty( $_SESSION['manual_files']['templates'] ) ): ?>
		<div class="alert alert-info">
			<p>
				The following client area template files were already in place. <br />
				<b><em>You will only need to update them if have manually modified any of the files.</em></b> <br />
				If they have been modified, you will need to be manually upgraded:
			</p>
			<ul>
				<?php foreach (
					$_SESSION['manual_files']['templates'] as $filepath
				): ?>
					<li>
						<a href="#"
						   data-path="<?php echo $filepath ?>"
						   title="<?php echo $fileToolTip ?>"
						   class="ep_tt filePathToggle"><?php echo basename( $filepath ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
			<p>Otherwise, feel free to
				<a class="btn btn-default"
				   href="<?php echo enom_pro::MODULE_LINK ?>&action=dismiss_manual_upgrade">Dismiss Reminder
				</a>
			</p>
		</div>
	<?php endif; ?>
	<?php if ( ! empty( $_SESSION['manual_files']['core_files'] ) ): ?>
		<div class="alert alert-danger">
			<div>
				The following files were not writeable by the webserver, and will need to be manually upgraded, or
				you can <input type="text"
				               size="90"
				               value="chmod -R 777 <?php echo ENOM_PRO_ROOT; ?>"
				               onclick="this.select();return false;" /> and
				<a class="btn btn-default"
				   href="<?php echo enom_pro::MODULE_LINK ?>&action=do_upgrade">Try Again
				</a>
			</div>
			<ul>
				<?php foreach (
					$_SESSION['manual_files']['core_files'] as $filepath
				): ?>
					<li>
						<a href="#"
						   data-path="<?php echo $filepath ?>"
						   title="<?php echo $fileToolTip ?>"
						   class="ep_tt filePathToggle"><?php echo basename( $filepath ); ?></a>
					</li>
				<?php endforeach; ?>
			</ul>
			<a class="btn btn-default"
			   href="<?php echo enom_pro::MODULE_LINK ?>&action=dismiss_manual_upgrade">Dismiss Reminder
			</a>
		</div>
	<?php endif; ?>
<?php endif; ?>

<?php //Upgrade Successful  ?>
<?php if ( isset( $_GET['upgraded'] ) ) : ?>
	<div class="alert alert-success">
		Upgrade Successful. Running version <?php echo ENOM_PRO_VERSION; ?>.
	</div>
<?php endif; ?>

<?php //Alert dismissed ?>
<?php if ( isset( $_GET['dismissed'] ) ) : ?>
	<div class="alert alert-success slideup">
		<p>Dismissed</p>
	</div>
<?php endif; ?>

<?php //Update Check  ?>
<?php if ( isset( $_GET['checked'] ) ): ?>
	<div class="alert <?php echo enom_pro_license::is_update_available() ? 'alert-warning' : 'alert-success'; ?>">
		<h4>Checked for updates.</h4>
		<?php if ( ! enom_pro_license::is_update_available() ): ?>
			You are running the latest release.
		<?php else: ?>
			Upgrade available.
		<?php endif; ?>
	</div>
<?php endif; ?>
<?php //Update Available  ?>
<?php if ( enom_pro_license::is_update_available() ) : ?>
	<?php $license = new enom_pro_license(); ?>
	<?php $status = $license->get_supportandUpdates(); ?>
	<?php if ( ! in_array( $status['status'], array( 'active', 'beta' ) ) ) : ?>
		<?php //Support & updates expired ?>
		<div class="alert alert-danger">
			<div class="text-right">
				<a class="btn btn-warning" href="<?php echo enom_pro::MODULE_LINK ?>&action=do_upgrade_check">
					Already renewed? Click here to refresh. <span class="enom-pro-icon-refresh-alt"></span>
				</a>
			</div>
			<h2>
				<?php if ( isset( $status['duedate'] ) ) : ?>
					Update Subscription Expired on <?php echo $status['duedate']; ?>
			<?php else: ?>
					No support &amp; updates addon found
			<?php endif; ?>
				<br/>
				<a target="_blank" href="https://mycircletree.com/client-area/cart.php?gid=addons"
				   class="btn btn-success btn-lg">
					<span class="enom-pro-icon-cart-plus"></span>
					 Renew Now
				</a>
				to enjoy these great new features:
			</h2>
			<?php enom_pro_changelog_dom(); ?>

		</div>
	<?php else://active & update available?>
		<?php if ( ! enom_pro::is_upgrader_compatible() ): ?>
			<div class="errorbox">
				<h3>Please upgrade PHP to use our auto-upgrade feature.
				    Current PHP Version: <code><?php echo PHP_VERSION ?></code> Recommended: ><code>5.3.6</code></h3>
			</div>
		<?php else: //Compatible?>
			<div class="alert alert-success" id="upgradeAlert">
				<button type="button"
				        class="close"
				        data-dismiss="alert"
				        aria-hidden="true">&uarr;</button>
				<h2>Upgrade available!</h2>
				<span class="badge">Update using our 1-click upgrade system.</span>
				<a id="doUpgrade" class="btn btn-lg btn-success" href="<?php echo enom_pro_license::DO_UPGRADE_URL; ?>">
					<span class="enom-pro-icon enom-pro-icon-update"></span>
					Upgrade to Version <?php echo enom_pro_license::get_latest_version(); ?> now!
				</a>
				-or-
				<a href="<?php echo $enom->get_upgrade_zip_url() ?>">Download Now</a>
				<?php enom_pro_changelog_dom(); ?>
			</div>
		<?php endif; //End upgrader compat. check?>
	<?php endif;//End Support & updates expired?>
<?php endif;//End Update is Available?>

<?php
//TODO add beta downgrade help text about BETA opt-in
if ( enom_pro_license::is_downgrade_available() && ! enom_pro_controller::isDismissed( 'beta-downgrade' ) )  :
	?>
	<div class="alert alert-danger fade in">
		<button type="button"
		        class="close"
		        data-dismiss="alert"
		        data-alert="beta-downgrade"
		        aria-hidden="true">&times;</button>
		<h3>Beta Downgrade</h3>

		<p>Warning - this may have unexpected results. Please have a backup before proceeding with any major operations.</p>
		<a id="doUpgrade" class="btn btn-lg btn-warning" href="<?php echo enom_pro_license::DO_UPGRADE_URL; ?>">
			<span class="enom-pro-icon enom-pro-icon-update"></span>
			Downgrade to Version <?php echo enom_pro_license::get_latest_version(); ?>.
		</a>
	</div>
<?php endif; ?>

<?php if ( ! enom_pro::areAnyWidgetsEnabled() && ! isset( $_GET['view'] ) ) : ?>
	<div class="alert alert-danger">
		<h3>No widgets enabled</h3>

		<div class="row">

			<a href="configadminroles.php">

				<img src="../modules/addons/enom_pro/images/admin-roles-list.jpg" height="" width=""
				     title="Click to go to Administrator Roles, and edit the role you want to give access to widgets"
				     class="col-md-6 ep_tt img-rounded" />
			</a>
			<img src="../modules/addons/enom_pro/images/admin-roles-widgets.jpg" height="" width=""
			     title="From the edit role screen, you can enable/disable eNom PRO widgets for each role."
			     class="col-md-6 ep_tt img-rounded" />
		</div>
	</div>
<?php endif; ?>

<?php if ( isset( $_GET['saved-exchange'] ) ) : ?>
	<div class="alert alert-success slideup fixed">
		<p>Custom Exchange Rate Saved</p>
	</div>
<?php endif; ?>

<?php if ( isset( $_COOKIE ) && isset( $_COOKIE['WHMCSMinSidebar'] ) && true == $_COOKIE['WHMCSMinSidebar'] ) : ?>
	<div class="alert alert-info">
		<h3>Where are the <?php echo ENOM_PRO; ?> links?</h3>

		<p>eNom PRO Uses the WHMCS Sidebar
			<span class="enom-pro-icon enom-pro-icon-log"></span>
		   for links.
			<a href="#" class="btn btn-primary"
			   onclick="sidebarOpen();jQuery(this).closest('.alert').slideUp();return false;">Open Sidebar
			</a>
		</p>
	</div>
<?php endif; ?>

<?php
/**
 * Outputs Changelog html
 */
function enom_pro_changelog_dom() { ?>
	<?php if ( enom_pro::isBeta() ) : ?>
		<div id="enom_pro_beta_changelog">
			<div class="enom_pro_loader"></div>
		</div>
	<?php else: ?>
		<div id="enom_pro_changelog"></div>
	<?php endif; ?>
<?php }