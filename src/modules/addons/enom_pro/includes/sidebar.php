<?php
/**
 * Project: enom_pro
 * @license GPL v2
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */
?>
<div class="enom_pro_output sidebar">
<span class="header">
    <span class="enom-pro-icon enom-pro-icon-globe"></span> <?php echo ENOM_PRO ?> <?php if (! enom_pro::isBetaBuild()) : ?> <?php echo ENOM_PRO_VERSION; ?> <?php endif;  ?>
</span>
	<ul class="menu">
		<li>
			<a class="btn btn-block btn-default"
			   href="<?php echo enom_pro::MODULE_LINK; ?>">
				<span class="enom-pro-icon enom-pro-icon-home"></span>
				Home
			</a>
		</li>
		<li>
			<a class="btn btn-block btn-default"
			   href="<?php echo enom_pro::MODULE_LINK; ?>&view=domain_import">
				<span class="enom-pro-icon enom-pro-icon-domains"></span>
				Import Domains
			</a>
		</li>
		<li>
			<a class="btn btn-block btn-default"
			   href="<?php echo enom_pro::MODULE_LINK; ?>&view=pricing_import">
				<span class="enom-pro-icon enom-pro-icon-tag"></span>
				Import TLD Pricing
				<span class="label label-info">3.0</span>
			</a>
		</li>
		<li>
			<a class="btn btn-block btn-default"
			   href="<?php echo enom_pro::MODULE_LINK; ?>&view=pricing_sort">
				<span class="enom-pro-icon enom-pro-icon-sort"></span>
				Sort Pricing
				<span class="label label-primary">NEW!</span>
			</a>
		</li>
		<li>
			<a class="btn btn-block btn-default"
			   href="<?php echo enom_pro::MODULE_LINK; ?>&view=whois_checker">
				<span class="enom-pro-icon enom-pro-icon-sort"></span>
				WHOIS Checker
				<span class="label label-primary">NEW!</span>
			</a>
		</li>
		<?php $id = enom_pro::is_ssl_email_installed(); ?>
		<?php if ( $id > 0 ) : ?>
			<li>
				<a class="btn btn-default btn-block ep_lightbox"
				   id="edit_ssl_sidebar"
				   title="Edit SSL Reminder Email"
				   data-width="90%"
				   data-no-refresh="true"
				   href="configemailtemplates.php?action=edit&id=<?php echo $id ?>">
					<span class="enom-pro-icon enom-pro-icon-mail-send"></span>
					Edit SSL Email
				</a>
			</li>
			<li>
				<a class="btn btn-default btn-block" href="<?php echo enom_pro::MODULE_LINK ?>&view=send_ssl_test">
					Preview SSL Emails
					<span class="label label-primary">NEW!</span>
				</a>
			</li>
		<?php else: ?>
			<li>
				<a class="btn btn-block btn-default"
				   href="<?php echo enom_pro::MODULE_LINK ?>&action=install_ssl_template">Install SSL Email
				</a>
			</li>
		<?php endif; ?>
		<li>
			<a class="btn btn-block btn-default ep_tt" data-placement="right"
			   title="<?php echo ENOM_PRO; ?> Help - Directly in your WHMCS"
			   href="<?php echo enom_pro::MODULE_LINK ?>&view=help">
				<span class="enom-pro-icon enom-pro-icon-question"></span>
				Help
				<span class="label label-primary">NEW!</span>
			</a>
		</li>
		<li>
			<a class="btn btn-block btn-default ep_lightbox settingsButton"
			   data-width="90%"
			   title="<?php echo ENOM_PRO; ?> Settings"
			   href="configaddonmods.php#enom_pro">
				<span class="enom-pro-icon enom-pro-icon-cog"></span>
				Settings
			</a>
		</li>
		<?php if ( enom_pro_license::isBetaOptedIn() ) : ?>
			<li>
				<?php enom_pro::getBetaReportLink(); ?>
			</li>
		<?php endif; ?>
	</ul>

	<span class="header"><?php echo ENOM_PRO; ?> Helpful Links</span>
	<ul class="menu">
		<li>
			<a class="ep_tt" data-placement="right"
			   title="<?php echo ENOM_PRO; ?> Help"
			   href="<?php echo enom_pro::MODULE_LINK ?>&view=help">
				<span class="enom-pro-icon enom-pro-icon-question"></span>
				Help
			</a>
		</li>
		<li>
			<a target="_blank"
			   href="systemmodulelog.php"
			   class="ep_tt ep_lightbox"
			   data-title="WHMCS Module Log"
			   data-width="90%"
			   data-placement="right"
			   data-no-refresh="true"
			   title="Useful for checking API Activity">
				<span class="enom-pro-icon enom-pro-icon-module-log"></span>
				Module Log
			</a>
		</li>
		<li>
			<a target="_blank"
			   href="systemactivitylog.php"
			   class="ep_tt ep_lightbox"
			   data-title="WHMCS Activity Log"
			   data-width="90%"
			   data-placement="right"
			   data-no-refresh="true"
			   title="Useful for viewing CRON Job Activity">
				<span class="enom-pro-icon enom-pro-icon-log"></span>
				Activity Log
			</a>
		</li>
		<li>
			<a href="configregistrars.php#enom"
			   class="ep_lightbox ep_tt"
			   id="edit_registrar"
			   title="Edit eNom Registrar Settings in WHMCS"
			   data-placement="right"
			   data-width="90%">
				<span class="enom-pro-icon enom-pro-icon-earth"></span>
				eNom Registrar Settings
			</a>
		</li>
		<li>
			<a href="configadminroles.php"
			   class="ep_lightbox ep_tt"
			   id="edit_registrar"
			   title="Edit Admin Roles to Enable Widgets."
			   data-placement="right"
			   data-width="90%">
				<span class="enom-pro-icon enom-pro-icon-verify-alt"></span>
				WHMCS Admin Roles
			</a>
		</li>
	</ul>
	<span class="header"><?php echo ENOM_PRO; ?> Meta</span>

	<table>
		<tr>
			<th class="text-right">Version</th>
			<td class="text-center">
				<?php if ( enom_pro_license::isBetaOptedIn() ) : ?>
					<span class="betaVersion">
						<?php echo ENOM_PRO_VERSION; ?>
					</span>
				<?php else: ?>
					<?php echo ENOM_PRO_VERSION; ?>
				<?php endif; ?>
				<span class="enom-pro-icon-code-fork"></span>
			</td>
		</tr>
		<tr>
			<td colspan="2" class="text-center">
				<span class="ep_tt" title="Checked for updates" data-placement="right">
					<span class="enom-pro-icon-clock"></span>
					<?php echo enom_pro_license::get_last_checked_time_ago(); ?>
				</span>
			</td>
		</tr>
	</table>
	<div class="upgradeAlertHidden">
		<a href="<?php echo enom_pro::MODULE_LINK; ?>&action=do_upgrade"
		   class="btn btn-block btn-success">Upgrade Now
			<span class="enom-pro-icon enom-pro-icon-update"></span>
		</a>
	</div>
	<div class="upgradeAlertHidden">
		<div class="alert alert-info">
			Version <?php echo enom_pro_license::get_latest_version() ?> <br />
			<a href="#" onclick="enom_pro.showUpgradeAlert(); return false;">View Change-Log</a>
		</div>
	</div>
	<a class="btn btn-default btn-xs btn-block"
	   href="<?php echo enom_pro::MODULE_LINK ?>&action=do_upgrade_check">
		Check for updates
		<span class="enom-pro-icon enom-pro-icon-update"></span>
	</a>
	<?php if ( ! enom_pro::isBeta() ) : ?>
		<a
			href="http://mycircletree.com/client-area/knowledgebase.php?action=displayarticle&id=43"
			class="btn btn-default btn-xs btn-block"
			target="_blank">
			View Changelog
		</a>
	<?php endif; ?>
	<a href="<?php echo enom_pro::INSTALL_URL; ?>"
	   target="_blank"
	   class="btn btn-default btn-xs btn-block">
		Order Install Service
		<span class="enom-pro-icon enom-pro-icon-secure"></span>
	</a>
	<a class="btn btn-default btn-xs btn-block ep_ajax ep_tt" data-placement="right"
	   title="Do you miss the getting started messages? Experience the fun, all over again!"
	   href="<?php echo enom_pro::MODULE_LINK ?>&action=reset_alerts">
		Restore Help Messages
	</a>
</div>
<p>&nbsp;</p>