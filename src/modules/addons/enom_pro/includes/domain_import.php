<?php
/**
 * @var $this enom_pro
 */
if ($this->get_addon_setting('import_per_page')) {
            //api is limited to 0 -> 100 domains
            //@props www.EXTREMESHOK.com for the bugfix
            if ($this->get_addon_setting('import_per_page') < 100) {
                $this->setParams( array('Display'=>$this->get_addon_setting('import_per_page') ) );
            } else {
                $this->setParams( array('Display'=> '25' ) );
            }
        } else {
            $this->setParams( array('Display'=> '25' ) );
        }
        if (isset($_GET['start'])) {
            $this->setParams( array('Start' => (int) $_GET['start']) );
        } else {
            $this->setParams( array('Start' => '1' ) );
        }
        $this->runTransaction('GetDomains');
        if ( $this->error && ! $this->debug()) {
            echo $this->errorMessage;
        }
        $xml = $this->xml;
        $list_meta = array(
                'total_domains' => (int) $xml->GetDomains->TotalDomainCount,
                'next_start' => (int) $xml->GetDomains->NextRecords,
                'prev_start' => (int) $xml->GetDomains->PreviousRecords,
        );
        $domains_list = $xml->GetDomains->{"domain-list"};
        $domains_array = array();
        foreach ($domains_list->domain as $domain) {
            $domains_array[] = (array) $domain;
        }
        if ( empty($domains_array) ) {
            echo '<div class="alert alert-error"><p>No domains returned from eNom.</p></div>';

            return;
        }
        ?>
<script
    src="../modules/addons/enom_pro/jquery.admin.js"></script>
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
<div id="create_order_dialog" title="Create Order">
    <form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>"
        id="create_order_form">
        <div id="ajax_messages" class="alert" style="display: none;"></div>
        <div class="enom_pro_loader" style="display: none;"></div>
        <div id="order_process">
            <input type="hidden" name="action" value="add_enom_pro_domain_order" />
            <input type="hidden" name="domaintype" value="register" /> <input
                type="hidden" name="domain" value="" id="domain_field2" /><br /> <input
                type="text" name="domain_display" value="" id="domain_field"
                disabled="disabled" readonly="readonly" size="60" /> <br />
            <?php $clients = localapi('getclients', array('limitnum' => 9000000 ));
            if ('success' == $clients['result']):
                $clients_array = $clients['clients']['client']; ?>
            <label for="client_select">Client</label> <select name="clientid"
                id="client_select">
                <?php
                foreach ($clients_array as $client) {
                            echo '<option value="'.$client['id'].'">'.$client['firstname'] . ' ' . $client['lastname'] . (! empty($client['companyname']) ? ' ('.$client['companyname'].')' : '') . '</option>';
                        }
                        ?>
            </select>
            <?php else :?>
            <div class="alert alert-error">
                WHMCS API Error:
                <?php echo '<pre>';
                print_r($clients);
                            echo '</pre>';?>
            </div>
            <?php endif;?>
            <label for="register_years">Years</label> <select name="regperiod"
                id="register_years">
                <?php for ($i = 1; $i <= 10; $i++) {
                    echo '<option value="'.$i.'">'.$i.'</option>';
                  }?>
            </select>
            <table style="width: 100%">
                <tr>
                    <td><label for="dnsmanagement" class="btn btn-mini">DNS Management</label>
                        <input type="checkbox" name="dnsmanagement" id="dnsmanagement" />
                    </td>
                    <td style="width: 50%"><label for="idprotection"
                        class="btn btn-mini">ID Protect</label> <input type="checkbox"
                        name="idprotection" id="idprotection" />
                    </td>
                </tr>
                <tr>
                    <td><label for="orderemail" class="btn btn-mini">Send order
                            confirmation email</label> <input type="checkbox" name="noemail"
                        id="orderemail" />
                    </td>
                    <td><label for="generateinvoice" class="btn btn-mini">Generate
                            Invoice</label> <input type="checkbox" name="noinvoice"
                        id="generateinvoice" />
                    </td>
                </tr>
                <tr>
                    <td><label for="payment_gateway">Payment gateway</label> <select
                        name="paymentmethod" id="payment_gateway">
                            <?php $methods = localapi('getpaymentmethods');
                            foreach ($methods['paymentmethods']['paymentmethod'] as $gateway) {
                                echo '<option value="'.$gateway['module'].'">'.$gateway['displayname'].'</option>';
                            }
                            ?>
                    </select>
                    </td>
                    <td>
                        <div id="invoice_email" style="display: none;">
                            <label for="noinvoiceemail" class="btn btn-mini">Send Invoice
                                Notification Email</label> <input type="checkbox"
                                name="noinvoiceemail" id="noinvoiceemail" /><br />
                        </div>
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><input type="submit" value="Create Order"
                        class="btn btn-success btn-block aligncenter" />
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>
