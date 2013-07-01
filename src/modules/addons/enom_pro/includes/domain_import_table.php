<?php 
$enom = new enom_pro();
$domains_array = $enom->getDomains($enom->get_addon_setting('import_per_page'), (int) $_GET['start']);
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
        <td><?php
        $whmcs_response = localapi('getclientsdomains', array('domain' => $domain_name ));
                    if ($whmcs_response['totalresults'] == 0) : ?>
            <div class="alert alert-error">
                <p>
                    Not Found <a class="btn btn-primary create_order"
                        data-domain="<?php echo $domain_name;?>" href="#">Create Order</a>
                </p>
            </div> <?php elseif ($whmcs_response['totalresults'] == 1):
            $domain = $whmcs_response['domains']['domain'][0];
            $client = localapi('getclientsdetails', array('clientid'=> $domain['userid']));
            ?>
            <div class="alert alert-success">
                <p>
                    Associated with client: <a class="btn"
                        data-domain="<?php echo $domain_name;?>"
                        href="clientsdomains.php?userid=<?php echo $domain['userid'];?>&domainid=<?php echo $domain['id'];?>"><?php echo $client['firstname'] . ' ' . $client['lastname'];?>
                    </a>
                </p>
            </div> <?php else: ?>
            <div class="alert alert-error">Uh oh. This domain is appears to be
                associated with more than 1 account in WHMCS. Here is the raw
                response data from whmcs:</div> <pre class="code">
                            <?php print_r($whmcs_response['domains']['domain'])?>
                        </pre> <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<ul class="pager">
    <?php if ($list_meta['prev_start'] !== 0) :?>
    <li class="previous"><a
        href="<?php echo enom_pro::MODULE_LINK; ?>&view=import&start=<?php echo $list_meta['prev_start'];?>#import_table">&larr;
            Previous</a></li>
    <?php endif;?>
    <?php if ($list_meta['next_start'] !== 0) :?>
    <li class="next"><a
        href="<?php echo enom_pro::MODULE_LINK; ?>&view=import&start=<?php echo $list_meta['next_start'];?>#import_table">Next
            &rarr;</a></li>
    <?php endif;?>
</ul>
<li style="text-align: right"><p>
        <?php echo $list_meta['total_domains']?>
        Total domains
    </p></li>