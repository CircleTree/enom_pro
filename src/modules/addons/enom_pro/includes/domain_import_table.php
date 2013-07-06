<?php 
$enom = new enom_pro();
$show_only = isset($_GET['show_only']) ? $_GET['show_only'] : false;
$per_page = $enom->get_addon_setting('import_per_page');
$domains_array = $enom->getDomainsWithClients($enom->get_addon_setting('import_per_page'), (int) $_GET['start'], $show_only);
$list_meta = $enom->getListMeta();
if ( empty($domains_array) ) {
    echo '<div class="alert alert-error"><p>No domains returned from eNom.</p></div>';
    return;
}
        ?>

<table class="table-hover" id="import_table">
    <tr>
        <th>Domain</th>
        <th>Status</th>
    </tr>
    <?php foreach ($domains_array as $domain ):
    $domain_name = $domain['sld'] . '.' .  $domain ['tld'];
    ?>
    <tr>
        <td><?php echo $domain_name;?></td>
        <td>
            <?php if (! isset($domain['client'])) : ?>
                <div class="alert alert-error">
                    <p>
                        Not Found <a class="btn btn-primary create_order"
                            data-domain="<?php echo $domain_name;?>"
                            href="#">Create Order</a>
                    </p>
                    <p>
                        <div class="domain_whois" data-action="get_domain_whois" data-domain="<?php echo $domain_name; ?>">
                            <div class="response"></div>
                            <div class="enom_pro_loader"></div>
                        </div>
                    </p>
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    <p>Associated with client:
                    <a class="btn"
                        data-domain="<?php echo $domain_name;?>"
                        href="clientsdomains.php?userid=<?php echo $domain['client']['userid'];?>&domainid=<?php echo $domain['id'];?>">
                        <?php echo $domain['client']['firstname'] . ' ' . $domain['client']['lastname'];?>
                        </a>
                    </p>
                </div> 
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<ul class="pager">
    <?php $prev_start = isset($_REQUEST['start']) ? ($_REQUEST['start'] - $per_page < 0 ? '0' : $_REQUEST['start'] - $per_page) : 0; ?>
    <?php if ($prev_start >= 1) :?>
        <li class="previous">
            <a data-start="<?php echo $prev_start;?>"
                href="<?php echo enom_pro::MODULE_LINK; ?>&view=import&start=<?php echo $prev_start;?>#import_table">&larr;
                Previous
            </a>
        </li>
    <?php endif;?>
    <?php $next_start = isset($_REQUEST['start']) ? ($_REQUEST['start'] + $per_page) : $per_page; ?>
    <?php if ($next_start <= $list_meta['total_domains']) :?>
        <li class="next">
            <a data-start="<?php echo $next_start;?>"
               href="<?php echo enom_pro::MODULE_LINK; ?>&view=import&start=<?php echo $next_start;?>#import_table">Next 
               &rarr;</a>
       </li>
    <?php endif; ?>
</ul>
<div class="clearfix">
    <span class="floatleft">
        Page <?php echo ceil( $_GET['start'] / $enom->get_addon_setting('import_per_page'));?>
    </span>
    <span class="floatright">
        <?php echo $list_meta['total_domains']?> Total domains
    </span>
</div>