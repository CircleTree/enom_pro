<?php 
if (empty($domains)) {
    return false;
}
/**
 * @var $domains array 
 */
$first_page = ! isset($_GET['start']) ? true : false;
if ($first_page):
?>
<table class="table-hover domain-widget-response">
    <thead>
        <tr>
            <th>domain</th>
            <th>expire<?php echo $tab == 'IOwn' ? 's' : 'd';?></th>
            <th>enom_dns</th>
            <th>privacy</th>
            <th>autorenew</th>
            <th>user</th>
            <th>domain</th>
        </tr>
    </thead>
    <tbody>
<?php endif;?>
        <?php foreach ($domains as $key => $domain): ?>
            <tr>
                <td><?php echo $domain['sld'] . '.' . $domain['tld'];?></td>
                <td>
                <?php $expires = new DateTime($domain['expiration']);
                $now = new DateTime('now');
                $diff = $now->diff($expires);
                echo $diff->format('%R%a days');
                ?>
                </td>
                <td><span class="badge <?php echo ($domain['enom_dns'] == 1) ? 'badge-success' : 'badge-important' ?>">
                        <?php echo ($domain['enom_dns'] == 1) ? 'On' : 'Off'; ?>
                    </span>
                </td>
                <td>
                    <span class="badge <?php echo ($domain['privacy'] == 1) ? 'badge-success' : 'badge-important' ?>">
                        <?php echo ($domain['privacy'] == 1) ? 'On' : 'Off'; ?>
                    </span>
                </td>
                <td>
                    <span class="badge <?php echo ($domain['autorenew'] == 1) ? 'badge-success' : 'badge-important' ?>">
                        <?php echo ($domain['autorenew'] == 1) ? 'On' : 'Off'; ?>
                    </span>
                </td>
                <td>
                    <?php if (isset($domain['userid'])) :?>
                        <a class="btn" href="clientssummary.php?userid=<?php echo $domain['userid'];?>">User</a>
                    <?php endif;?>
                </td>
                <td>
                    <?php if (isset($domain['domainid'])) :?>
                        <a class="btn" href="clientsdomains.php?id=<?php echo $domain['domainid'];?>">Domain</a>
                    <?php else:?>
                        <a class="btn" href="addonmodules.php?module=enom_pro&view=import&domain=<?php echo urlencode($domain['sld'].'.'.$domain['tld']);?>">Import</a>
                    <?php endif;?>
                </td>
            </tr>
            <?php if ($key == (count($domains) - 1)) : ?>
            <tr>
                <td colspan="7">
                    <a class="btn btn-block btn-mini load_more"
                    href="<?php echo enom_pro::MODULE_LINK.'&action=get_domains' . ( isset($_GET['tab']) ? '&tab='.$_GET['tab'] : '')
                    . '&start='.(count($domains) + 1); ?>">Load More</a>
                    <div class="enom_pro_loader small hidden"></div>
                </td>
            </tr>
            <?php endif; ?>
        <?php endforeach; ?>
        <?php if ($first_page) :?>
    </tbody>
</table>
<?php endif;?>
<script>
jQuery(function($) {
    $(".load_more").on('click', function  () {
        var $button = $(this),
        $row = $button.closest('tr');
        $.get($(this).attr('href'), function  (data) {
            $(".domain-widget-response tbody").append(data);
            $button.add($row).hide(); 
        });
        return false;
    });
});
</script>