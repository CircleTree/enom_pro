<?php
/**
 * Project: enom_pro
 * Build: @BUILD_DATE@
 * Version: @VERSION@
 */
/** @var enom_pro $this  */
$warning_level = $this->get_addon_setting('balance_warning');
$available = (float) preg_replace("/([^0-9.])/i", "", $this->getAvailableBalance());
$warning = $available <= $warning_level ? true : false;
if ('off' == strtolower($warning_level)) {
	$warning = false;
}
$class = $warning ? 'alert-danger' : 'alert-success'; ?>
<div id="enom_balance_message" class="alert enom_pro_widget <?php echo $class ?>">
Enom Credit Balance: $<?php echo $this->getBalance(); ?> Available: <b style="color: #000000;">$<?php echo $this->getAvailableBalance() ?></b>
<a class="btn btn-default btn-xs" href="https://www.enom.com/myaccount/RefillAccount.asp" target="_blank">Refill Account <span class="enom-pro-icon enom-pro-icon-refill-account"></span></a>
</div>
<?php if ($warning) :  ?>
<script>
jQuery(function($) {
    var $message = $("#enom_balance_message");
    setInterval(function  () {
        if ($message.hasClass("inset")) {
              $message.removeClass("inset");
        } else {
              $message.addClass("inset");
        }
    },500)
});
</script>
<?php endif;?>
<?php $license = new enom_pro_license(); ?>
<?php if ($license->is_update_available()) :?>
	<div class="alert alert-info text-center"><b>Update available!</b>
		<span class="text-muted">Version: <?php echo $this->license->get_latest_version() ?></span><br/>
		<a class="btn btn-success btn-block" href="<?php echo enom_pro_license::DO_UPGRADE_URL; ?>">
			Upgrade automatically
			<span class="enom-pro-icon enom-pro-icon-update"></span>
		</a>
	</div>
<?php endif;?>