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
Enom Credit Balance: <?php echo $this->getBalance(); ?> Available: <b><?php echo $this->getAvailableBalance() ?></b>
<a class="btn btn-default btn-xs" href="https://www.enom.com/myaccount/RefillAccount.asp" target="_blank">Refill Account</a>
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
<?php if ($this->license->is_update_available()) :?>
	<div class="alert alert-warning aligncenter"><b>Update available:</b>
		<?php echo $this->license->get_latest_version() ?><br/>
		<a class="btn btn-primary btn-block" href="<?php echo enom_pro_license::DO_UPGRADE_URL; ?>">Upgrade automatically</a>
	</div>
<?php endif;?>