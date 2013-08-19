<?php
/**
 * @var $this enom_pro
 */
?>
<div id="enom_pro_import_page">
<?php if (isset($_GET['cleared'])) : ?>
    <div class="alert alert-info slideup">
        <h3>Cache Cleared</h3>
    </div>
<?php endif; ?>
    <script src="../modules/addons/enom_pro/jquery.admin.js"></script>
    <div class="enom_pro_loader" id="top_loader"></div>
     <table id="meta">
        <tr>
            <td>
                <form method="GET" action="<?php echo $_SERVER['PHP_SELF'];?>" id="filter_form">
                <?php $options = array('All', 'Imported', 'Unimported'); ?>
                    <label for="filter">Filter</label>
                    <select name="show_only" id="filter">
                        <?php foreach ($options as $option) :?>
                            <option value="<?php echo strtolower($option);?>"
                                <?php if (isset($_GET['show_only']) && $_GET['show_only'] == strtolower($option)):?> selected<?php endif?>>
                                <?php echo $option; ?>
                            </option>
                        <?php endforeach;?>
                    </select>
                    <input type="hidden" name="module" value="enom_pro" />
                    <input type="hidden" name="view" value="import" />
                    <input type="submit" value="Go" class="no-js" />
                </form>
            </td>
            <td>
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>" id="per_page_form">
                    <?php 
                    $config = enom_pro_config();
                    $options = $config['fields'];
                    $per_page = explode(',', $options['import_per_page']['Options']);
                    ?>
                    <label for="per_page">Per Page</label>
                    <select name="per_page" id="per_page">
                        <?php foreach ($per_page as $num) :?>
                            <option value="<?php echo $num?>"
                                <?php if (enom_pro::get_addon_setting('import_per_page') == $num):?> selected<?php endif?>>
                                <?php echo $num?>
                            </option>
                        <?php endforeach;?>
                    </select>
                    <input type="hidden" name="action" value="set_results_per_page" />
                    <input type="submit" value="Go" class="no-js" />
                </form>
            </td>
            <td>
                <form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>" id="search_form">
                    <input type="text" name="s" placeholder="Search"/>
                    <input type="submit" value="Go" />
                </form>
            </td>
        </tr>
    </table>
    <form method="POST" id="import_table_form">
        <input type="hidden" name="action" value="render_import_table" />
        <input type="hidden" name="start" value="1" />
        <input type="hidden" name="s" value="" />
        <input type="hidden" name="per_page" value="<?php echo enom_pro::get_addon_setting('import_per_page')?>" />
        <input type="hidden" name="show_only" value="<?php if (in_array($_GET['show_only'], array('imported', 'unimported'))): echo $_GET['show_only']; endif;?>" />
        <?php if (isset($_GET['domain'])) :?>
            <input type="hidden" name="domain" value="<?php echo htmlentities($_GET['domain']);?>" />
        <?php endif;?>
        <div id="domains_target">
        </div>
    </form>
    <div id="create_order_dialog" title="Create Order">
        <form method="POST" action="<?php echo $_SERVER['PHP_SELF'];?>"
            id="create_order_form">
            <div id="ajax_messages" class="alert" style="display: none;"></div>
            <div class="enom_pro_loader" style="display: none;"></div>
            <div id="order_process">
            <div class="alert alert-warning hidden" id="auto-renew-warning">
                <p>     Auto-Renew is enabled for this domain. 
                        Make sure to disable it after it has been imported into WHMCS,
                        to avoid double billing. 
                </p>
            </div>
                <input type="hidden" name="action" value="add_enom_pro_domain_order" />
                <input type="hidden" name="domaintype" value="register" /> <input
                    type="hidden" name="domain" value="" id="domain_field2" /><br />
                <input type="text" name="domain_display" value="" id="domain_field"
                    disabled="disabled" readonly="readonly" size="60" /> <br />
                <?php $clients = enom_pro::whmcs_api('getclients', array());
                if ('success' == $clients['result']):
                    $clients_array = $clients['clients']['client']; ?>
                <label for="client_select">Client</label> <select name="clientid"
                    id="client_select">
                    <?php
                    foreach ($clients_array as $client) {
                                echo '<option data-email="'.$client['email'].'" value="'.$client['id'].'">'.$client['firstname'] . ' ' . $client['lastname'] . (! empty($client['companyname']) ? ' ('.$client['companyname'].')' : '') . '</option>';
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
    <table id="domain_caches">
        <tr>
            <td>
                <a class="btn btn-inverse btn-mini btn-block" href="addonmodules.php?module=enom_pro&action=clear_cache">Clear Cache</a><br/>
                Domains Cached from <span class="domains_cache_time"><?php echo $this->get_domain_cache_date(); ?></span>
            </td>
            <td id="local_storage">
            </td>
        </tr>
    </table>
    <div class="enom_pro_loader hidden" id="loader_bottom"></div>
</div>