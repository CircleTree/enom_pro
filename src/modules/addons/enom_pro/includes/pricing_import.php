<?php if (isset($_GET['cleared'])) : ?>
    <div class="alert alert-info">Cache Cleared</div>
<?php endif; ?>
<?php if (isset($_GET['new'])):?>
    <div class="alert alert-success">
        <p>Created <?php echo (int) $_GET['new']?> tld pricing tiers</p>
    </div>
<?php endif;?>
<?php if (isset($_GET['updated'])):?>
    <div class="alert alert-success">
        <p>Updated <?php echo (int) $_GET['updated']?> tld pricing tiers</p>
    </div>
<?php endif;?>
<?php if (isset($_GET['deleted'])):?>
    <div class="alert alert-error">
        <p>Deleted <?php echo (int) $_GET['deleted']?> tld pricing tiers</p>
    </div>
<?php endif;?>
<?php if (isset($_GET['nochange'])):?>
    <div class="alert alert-error">
        <p>No Pricing was Selected for Update</p>
    </div>
<?php endif;?>
<?php 
/**
 * @var $this enom_pro
 */
// $this = new enom_pro();
if ($this->is_pricing_cached()) :
?>
<script src="../modules/addons/enom_pro/jquery.admin.js"></script>
<form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>?module=enom_pro&view=pricing_import" id="enom_pro_pricing_import">
    <input type="hidden" name="action" value="save_domain_pricing" />
    <input type="hidden" name="start" value="<?php echo isset($_GET['start']) ? (int) $_GET['start'] : '0';?>" />
    <input type="submit" value="Save" />
    <a href="#" onClick="$('[data-price]').val(''); return false;" class="btn" >Clear All Pricing</a>
    <a href="#" class="btn restore_all" >Reset All Pricing</a>
    <table class="datatable" id="enom_pro_pricing_table">
        <tr>
            <th colspan="3">TLD</th>
            <th>Status</th>
            <th colspan="2">1 Year
                <a href="#" data-year="1" class="btn btn-mini toggle_years">Toggle</a>
            </th>
            <?php foreach (array_keys(array_fill(2, 9, '')) as $key => $year) : ?>
                <th colspan="2"><?php echo $year; ?> Years
                    <a href="#" data-year="<?php echo $year?>" class="btn btn-mini toggle_years">Toggle</a>
                </th>
            <?php endforeach;?>
        </tr>
        <?php 
        $per_page = 10;
        $offset = isset($_GET['start']) ? (int) $_GET['start'] : 0;
        $domains = array_slice($this->getAllDomainsPricing(), $offset, $per_page);
        foreach ($domains as $tld => $price): ?>
            <tr>
                <td><?php echo $tld;?>
                    <?php $whmcs_pricing_for_tld = $this->get_whmcs_domain_pricing($tld);?>
                </td>
                <td>
                    <a href="#" data-tld="<?php echo $tld?>" class="btn btn-mini toggle_tld" title="Toggle">&rarr;</a>
                </td>
                <td>
                    <a href="#" data-tld="<?php echo $tld?>" class="btn btn-mini delete_tld" title="Toggle">x</a>
                </td>
                <td>
                    <?php if (count($whmcs_pricing_for_tld) > 0) : ?>
                        <span class="badge badge-info">WHMCS</span>
                    <?php else:?>
                        <span class="badge small">Not In WHMCS</span>
                    <?php endif;?>
                </td>
                <?php foreach (array_keys(array_fill(1, 10, '')) as $key => $year) : ?>
                    <td>
                        <?php 
                        $enom_price = number_format(($price * $year), 2);
                        $whmcs_price = isset($whmcs_pricing_for_tld[$year]) ? number_format($whmcs_pricing_for_tld[$year],2) : false;
                        ?>
                        <span class="price"><?php echo '$'. $enom_price;?></span>
                    </td>
                    <td>
                        <table>
                            <tr>
                                <td><span class="badge toggle_this_val">&rsaquo;</span></td>
                                <td>
                                    <input
                                        data-tld="<?php echo $tld?>"
                                        data-year="<?php echo $year;?>"
                                        data-price="<?php echo $enom_price;?>"
                                        title="Enom Price: <?php echo '$'.$enom_price; ?>"
                                        size="6"
                                        class="ep_tt"
                                        type="text"
                                        name="pricing[<?php echo $tld?>][<?php echo $year?>]"
                                        value="<?php echo $whmcs_price; ?>"
                                    />
                                </td>
                            </tr>
                        </table>                        
                    </td>
                <?php endforeach;?>
            </tr>
        <?php endforeach;?>
    </table>
</form>
 <ul class="pager">
	<li class="previous">
	   <?php if (@$_GET['start'] >= $per_page) :?>
            <a href="<?php echo $_SERVER['PHP_SELF'];?>?module=enom_pro&view=pricing_import&start=<?php echo isset($_GET['start']) ? (int) $_GET['start'] - $per_page : $per_page?>">&larr; Prev</a>
        <?php endif;?>
	</li>
	<li class="next">
        <?php if (@$_GET['start'] <= ( count($this->getAllDomainsPricing()) - $per_page)) :?>
            <a href="<?php echo $_SERVER['PHP_SELF'];?>?module=enom_pro&view=pricing_import&start=<?php echo isset($_GET['start']) ? (int) $_GET['start'] + $per_page : $per_page?>">Next &rarr;</a>
        <?php endif;?>	   
	</li>
</ul>

<?php echo count($this->getAllDomainsPricing()) ?> TLDs.

Cached from <?php echo $this->get_price_cache_date();?>

<a class="btn btn-mini" href="<?php echo enom_pro::MODULE_LINK; ?>&action=clear_price_cache">Clear Cache</a>
<?php else: ?>
<div class="alert">
    <h3>Loading pricing for <?php echo count($this->getTLDs())?> top level domains.</h3>
    <div class="enom_pro_loader"></div>
    <h3 class="hidden"><em>This may take a minute...</em></h3>
</div>
<script>
jQuery(function($) {
	setTimeout(function  (){
		$(".alert .hidden").fadeIn(); 
	}, 3000);
	  $.ajax({
		    url: '<?php echo enom_pro::MODULE_LINK; ?>&action=get_pricing_data',
		    success: function  (data)
		    {
			    if (data == 'success') {
			        window.location = 'addonmodules.php?module=enom_pro&view=pricing_import';
			    }
		    }
	  });
})
</script>
<?php endif;?>