{if $sidebar eq "home"}
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-link"></i> {$_ADMINLANG.global.shortcuts}</li>
    <li><a href="clientsadd.php">{$_ADMINLANG.clients.addnew}</a></li>
    <li><a href="ordersadd.php">{$_ADMINLANG.orders.addnew}</a></li>
    <li><a href="quotes.php?action=manage">{$_ADMINLANG.quotes.createnew}</a></li>
    <li><a href="todolist.php">{$_ADMINLANG.utilities.todolistcreatenew}</a></li>
    <li><a href="supporttickets.php?action=open">{$_ADMINLANG.support.opennewticket}</a></li>
    <li><a href="whois.php">{$_ADMINLANG.utilities.whois}</a></li>
    <li><a href="#" onClick="showDialog('geninvoices');return false">{$_ADMINLANG.invoices.geninvoices}</a></li>
    <li><a href="#" onClick="showDialog('cccapture');return false">{$_ADMINLANG.invoices.attemptcccaptures}</a></li>
  </ul>
</div>
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-cog"></i> {$_ADMINLANG.global.systeminfo}</li>
    <li class="text">{$_ADMINLANG.license.regto}: {$licenseinfo.registeredname}<br />
      {$_ADMINLANG.license.type}: {$licenseinfo.productname}<br />
      {$_ADMINLANG.license.expires}: {$licenseinfo.expires}<br />
      {$_ADMINLANG.global.version}: {$licenseinfo.currentversion}{if $licenseinfo.updateavailable}<br />
      <span class="textred"><strong>{$_ADMINLANG.license.updateavailable}</strong></span>{/if}</li>
  </ul>
</div>
{elseif $sidebar eq "clients"}
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-user"></i> {$_ADMINLANG.clients.title}</li>
    <li><a href="clients.php">{$_ADMINLANG.clients.viewsearch}</a></li>
    <li><a href="clientsadd.php">{$_ADMINLANG.clients.addnew}</a></li>
  </ul>
</div>
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-shopping-cart"></i> {$_ADMINLANG.services.title}</li>
    <li><a href="clientshostinglist.php">{$_ADMINLANG.services.listall}</a></li>
    <li><a href="clientshostinglist.php?listtype=hostingaccount">- {$_ADMINLANG.services.listhosting}</a></li>
    <li><a href="clientshostinglist.php?listtype=reselleraccount">- {$_ADMINLANG.services.listreseller}</a></li>
    <li><a href="clientshostinglist.php?listtype=server">- {$_ADMINLANG.services.listservers}</a></li>
    <li><a href="clientshostinglist.php?listtype=other">- {$_ADMINLANG.services.listother}</a></li>
    <li><a href="clientsaddonslist.php">{$_ADMINLANG.services.listaddons}</a></li>
    <li><a href="clientsdomainlist.php">{$_ADMINLANG.services.listdomains}</a></li>
    <li><a href="cancelrequests.php">{$_ADMINLANG.clients.cancelrequests}</a></li>
  </ul>
</div>
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-users"></i> {$_ADMINLANG.affiliates.title}</li>
    <li><a href="affiliates.php">{$_ADMINLANG.affiliates.manage}</a></li>
  </ul>
</div>
{elseif $sidebar eq "orders"}
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-shopping-cart"></i> {$_ADMINLANG.orders.title}</li>
    <li><a href="orders.php">{$_ADMINLANG.orders.listall}</a></li>
    <li><a href="orders.php?status=Pending">- {$_ADMINLANG.orders.listpending}</a></li>
    <li><a href="orders.php?status=Active">- {$_ADMINLANG.orders.listactive}</a></li>
    <li><a href="orders.php?status=Fraud">- {$_ADMINLANG.orders.listfraud}</a></li>
    <li><a href="orders.php?status=Cancelled">- {$_ADMINLANG.orders.listcancelled}</a></li>
    <li><a href="ordersadd.php">{$_ADMINLANG.orders.addnew}</a></li>
  </ul>
</div>
{elseif $sidebar eq "billing"}
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-money"></i> {$_ADMINLANG.billing.title}</li>
    <li><a href="transactions.php">{$_ADMINLANG.billing.transactionslist}</a></li>
    <li><a href="gatewaylog.php">{$_ADMINLANG.billing.gatewaylog}</a></li>
    <li><a href="offlineccprocessing.php">{$_ADMINLANG.billing.offlinecc}</a></li>
  </ul>
</div>
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-calculator"></i> {$_ADMINLANG.invoices.title}</li>
    <li><a href="invoices.php">{$_ADMINLANG.invoices.listall}</a></li>
    <li><a href="invoices.php?status=Paid">- {$_ADMINLANG.status.paid}</a></li>
    <li><a href="invoices.php?status=Unpaid">- {$_ADMINLANG.status.unpaid}</a></li>
    <li><a href="invoices.php?status=Overdue">- {$_ADMINLANG.status.overdue}</a></li>
    <li><a href="invoices.php?status=Cancelled">- {$_ADMINLANG.status.cancelled}</a></li>
    <li><a href="invoices.php?status=Refunded">- {$_ADMINLANG.status.refunded}</a></li>
    <li><a href="invoices.php?status=Collections">- {$_ADMINLANG.status.collections}</a></li>
  </ul>
</div>
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-clock-o"></i> {$_ADMINLANG.billableitems.title}</li>
    <li><a href="billableitems.php">{$_ADMINLANG.billableitems.listall}</a></li>
    <li><a href="billableitems.php?status=Uninvoiced">- {$_ADMINLANG.billableitems.uninvoiced}</a></li>
    <li><a href="billableitems.php?status=Recurring">- {$_ADMINLANG.billableitems.recurring}</a></li>
    <li><a href="billableitems.php?action=manage">{$_ADMINLANG.billableitems.addnew}</a></li>
  </ul>
</div>
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-quote-left"></i> {$_ADMINLANG.quotes.title}</li>
    <li><a href="quotes.php">{$_ADMINLANG.quotes.listall}</a></li>
    <li><a href="quotes.php?validity=Valid">- {$_ADMINLANG.status.valid}</a></li>
    <li><a href="quotes.php?validity=Expired">- {$_ADMINLANG.status.expired}</a></li>
    <li><a href="quotes.php?action=manage">{$_ADMINLANG.quotes.createnew}</a></li>
  </ul>
</div>
{elseif $sidebar eq "support"}
{if $inticket}
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-life-ring"></i> {$_ADMINLANG.support.ticketinfo} {$_ADMINLANG.fields.client}</li>
    <li class="text">
      <div class="ticketinfo"> {if $userid}<a href="clientssummary.php?userid={$userid}"{if $clientgroupcolour} style="background-color:{$clientgroupcolour}"{/if} target="_blank">{$clientname}</a>{if $contactid} (<a href="clientscontacts.php?userid={$userid}&contactid={$contactid}"{if $clientgroupcolour} style="background-color:{$clientgroupcolour}"{/if} target="_blank">{$contactname}</a>){/if}{else}{$_ADMINLANG.support.notregclient}{/if} </div>
      <span class="ticketheader">{$_ADMINLANG.support.department}</span>
      <div class="ticketinfo">
        <select id="deptid" onchange="updateTicket('deptid')">
          
  {foreach from=$departments item=department}
          <option value="{$department.id}"{if $department.id eq $deptid} selected{/if}>{$department.name}</option>
          {/foreach}
  
        </select>
      </div>
      <span class="ticketheader">{$_ADMINLANG.support.assignedto}</span>
      <div class="ticketinfo">
        <select id="flagto" onchange="updateTicket('flagto')">
          <option value="0">{$_ADMINLANG.global.none}</option>
          
    {foreach from=$staff item=staffmember}
          <option value="{$staffmember.id}"{if $staffmember.id eq $flag} selected{/if}>{$staffmember.name}</option>
          {/foreach}
    
        </select>
        <a href="#" class="button small" onclick="$('#flagto').val({$adminid});$('#flagto').trigger('change');return false">{$_ADMINLANG.support.me}</a> </div>
      <span class="ticketheader">{$_ADMINLANG.support.priority}</span>
      <div class="ticketinfo">
        <select id="priority" onchange="updateTicket('priority')">
          <option value="High"{if $priority eq "High"} selected{/if}>{$_ADMINLANG.status.high}</option>
          <option value="Medium"{if $priority eq "Medium"} selected{/if}>{$_ADMINLANG.status.medium}</option>
          <option value="Low"{if $priority eq "Low"} selected{/if}>{$_ADMINLANG.status.low}</option>
        </select>
      </div>
      <span class="ticketheader">{$_ADMINLANG.support.staffparticipants}</span>
      <div class="ticketinfo"> {foreach from=$staffinvolved item=staffname}
        {$staffname}<br />
        {foreachelse}
        No Replies Yet
        {/foreach} </div>
      <span class="ticketheader">{$_ADMINLANG.support.tags}</span>
      <div class="ticketinfo">
        <textarea id="ticketTags" rows="1"></textarea>
      </div>
    </li>
  </ul>
</div>
{else}
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-life-ring"></i> {$_ADMINLANG.support.title}</li>
    <li><a href="supportannouncements.php">{$_ADMINLANG.support.announcements}</a></li>
    <li><a href="supportdownloads.php">{$_ADMINLANG.support.downloads}</a></li>
    <li><a href="supportkb.php">{$_ADMINLANG.support.knowledgebase}</a></li>
    <li><a href="supporttickets.php?action=open">{$_ADMINLANG.support.opennewticket}</a></li>
    <li><a href="supportticketpredefinedreplies.php">{$_ADMINLANG.support.predefreplies}</a></li>
  </ul>
</div>
{/if}
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-ticket"></i> {$_ADMINLANG.support.filtertickets}</li>
    <li class="text">
      <form method="post" action="supporttickets.php">
        <strong>{$_ADMINLANG.fields.status}</strong><br />
        <select name="view">
          <option value="any">- Any -</option>
          <option value=""{if $ticketfilterdata.view eq ""} selected{/if}>{$_ADMINLANG.support.awaitingreply} ({$ticketsawaitingreply})</option>
          <option value="flagged"{if $ticketfilterdata.view eq "flagged"} selected{/if}>{$_ADMINLANG.support.flagged} ({$ticketsflagged})</option>
          <option value="active"{if $ticketfilterdata.view eq "active"} selected{/if}>{$_ADMINLANG.support.allactive} ({$ticketsallactive})</option>
          
    {foreach from=$ticketstatuses item=status}
          <option value="{$status.title}"{if $status.title eq $ticketfilterdata.view} selected{/if}>{$status.title} ({$status.count})</option>
          {/foreach}
  
        </select>
        <br />
        <strong>{$_ADMINLANG.support.department}</strong><br />
        <select name="deptid">
          <option value="">- Any -</option>
          
    {foreach from=$ticketdepts item=dept}
          <option value="{$dept.id}"{if $dept.id eq $ticketfilterdata.deptid} selected{/if}>{$dept.name}</option>
          {/foreach}
  
        </select>
        <br />
        <strong>{$_ADMINLANG.support.subjectmessage}</strong><br />
        <input type="text" name="subject" value="{$ticketfilterdata.subject}" />
        <br />
        <strong>{$_ADMINLANG.fields.email}</strong><br />
        <input type="text" name="email" value="{$ticketfilterdata.email}" />
        <div class="text-center">
          <input type="submit" value="{$_ADMINLANG.global.filter} &raquo;" />
        </div>
      </form>
    </li>
  </ul>
</div>
{if $inticketlist}
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-tags"></i> {$_ADMINLANG.support.tagcloud}</span></li>
    <li class="text">
      <div class="tagcloud">{$tagcloud}</div>
    </li>
  </ul>
</div>
{/if}

{if !$inticket}
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-exclamation-triangle"></i> {$_ADMINLANG.networkissues.title}</li>
    <li><a href="networkissues.php">- {$_ADMINLANG.networkissues.open}</a></li>
    <li><a href="networkissues.php?view=scheduled">- {$_ADMINLANG.networkissues.scheduled}</a></li>
    <li><a href="networkissues.php?view=resolved">- {$_ADMINLANG.networkissues.resolved}</a></li>
    <li><a href="networkissues.php?action=manage">{$_ADMINLANG.networkissues.addnew}</a></li>
  </ul>
</div>
{/if}

{elseif $sidebar eq "reports"}
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-pie-chart"></i> {$_ADMINLANG.reports.title}</li>
    {foreach from=$text_reports key=filename item=reporttitle}
    <li><a href="reports.php?report={$filename}">{$reporttitle}</a></li>
    {/foreach}
  </ul>
</div>
{elseif $sidebar eq "browser"}
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-globe"></i> {$_ADMINLANG.browser.bookmarks}</li>
    <li><a href="http://www.whmcs.com/" target="brwsrwnd">WHMCS Homepage</a></li>
    <li><a href="https://www.whmcs.com/clients/" target="brwsrwnd">WHMCS Client Area</a></li>
    {foreach from=$browserlinks item=link}
    <li><a href="{$link.url}" target="brwsrwnd">{$link.name} <i class="fa fa-times textred" onclick="doDelete('{$link.id}')"></i></a></li>
    {/foreach}
  </ul>
</div>
<form method="post" action="browser.php?action=add">
  <input type="hidden" name="token" value="{$csrfToken}" />
  <div class="sidebar-widget">
    <ul class="side-nav">
      <li class="heading"><i class="fa fa-plus-circle"></i> {$_ADMINLANG.browser.addnew}</li>
      <li class="text"><strong>{$_ADMINLANG.browser.sitename}:</strong><br />
        <input type="text" name="sitename" size="25" />
        <br />
        <strong>{$_ADMINLANG.browser.url}:</strong><br />
        <input type="text" name="siteurl" size="25" value="http://" />
        <br />
        <div class="text-center">
          <input type="submit" value="{$_ADMINLANG.browser.add}">
        </div>
      </li>
    </ul>
  </div>
</form>
{elseif $sidebar eq "utilities"}
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-plug"></i> {$_ADMINLANG.utilities.title}</li>
    <li><a href="utilitieslinktracking.php">{$_ADMINLANG.utilities.linktracking}</a></li>
    <li><a href="browser.php">{$_ADMINLANG.utilities.browser}</a></li>
    <li><a href="calendar.php">{$_ADMINLANG.utilities.calendar}</a></li>
    <li><a href="todolist.php">{$_ADMINLANG.utilities.todolist}</a></li>
    <li><a href="whois.php">{$_ADMINLANG.utilities.whois}</a></li>
    <li><a href="utilitiesresolvercheck.php">{$_ADMINLANG.utilities.domainresolver}</a></li>
    <li><a href="systemintegrationcode.php">{$_ADMINLANG.utilities.integrationcode}</a></li>
    <li><a href="whmimport.php">{$_ADMINLANG.utilities.cpanelimport}</a></li>
    <li><a href="systemdatabase.php">{$_ADMINLANG.utilities.dbstatus}</a></li>
    <li><a href="systemcleanup.php">{$_ADMINLANG.utilities.syscleanup}</a></li>
    <li><a href="systemphpinfo.php">{$_ADMINLANG.utilities.phpinfo}</a></li>
  </ul>
</div>
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-list"></i> {$_ADMINLANG.utilities.logs}</li>
    <li><a href="systemactivitylog.php">{$_ADMINLANG.utilities.activitylog}</a></li>
    <li><a href="systemadminlog.php">{$_ADMINLANG.utilities.adminlog}</a></li>
    <li><a href="systemmodulelog.php">{$_ADMINLANG.utilities.modulelog}</a></li>
    <li><a href="systememaillog.php">{$_ADMINLANG.utilities.emaillog}</a></li>
    <li><a href="systemmailimportlog.php">{$_ADMINLANG.utilities.ticketmaillog}</a></li>
    <li><a href="systemwhoislog.php">{$_ADMINLANG.utilities.whoislog}</a></li>
  </ul>
</div>
{elseif $sidebar eq "addonmodules"}

{$addon_module_sidebar}
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-puzzle-piece"></i> {$_ADMINLANG.utilities.addonmodules}</li>
    {foreach from=$addon_modules key=filename item=addontitle}
    <li><a href="addonmodules.php?module={$filename}">{$addontitle}</a></li>
    {/foreach}
  </ul>
</div>
{elseif $sidebar eq "config"}
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-cog"></i> {$_ADMINLANG.setup.config}</li>
    <li><a href="configgeneral.php">{$_ADMINLANG.setup.general}</a></li>
    <li><a href="configauto.php">{$_ADMINLANG.setup.automation}</a></li>
    <li><a href="configemailtemplates.php">{$_ADMINLANG.setup.emailtpls}</a></li>
    <li><a href="configaddonmods.php">{$_ADMINLANG.setup.addonmodules}</a></li>
    <li><a href="configclientgroups.php">{$_ADMINLANG.setup.clientgroups}</a></li>
    <li><a href="configfraud.php">{$_ADMINLANG.setup.fraud}</a></li>
  </ul>
</div>
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-user"></i> {$_ADMINLANG.setup.staff}</li>
    <li><a href="configadmins.php">{$_ADMINLANG.setup.admins}</a></li>
    <li><a href="configadminroles.php">{$_ADMINLANG.setup.adminroles}</a></li>
    <li><a href="configtwofa.php">{$_ADMINLANG.setup.twofa}</a></li>
  </ul>
</div>
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-money"></i> {$_ADMINLANG.setup.payments}</li>
    <li><a href="configcurrencies.php">{$_ADMINLANG.setup.currencies}</a></li>
    <li><a href="configgateways.php">{$_ADMINLANG.setup.gateways}</a></li>
    <li><a href="configtax.php">{$_ADMINLANG.setup.tax}</a></li>
    <li><a href="configpromotions.php">{$_ADMINLANG.setup.promos}</a></li>
  </ul>
</div>
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-shopping-cart"></i> {$_ADMINLANG.setup.products}</li>
    <li><a href="configproducts.php">{$_ADMINLANG.setup.products}</a></li>
    <li><a href="configproductoptions.php">{$_ADMINLANG.setup.configoptions}</a></li>
    <li><a href="configaddons.php">{$_ADMINLANG.setup.addons}</a></li>
    <li><a href="configbundles.php">{$_ADMINLANG.setup.bundles}</a></li>
    <li><a href="configdomains.php">{$_ADMINLANG.setup.domainpricing}</a></li>
    <li><a href="configregistrars.php">{$_ADMINLANG.setup.registrars}</a></li>
    <li><a href="configservers.php">{$_ADMINLANG.setup.servers}</a></li>
  </ul>
</div>
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-life-ring"></i> {$_ADMINLANG.support.title}</li>
    <li><a href="configticketdepartments.php">{$_ADMINLANG.setup.supportdepartments}</a></li>
    <li><a href="configticketstatuses.php">{$_ADMINLANG.setup.ticketstatuses}</a></li>
    <li><a href="configticketescalations.php">{$_ADMINLANG.setup.escalationrules}</a></li>
    <li><a href="configticketspamcontrol.php">{$_ADMINLANG.setup.spam}</a></li>
  </ul>
</div>
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-database"></i> {$_ADMINLANG.setup.other}</li>
    <li><a href="configcustomfields.php">{$_ADMINLANG.setup.customclientfields}</a></li>
    <li><a href="configorderstatuses.php">{$_ADMINLANG.setup.orderstatuses}</a></li>
    <li><a href="configsecurityqs.php">{$_ADMINLANG.setup.securityqs}</a></li>
    <li><a href="configbannedips.php">{$_ADMINLANG.setup.bannedips}</a></li>
    <li><a href="configbannedemails.php">{$_ADMINLANG.setup.bannedemails}</a></li>
    <li><a href="configbackups.php">{$_ADMINLANG.setup.backups}</a></li>
  </ul>
</div>
{/if}
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-search"></i> {$_ADMINLANG.global.advancedsearch}</li>
    <li class="text">
      <form method="get" action="search.php">
        <select name="type" id="searchtype" onchange="populate(this)">
          <option value="clients">Clients </option>
          <option value="orders">Orders </option>
          <option value="services">Services </option>
          <option value="domains">Domains </option>
          <option value="invoices">Invoices </option>
          <option value="tickets">Tickets </option>
        </select>
        <select name="field" id="searchfield">
          <option>Client ID</option>
          <option selected="selected">Client Name</option>
          <option>Company Name</option>
          <option>Email Address</option>
          <option>Address 1</option>
          <option>Address 2</option>
          <option>City</option>
          <option>State</option>
          <option>Postcode</option>
          <option>Country</option>
          <option>Phone Number</option>
          <option>CC Last Four</option>
        </select>
        <input type="text" name="q" style="width:85%;" />
        <input type="submit" value="{$_ADMINLANG.global.search}" class="button" />
      </form>
    </li>
  </ul>
</div>
<div class="sidebar-widget">
  <ul class="side-nav">
    <li class="heading"><i class="fa fa-user"></i> {$_ADMINLANG.global.staffonline}</li>
    <li class="text">{$adminsonline}</li>
  </ul>
</div>