<div id="clientsummarycontainer">
  <div class="clientsummaryactions"> {$_ADMINLANG.clientsummary.settingtaxexempt}: <span id="taxstatus" class="csajaxtoggle" style="cursor:pointer"><strong class="{if $clientsdetails.taxstatus == "Yes"}textgreen{else}textred{/if}">{$clientsdetails.taxstatus}</strong></span> &nbsp;&nbsp;
    {$_ADMINLANG.clientsummary.settingautocc}: <span id="autocc" class="csajaxtoggle" style="cursor:pointer"><strong class="{if $clientsdetails.autocc == "Yes"}textgreen{else}textred{/if}">{$clientsdetails.autocc}</strong></span> &nbsp;&nbsp;
    {$_ADMINLANG.clientsummary.settingreminders}: <span id="overduenotices" class="csajaxtoggle" style="cursor:pointer"><strong class="{if $clientsdetails.overduenotices == "Yes"}textgreen{else}textred{/if}">{$clientsdetails.overduenotices}</strong></span> &nbsp;&nbsp;
    {$_ADMINLANG.clientsummary.settinglatefees}: <span id="latefees" class="csajaxtoggle" style="cursor:pointer"><strong class="{if $clientsdetails.latefees == "Yes"}textgreen{else}textred{/if}">{$clientsdetails.latefees}</strong></span> </div>
  <h3>#<span id="userId">{$clientsdetails.userid}</span> - {$clientsdetails.firstname} {$clientsdetails.lastname}</h3>
  <div class="clear"></div>
  {if $notes} <br />
  <div class="panel" id="clientsimportantnotes"> {foreach from=$notes item=note}
    <div class="ticketstaffnotes">
      <div><strong>{$note.adminuser}</strong> | {$note.modified}</div>
      <div>{$note.note}</div>
      <div class="text-right"><a class="button small" href="clientsnotes.php?userid={$clientsdetails.userid}&action=edit&id={$note.id}"><i class="fa fa-pencil-square-o"></i> Edit</a></div>
    </div>
    {/foreach} </div>
  {/if}
  
  {foreach from=$addons_html item=addon_html}
  <div style="margin-top:10px;">{$addon_html}</div>
  {/foreach}
  <div class="row">
    <div class="small-12 large-3 columns">
      <div class="clientssummarybox">
        <div class="title">{$_ADMINLANG.clientsummary.infoheading}</div>
        <table class="clientssummarystats">
          <tr>
            <td>{$_ADMINLANG.fields.firstname}</td>
            <td>{$clientsdetails.firstname}</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.fields.lastname}</td>
            <td>{$clientsdetails.lastname}</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.fields.companyname}</td>
            <td>{$clientsdetails.companyname}</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.fields.email}</td>
            <td>{$clientsdetails.email}</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.fields.address1}</td>
            <td>{$clientsdetails.address1}</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.fields.address2}</td>
            <td>{$clientsdetails.address2}</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.fields.city}</td>
            <td>{$clientsdetails.city}</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.fields.state}</td>
            <td>{$clientsdetails.state}</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.fields.postcode}</td>
            <td>{$clientsdetails.postcode}</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.fields.country}</td>
            <td>{$clientsdetails.country} - {$clientsdetails.countrylong}</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.fields.phonenumber}</td>
            <td>{$clientsdetails.phonenumber}</td>
          </tr>
        </table>
        <ul>
          <li><a id="summary-reset-password" href="clientssummary.php?userid={$clientsdetails.userid}&resetpw=true&token={$csrfToken}"><i class="fa fa-key"></i> {$_ADMINLANG.clients.resetsendpassword}</a>
          <li><a id="summary-cccard-details" href="#" onClick="openCCDetails();return false"><i class="fa fa-credit-card"></i> {$_ADMINLANG.clientsummary.ccinfo}</a>
          <li><a id="summary-login-as-client" href="../dologin.php?username={$clientsdetails.email|urlencode}"><i class="fa fa-sign-in"></i> {$_ADMINLANG.clientsummary.loginasclient}</a>
        </ul>
      </div>
      <div class="clientssummarybox">
        <div class="title">{$_ADMINLANG.clientsummary.contactsheading}</div>
        <table class="clientssummarystats">
          {foreach key=num from=$contacts item=contact}
          <tr class="{cycle values=",altrow"}">
            <td class="text-center"><a href="clientscontacts.php?userid={$clientsdetails.userid}&contactid={$contact.id}">{$contact.firstname} {$contact.lastname}</a> - {$contact.email}</td>
          </tr>
          {foreachelse}
          <tr>
            <td class="text-center">{$_ADMINLANG.clientsummary.nocontacts}</td>
          </tr>
          {/foreach}
        </table>
        <ul>
          <li><a href="clientscontacts.php?userid={$clientsdetails.userid}&contactid=addnew"><i class="fa fa-users"></i> {$_ADMINLANG.clients.addcontact}</a>
        </ul>
      </div>
    </div>
    <div class="small-12 large-3 columns">
      <div class="clientssummarybox">
        <div class="title">{$_ADMINLANG.clientsummary.billingheading}</div>
        <table class="clientssummarystats">
          <tr>
            <td>{$_ADMINLANG.status.paid}</td>
            <td>{$stats.numpaidinvoices} ({$stats.paidinvoicesamount})</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.status.unpaid}/{$_ADMINLANG.status.due}</td>
            <td>{$stats.numdueinvoices} ({$stats.dueinvoicesbalance})</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.status.cancelled}</td>
            <td>{$stats.numcancelledinvoices} ({$stats.cancelledinvoicesamount})</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.status.refunded}</td>
            <td>{$stats.numrefundedinvoices} ({$stats.refundedinvoicesamount})</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.status.collections}</td>
            <td>{$stats.numcollectionsinvoices} ({$stats.collectionsinvoicesamount})</td>
          </tr>
          <tr>
            <td><strong>{$_ADMINLANG.billing.income}</strong></td>
            <td><strong>{$stats.income}</strong></td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.clients.creditbalance}</td>
            <td>{$stats.creditbalance}</td>
          </tr>
        </table>
        <ul>
          <li><a href="invoices.php?action=createinvoice&userid={$clientsdetails.userid}&token={$csrfToken}"><i class="fa fa-calculator"></i> {$_ADMINLANG.invoices.create}</a>
          <li><a href="#" onClick="showDialog('addfunds');return false"><i class="fa fa-money"></i> {$_ADMINLANG.clientsummary.createaddfunds}</a>
          <li><a href="#" onClick="showDialog('geninvoices');return false"><i class="fa fa-file-pdf-o"></i> {$_ADMINLANG.invoices.geninvoices}</a>
          <li><a href="clientsbillableitems.php?userid={$clientsdetails.userid}&action=manage"><i class="fa fa-clock-o"></i> {$_ADMINLANG.billableitems.additem}</a>
          <li><a href="#" onClick="window.open('clientscredits.php?userid={$clientsdetails.userid}','','width=750,height=350,scrollbars=yes');return false"><i class="fa fa-usd"></i> {$_ADMINLANG.clientsummary.managecredits}</a>
          <li><a href="quotes.php?action=manage&userid={$clientsdetails.userid}"><i class="fa fa-quote-left"></i> {$_ADMINLANG.quotes.createnew}</a>
        </ul>
      </div>
      <div class="clientssummarybox">
        <div class="title">{$_ADMINLANG.clientsummary.otherinfoheading}</div>
        <table class="clientssummarystats">
          <tr>
            <td>{$_ADMINLANG.fields.status}</td>
            <td>{$clientsdetails.status}</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.fields.clientgroup}</td>
            <td>{$clientgroup.name}</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.fields.signupdate}</td>
            <td>{$signupdate}</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.clientsummary.clientfor}</td>
            <td>{$clientfor}</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.clientsummary.lastlogin}</td>
            <td>{$lastlogin}</td>
          </tr>
        </table>
      </div>
    </div>
    <div class="small-12 large-3 columns">
      <div class="clientssummarybox">
        <div class="title">{$_ADMINLANG.services.title}</div>
        <table class="clientssummarystats">
          <tr>
            <td width="140">{$_ADMINLANG.orders.sharedhosting}</td>
            <td>{$stats.productsnumactivehosting} ({$stats.productsnumhosting} Total)</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.orders.resellerhosting}</td>
            <td>{$stats.productsnumactivereseller} ({$stats.productsnumreseller} Total)</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.orders.server}</td>
            <td>{$stats.productsnumactiveservers} ({$stats.productsnumservers} Total)</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.orders.other}</td>
            <td>{$stats.productsnumactiveother} ({$stats.productsnumother} Total)</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.domains.title}</td>
            <td>{$stats.numactivedomains} ({$stats.numdomains} Total)</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.stats.acceptedquotes}</td>
            <td>{$stats.numacceptedquotes} ({$stats.numquotes} Total)</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.support.supporttickets}</td>
            <td>{$stats.numactivetickets} ({$stats.numtickets} Total)</td>
          </tr>
          <tr>
            <td>{$_ADMINLANG.stats.affiliatesignups}</td>
            <td>{$stats.numaffiliatesignups}</td>
          </tr>
        </table>
        <ul>
          <li><a href="orders.php?clientid={$clientsdetails.userid}"><i class="fa fa-shopping-cart"></i> {$_ADMINLANG.clientsummary.vieworders}</a>
          <li><a href="ordersadd.php?userid={$clientsdetails.userid}"><i class="fa fa-plus-circle"></i> {$_ADMINLANG.orders.addnew}</a>
        </ul>
      </div>
      <div class="clientssummarybox">
        <div class="title">{$_ADMINLANG.clientsummary.filesheading}</div>
        <table class="clientssummarystats">
          {foreach key=num from=$files item=file}
          <tr class="{cycle values=",altrow"}">
            <td class="text-center"><a href="../dl.php?type=f&id={$file.id}"><i class="fa fa-file-image-o"></i> {$file.title}</a> {if $file.adminonly}({$_ADMINLANG.clientsummary.fileadminonly}){/if} <i class="fa fa-times" onClick="deleteFile('{$file.id}')"></i></td>
          </tr>
          {foreachelse}
          <tr>
            <td class="text-center">{$_ADMINLANG.clientsummary.nofiles}</td>
          </tr>
          {/foreach}
        </table>
        <ul>
          <li><a href="#" id="addfile"><i class="fa fa-plus-circle"></i> {$_ADMINLANG.clientsummary.fileadd}</a>
        </ul>
        <div id="addfileform" style="display:none;">
          <form method="post" action="clientssummary.php?userid={$clientsdetails.userid}&action=uploadfile" enctype="multipart/form-data">
            <table class="clientssummarystats">
              <tr>
                <td width="40">{$_ADMINLANG.clientsummary.filetitle}</td>
                <td class="fieldarea"><input type="text" name="title" style="width:90%" /></td>
              </tr>
              <tr>
                <td>{$_ADMINLANG.clientsummary.filename}</td>
                <td class="fieldarea"><input type="file" name="uploadfile" style="width:90%" /></td>
              </tr>
              <tr>
                <td></td>
                <td class="fieldarea"><input type="checkbox" name="adminonly" value="1" />
                  {$_ADMINLANG.clientsummary.fileadminonly} &nbsp;&nbsp;&nbsp;&nbsp;
                  <input class="button small" type="submit" value="{$_ADMINLANG.global.submit}" /></td>
              </tr>
            </table>
          </form>
        </div>
      </div>
      <div class="clientssummarybox">
        <div class="title">{$_ADMINLANG.clientsummary.emailsheading}</div>
        <table class="clientssummarystats">
          {foreach key=num from=$lastfivemail item=email}
          <tr class="{cycle values=",altrow"}">
            <td class="text-center">{$email.date} - <a href="#" onClick="window.open('clientsemails.php?&displaymessage=true&id={$email.id}','','width=650,height=400,scrollbars=yes');return false">{$email.subject}</a></td>
          </tr>
          {foreachelse}
          <tr>
            <td class="text-center">{$_ADMINLANG.clientsummary.noemails}</td>
          </tr>
          {/foreach}
        </table>
      </div>
    </div>
    <div class="small-12 large-3 columns">
      <div class="clientssummarybox">
        <div class="title">{$_ADMINLANG.clientsummary.actionsheading}</div>
        <ul>
          {foreach from=$customactionlinks item=customactionlink}
          <li>{$customactionlink}</li>
          {/foreach}
          <li><a href="reports.php?report=client_statement&userid={$clientsdetails.userid}"><i class="fa fa-pie-chart"></i> {$_ADMINLANG.clientsummary.accountstatement}</a>
          <li><a href="supporttickets.php?action=open&userid={$clientsdetails.userid}"><i class="fa fa-plus-circle"></i> {$_ADMINLANG.clientsummary.newticket}</a>
          <li><a href="supporttickets.php?view=any&client={$clientsdetails.userid}"><i class="fa fa-ticket"></i> {$_ADMINLANG.clientsummary.viewtickets}</a>
          <li><a href="{if $affiliateid}affiliates.php?action=edit&id={$affiliateid}{else}clientssummary.php?userid={$clientsdetails.userid}&activateaffiliate=true&token={$csrfToken}{/if}"><i class="fa fa-users"></i> {if $affiliateid}{$_ADMINLANG.clientsummary.viewaffiliate}{else}{$_ADMINLANG.clientsummary.activateaffiliate}{/if}</a>
          <li><a href="#" onClick="window.open('clientsmerge.php?userid={$clientsdetails.userid}','movewindow','width=500,height=280,top=100,left=100,scrollbars=1');return false"><i class="fa fa-male"></i> {$_ADMINLANG.clientsummary.mergeclients}</a>
          <li><a href="#" onClick="closeClient();return false" class="textblack"><i class="fa fa-times"></i> {$_ADMINLANG.clientsummary.closeclient}</a>
          <li><a href="#" onClick="deleteClient();return false" class="textred"><i class="fa fa-trash"></i> {$_ADMINLANG.clientsummary.deleteclient}</a>
        </ul>
      </div>
      <div class="clientssummarybox">
        <div class="title">{$_ADMINLANG.clientsummary.sendemailheading}</div>
        <form action="clientsemails.php?userid={$clientsdetails.userid}&action=send&type=general" method="post">
          <input type="hidden" name="id" value="{$clientsdetails.userid}">
          <div class="text-center">{$messages}
            <input type="submit" value="{$_ADMINLANG.global.go}" class="button small">
          </div>
        </form>
      </div>
      <div class="clientssummarybox">
        <div class="title">{$_ADMINLANG.fields.adminnotes}</div>
        <form method="post" action="{$smarty.server.PHP_SELF}?userid={$clientsdetails.userid}&action=savenotes">
          <div class="text-center">
            <textarea name="adminnotes" rows="6" style="width:90%;" />
            {$clientsdetails.notes}
            </textarea>
            <br />
            <input type="submit" value="{$_ADMINLANG.global.submit}" class="button small" />
          </div>
        </form>
      </div>
    </div>
  </div>
  <p class="text-right">
    <input type="button" value="{$_ADMINLANG.clientsummary.statusfilter}: {if $statusfilterenabled}{$_ADMINLANG.global.on}{else}{$_ADMINLANG.global.off}{/if}" class="btn-small{if $statusfilterenabled} btn-success{/if}" onclick="toggleStatusFilter()" />
  </p>
  <div id="statusfilter">
    <form>
      <div class="checkall">
        <label>
          <input type="checkbox" id="statusfiltercheckall" onclick="checkAllStatusFilter()"{if !$statusfilterenabled} checked{/if} />
          {$_ADMINLANG.global.checkall}</label>
      </div>
      <table class="datatable" width="100%">
        {foreach from=$itemstatuses key=itemstatus item=statuslang}
        <tr>
          <td><label style="display:block;">
              <input type="checkbox" name="statusfilter[]" value="{$itemstatus}" onclick="uncheckCheckAllStatusFilter()"{if !in_array($itemstatus, $disabledstatuses)} checked{/if} />
              {$statuslang}</label></td>
        </tr>
        {/foreach}
      </table>
      <div class="text-center">
        <input type="button" value="{$_ADMINLANG.global.apply}" class="button small success" onclick="applyStatusFilter()" />
      </div>
    </form>
  </div>
  <form method="post" action="{$smarty.server.PHP_SELF}?userid={$clientsdetails.userid}&action=massaction">
    {literal}<script>
$(document).ready(function(){
    $("#prodsall").click(function () {
        $(".checkprods").attr("checked",this.checked);
    });
    $("#addonsall").click(function () {
        $(".checkaddons").attr("checked",this.checked);
    });
    $("#domainsall").click(function () {
        $(".checkdomains").attr("checked",this.checked);
    });
});
</script>{/literal}
    <h2>{$_ADMINLANG.services.title}</h2>
    <table class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr>
        <th width="20"><input type="checkbox" id="prodsall" /></th>
        <th>{$_ADMINLANG.fields.id}</th>
        <th>{$_ADMINLANG.fields.product}</th>
        <th>{$_ADMINLANG.fields.amount}</th>
        <th>{$_ADMINLANG.fields.billingcycle}</th>
        <th>{$_ADMINLANG.fields.signupdate}</th>
        <th>{$_ADMINLANG.fields.nextduedate}</th>
        <th>{$_ADMINLANG.fields.status}</th>
        <th width="20"></th>
      </tr>
      {foreach key=num from=$productsummary item=product}
      <tr>
        <td><input type="checkbox" name="selproducts[]" value="{$product.id}" class="checkprods" /></td>
        <td><a href="clientsservices.php?userid={$clientsdetails.userid}&id={$product.id}">{$product.idshort}</a></td>
        <td style="padding-left:5px;padding-right:5px">{$product.dpackage} - <a href="http://{$product.domain}" target="_blank">{$product.domain}</a></td>
        <td>{$product.amount}</td>
        <td>{$product.dbillingcycle}</td>
        <td>{$product.regdate}</td>
        <td>{$product.nextduedate}</td>
        <td>{$product.domainstatus}</td>
        <td><a href="clientsservices.php?userid={$clientsdetails.userid}&id={$product.id}"><img src="images/edit.gif" width="16" height="16" border="0" alt="Edit"></a></td>
      </tr>
      {foreachelse}
      <tr>
        <td colspan="9">{$_ADMINLANG.global.norecordsfound}</td>
      </tr>
      {/foreach}
    </table>
    <h2>{$_ADMINLANG.addons.title}</h2>
    <table class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr>
        <th width="20"><input type="checkbox" id="addonsall" /></th>
        <th>ID</th>
        <th>{$_ADMINLANG.addons.name}</th>
        <th>{$_ADMINLANG.fields.amount}</th>
        <th>{$_ADMINLANG.fields.billingcycle}</th>
        <th>{$_ADMINLANG.fields.signupdate}</th>
        <th>{$_ADMINLANG.fields.nextduedate}</th>
        <th>{$_ADMINLANG.fields.status}</th>
        <th width="20"></th>
      </tr>
      {foreach key=num from=$addonsummary item=addon}
      <tr>
        <td><input type="checkbox" name="seladdons[]" value="{$addon.id}" class="checkaddons" /></td>
        <td><a href="clientsservices.php?userid={$clientsdetails.userid}&id={$addon.serviceid}&aid={$addon.id}">{$addon.idshort}</a></td>
        <td style="padding-left:5px;padding-right:5px">{$addon.addonname}<br>
          {$addon.dpackage} - <a href="http://{$addon.domain}" target="_blank">{$addon.domain}</a></td>
        <td>{$addon.amount}</td>
        <td>{$addon.dbillingcycle}</td>
        <td>{$addon.regdate}</td>
        <td>{$addon.nextduedate}</td>
        <td>{$addon.status}</td>
        <td><a href="clientsservices.php?userid={$clientsdetails.userid}&id={$addon.serviceid}&aid={$addon.id}"><img src="images/edit.gif" width="16" height="16" border="0" alt="Edit"></a></td>
      </tr>
      {foreachelse}
      <tr>
        <td colspan="9">{$_ADMINLANG.global.norecordsfound}</td>
      </tr>
      {/foreach}
    </table>
    <h2>{$_ADMINLANG.domains.title}</h2>
    <table class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr>
        <th width="20"><input type="checkbox" id="domainsall" /></th>
        <th>{$_ADMINLANG.fields.id}</th>
        <th>{$_ADMINLANG.fields.domain}</th>
        <th>{$_ADMINLANG.fields.registrar}</th>
        <th>{$_ADMINLANG.fields.regdate}</th>
        <th>{$_ADMINLANG.fields.nextduedate}</th>
        <th>{$_ADMINLANG.fields.expirydate}</th>
        <th>{$_ADMINLANG.fields.status}</th>
        <th width="20"></th>
      </tr>
      {foreach key=num from=$domainsummary item=domain}
      <tr>
        <td><input type="checkbox" name="seldomains[]" value="{$domain.id}" class="checkdomains" /></td>
        <td><a href="clientsdomains.php?userid={$clientsdetails.userid}&domainid={$domain.id}">{$domain.idshort}</a></td>
        <td style="padding-left:5px;padding-right:5px"><a href="http://{$domain.domain}" target="_blank">{$domain.domain}</a></td>
        <td>{$domain.registrar}</td>
        <td>{$domain.registrationdate}</td>
        <td>{$domain.nextduedate}</td>
        <td>{$domain.expirydate}</td>
        <td>{$domain.status}</td>
        <td><a href="clientsdomains.php?userid={$clientsdetails.userid}&domainid={$domain.id}"><img src="images/edit.gif" width="16" height="16" border="0" alt="Edit"></a></td>
      </tr>
      {foreachelse}
      <tr>
        <td colspan="9">{$_ADMINLANG.global.norecordsfound}</td>
      </tr>
      {/foreach}
    </table>
    <h2>{$_ADMINLANG.clientsummary.currentquotes}</h2>
    <table class="datatable" width="100%" border="0" cellspacing="1" cellpadding="3">
      <tr>
        <th>{$_ADMINLANG.fields.id}</th>
        <th>{$_ADMINLANG.fields.subject}</th>
        <th>{$_ADMINLANG.fields.date}</th>
        <th>{$_ADMINLANG.fields.total}</th>
        <th>{$_ADMINLANG.fields.validuntil}</th>
        <th>{$_ADMINLANG.fields.status}</th>
        <th width="20"></th>
      </tr>
      {foreach key=num from=$quotes item=quote}
      <tr>
        <td>{$quote.id}</td>
        <td>{$quote.subject}</td>
        <td>{$quote.datecreated}</td>
        <td>{$quote.total}</td>
        <td>{$quote.validuntil}</td>
        <td>{$quote.stage}</td>
        <td><a href="quotes.php?action=manage&id={$quote.id}"><img src="images/edit.gif" width="16" height="16" border="0" alt="Edit"></a></td>
      </tr>
      {foreachelse}
      <tr>
        <td colspan="7">{$_ADMINLANG.global.norecordsfound}</td>
      </tr>
      {/foreach}
    </table>
    <div class="text-center">
      <input type="button" value="{$_ADMINLANG.clientsummary.massupdateitems}" class="button small" onclick="$('#massupdatebox').slideToggle()" />
      <input type="submit" name="inv" value="{$_ADMINLANG.clientsummary.invoiceselected}" class="button small" />
      <input type="submit" name="del" value="{$_ADMINLANG.clientsummary.deleteselected}" class="button small alert" />
    </div>
    <div id="massupdatebox" style="display:none;">
      <h3>{$_ADMINLANG.clientsummary.massupdateitems}</h3>
      <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
        <tr>
          <td width="15%" class="fieldlabel" nowrap>{$_ADMINLANG.fields.firstpaymentamount}</td>
          <td class="fieldarea"><input type="text" size="20" name="firstpaymentamount" /></td>
          <td width="15%" class="fieldlabel" nowrap>{$_ADMINLANG.fields.recurringamount}</td>
          <td class="fieldarea"><input type="text" size="20" name="recurringamount" /></td>
        </tr>
        <tr>
          <td class="fieldlabel" width="15%">{$_ADMINLANG.fields.nextduedate}</td>
          <td class="fieldarea"><input type="text" size="20" name="nextduedate" class="datepick" />
            &nbsp;&nbsp;
            <input type="checkbox" name="proratabill" id="proratabill" />
            <label for="proratabill">{$_ADMINLANG.clientsummary.createproratainvoice}</label></td>
          <td width="15%" class="fieldlabel">{$_ADMINLANG.fields.billingcycle}</td>
          <td class="fieldarea"><select name="billingcycle">
              <option value="">- {$_ADMINLANG.global.nochange} -</option>
              <option value="Free Account">{$_ADMINLANG.billingcycles.free}</option>
              <option value="One Time">{$_ADMINLANG.billingcycles.onetime}</option>
              <option value="Monthly">{$_ADMINLANG.billingcycles.monthly}</option>
              <option value="Quarterly">{$_ADMINLANG.billingcycles.quarterly}</option>
              <option value="Semi-Annually">{$_ADMINLANG.billingcycles.semiannually}</option>
              <option value="Annually">{$_ADMINLANG.billingcycles.annually}</option>
              <option value="Biennially">{$_ADMINLANG.billingcycles.biennially}</option>
              <option value="Triennially">{$_ADMINLANG.billingcycles.triennially}</option>
            </select></td>
        </tr>
        <tr>
          <td class="fieldlabel" width="15%">{$_ADMINLANG.fields.paymentmethod}</td>
          <td class="fieldarea">{$paymentmethoddropdown}</td>
          <td class="fieldlabel" width="15%">{$_ADMINLANG.fields.status}</td>
          <td class="fieldarea"><select name="status">
              <option value="">- {$_ADMINLANG.global.nochange} -</option>
              <option value="Pending">{$_ADMINLANG.status.pending}</option>
              <option value="Active">{$_ADMINLANG.status.active}</option>
              <option value="Suspended">{$_ADMINLANG.status.suspended}</option>
              <option value="Terminated">{$_ADMINLANG.status.terminated}</option>
              <option value="Cancelled">{$_ADMINLANG.status.cancelled}</option>
              <option value="Fraud">{$_ADMINLANG.status.fraud}</option>
            </select></td>
        </tr>
        <tr>
          <td class="fieldlabel" width="15%">{$_ADMINLANG.services.modulecommands}</td>
          <td class="fieldarea" colspan="3"><input type="submit" name="masscreate" value="{$_ADMINLANG.modulebuttons.create}" class="button small success" />
            <input type="submit" name="masssuspend" value="{$_ADMINLANG.modulebuttons.suspend}" class="button small" />
            <input type="submit" name="massunsuspend" value="{$_ADMINLANG.modulebuttons.unsuspend}" class="button small" />
            <input type="submit" name="massterminate" value="{$_ADMINLANG.modulebuttons.terminate}" class="button small alert" />
            <input type="submit" name="masschangepackage" value="{$_ADMINLANG.modulebuttons.changepackage}" class="button small" />
            <input type="submit" name="masschangepw" value="{$_ADMINLANG.modulebuttons.changepassword}" class="button small" /></td>
        </tr>
        <tr>
          <td class="fieldlabel" width="15%">{$_ADMINLANG.services.overrideautosusp}</td>
          <td class="fieldarea" colspan="3"><input type="checkbox" name="overideautosuspend" id="overridesuspend" />
            <label for="overridesuspend">{$_ADMINLANG.services.nosuspenduntil}</label>
            <input type="text" name="overidesuspenduntil" class="datepick" /></td>
        </tr>
      </table>
      <div class="text-center">
        <input type="submit" name="massupdate" value="{$_ADMINLANG.global.submit}" />
      </div>
    </div>
  </form>
</div>
