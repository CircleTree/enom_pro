<ul>
  <li><a id="Menu-Clients" {if in_array("List Clients",$admin_perms)}href="clients.php"{/if} title="Clients">{$_ADMINLANG.clients.title} <i class="fa fa-angle-down"></i></a>
    <ul class="dropdown">
      {if in_array("List Clients",$admin_perms)}
      <li><a id="Menu-Clients-View_Search_Clients" href="clients.php">{$_ADMINLANG.clients.viewsearch}</a></li>
      {/if}
      {if in_array("Add New Client",$admin_perms)}
      <li><a id="Menu-Clients-Add_New_Client" href="clientsadd.php">{$_ADMINLANG.clients.addnew}</a></li>
      {/if}
      {if in_array("List Services",$admin_perms)}
      <li class="expand"><a id="Menu-Clients-Products_Services" href="clientshostinglist.php">{$_ADMINLANG.services.title} <i class="fa fa-angle-right"></i></a>
        <ul>
          <li><a id="Menu-Clients-Products_Services-Shared_Hosting" href="clientshostinglist.php?listtype=hostingaccount">{$_ADMINLANG.services.listhosting}</a></li>
          <li><a id="Menu-Clients-Products_Services-Reseller_Accounts" href="clientshostinglist.php?listtype=reselleraccount">{$_ADMINLANG.services.listreseller}</a></li>
          <li><a id="Menu-Clients-Products_Services-VPS_Servers" href="clientshostinglist.php?listtype=server">{$_ADMINLANG.services.listservers}</a></li>
          <li><a id="Menu-Clients-Products_Services-Other_Services" href="clientshostinglist.php?listtype=other">{$_ADMINLANG.services.listother}</a></li>
        </ul>
      </li>
      {/if}
      {if in_array("List Addons",$admin_perms)}
      <li><a id="Menu-Clients-Service_Addons" href="clientsaddonslist.php">{$_ADMINLANG.services.listaddons}</a></li>
      {/if}
      {if in_array("List Domains",$admin_perms)}
      <li><a id="Menu-Clients-Domain_Registration" href="clientsdomainlist.php">{$_ADMINLANG.services.listdomains}</a></li>
      {/if}
      {if in_array("View Cancellation Requests",$admin_perms)}
      <li><a id="Menu-Clients-Cancelation_Requests" href="cancelrequests.php">{$_ADMINLANG.clients.cancelrequests}</a></li>
      {/if}
      {if in_array("Manage Affiliates",$admin_perms)}
      <li><a id="Menu-Clients-Manage_Affiliates" href="affiliates.php">{$_ADMINLANG.affiliates.manage}</a></li>
      {/if}
      {if in_array("Mass Mail",$admin_perms)}
      <li><a id="Menu-Clients-Mass_Mail_Tool" href="massmail.php">{$_ADMINLANG.clients.massmail}</a></li>
      {/if}
    </ul>
  </li>
  <li><a id="Menu-Orders" {if in_array("View Orders",$admin_perms)}href="orders.php"{/if} title="Orders">{$_ADMINLANG.orders.title} <i class="fa fa-angle-down"></i></a>
    <ul class="dropdown">
      {if in_array("View Orders",$admin_perms)}
      <li class="expand"><a id="Menu-Orders-List_All_Orders" href="orders.php">{$_ADMINLANG.orders.listall} <i class="fa fa-angle-right"></i></a>
        <ul>
          <li><a id="Menu-Orders-Pending_Orders" href="orders.php?status=Pending">{$_ADMINLANG.orders.listpending}</a></li>
          <li><a id="Menu-Orders-Active_Orders" href="orders.php?status=Active">{$_ADMINLANG.orders.listactive}</a></li>
          <li><a id="Menu-Orders-Fraud_Orders" href="orders.php?status=Fraud">{$_ADMINLANG.orders.listfraud}</a></li>
          <li><a id="Menu-Orders-Cancelled_Orders" href="orders.php?status=Cancelled">{$_ADMINLANG.orders.listcancelled}</a></li>
        </ul>
      </li>
      {/if}
      {if in_array("Add New Order",$admin_perms)}
      <li><a id="Menu-Orders-Add_New_Order" href="ordersadd.php">{$_ADMINLANG.orders.addnew}</a></li>
      {/if}
    </ul>
  </li>
  <li><a id="Menu-Billing" {if in_array("List Transactions",$admin_perms)}href="transactions.php"{/if} title="Billing">{$_ADMINLANG.billing.title} <i class="fa fa-angle-down"></i></a>
    <ul class="dropdown">
      {if in_array("List Transactions",$admin_perms)}
      <li><a id="Menu-Billing-Transactions_List" href="transactions.php">{$_ADMINLANG.billing.transactionslist}</a></li>
      {/if}
      {if in_array("List Invoices",$admin_perms)}
      <li class="expand"><a id="Menu-Billing-Invoices" href="invoices.php">{$_ADMINLANG.invoices.title} <i class="fa fa-angle-right"></i></a>
        <ul>
          <li><a id="Menu-Billing-Invoices-Paid" href="invoices.php?status=Paid">{$_ADMINLANG.status.paid}</a></li>
          <li><a id="Menu-Billing-Invoices-Unpaid" href="invoices.php?status=Unpaid">{$_ADMINLANG.status.unpaid}</a></li>
          <li><a id="Menu-Billing-Invoices-Overdue" href="invoices.php?status=Overdue">{$_ADMINLANG.status.overdue}</a></li>
          <li><a id="Menu-Billing-Invoices-Cancelled" href="invoices.php?status=Cancelled">{$_ADMINLANG.status.cancelled}</a></li>
          <li><a id="Menu-Billing-Invoices-Refunded" href="invoices.php?status=Refunded">{$_ADMINLANG.status.refunded}</a></li>
          <li><a id="Menu-Billing-Invoices-Collections" href="invoices.php?status=Collections">{$_ADMINLANG.status.collections}</a></li>
        </ul>
      </li>
      {/if}
      {if in_array("View Billable Items",$admin_perms)}
      <li class="expand"><a id="Menu-Billing-Billable_Items" href="billableitems.php">{$_ADMINLANG.billableitems.title} <i class="fa fa-angle-right"></i></a>
        <ul>
          <li><a id="Menu-Billing-Billable_Items-Uninvoiced_Items" href="billableitems.php?status=Uninvoiced">{$_ADMINLANG.billableitems.uninvoiced}</a></li>
          <li><a id="Menu-Billing-Billable_Items-Recurring_Items" href="billableitems.php?status=Recurring">{$_ADMINLANG.billableitems.recurring}</a></li>
          {if in_array("Manage Billable Items",$admin_perms)}
          <li><a id="Menu-Billing-Billable_Items-Add_New" href="billableitems.php?action=manage">{$_ADMINLANG.billableitems.addnew}</a></li>
          {/if}
        </ul>
      </li>
      {/if}
      {if in_array("Manage Quotes",$admin_perms)}
      <li class="expand"><a id="Menu-Billing-Quotes" href="quotes.php">{$_ADMINLANG.quotes.title} <i class="fa fa-angle-right"></i></a>
        <ul>
          <li><a id="Menu-Billing-Quotes-Valid" href="quotes.php?validity=Valid">{$_ADMINLANG.status.valid}</a></li>
          <li><a id="Menu-Billing-Quotes-Expired" href="quotes.php?validity=Expired">{$_ADMINLANG.status.expired}</a></li>
          <li><a id="Menu-Billing-Quotes-Create_New_Quote" href="quotes.php?action=manage">{$_ADMINLANG.quotes.createnew}</a></li>
        </ul>
      </li>
      {/if}
      {if in_array("Offline Credit Card Processing",$admin_perms)}
      <li><a id="Menu-Billing-Offline_CC_Processing" href="offlineccprocessing.php">{$_ADMINLANG.billing.offlinecc}</a></li>
      {/if}
      {if in_array("View Gateway Log",$admin_perms)}
      <li><a id="Menu-Billing-Gateway_Log" href="gatewaylog.php">{$_ADMINLANG.billing.gatewaylog}</a></li>
      {/if}
    </ul>
  </li>
  <li><a id="Menu-Support" {if in_array("Support Center Overview",$admin_perms)}href="supportcenter.php"{/if} title="Support">{$_ADMINLANG.support.title} <i class="fa fa-angle-down"></i></a>
    <ul class="dropdown">
      {if in_array("Support Center Overview",$admin_perms)}
      <li><a id="Menu-Support-Support_Overview" href="supportcenter.php">{$_ADMINLANG.support.supportoverview}</a></li>
      {/if}
      {if in_array("Manage Announcements",$admin_perms)}
      <li><a id="Menu-Support-Annoucements" href="supportannouncements.php">{$_ADMINLANG.support.announcements}</a></li>
      {/if}
      {if in_array("Manage Downloads",$admin_perms)}
      <li><a id="Menu-Support-Downloads" href="supportdownloads.php">{$_ADMINLANG.support.downloads}</a></li>
      {/if}
      {if in_array("Manage Knowledgebase",$admin_perms)}
      <li><a id="Menu-Support-Knowledgebase" href="supportkb.php">{$_ADMINLANG.support.knowledgebase}</a></li>
      {/if}
      {if in_array("List Support Tickets",$admin_perms)}
      <li class="expand"><a id="Menu-Support-Support_Tickets" href="supporttickets.php">{$_ADMINLANG.support.supporttickets} <i class="fa fa-angle-right"></i></a>
        <ul>
          <li><a id="Menu-Support-Support_Tickets-Flagged_Tickets" href="supporttickets.php?view=flagged">{$_ADMINLANG.support.flagged}</a></li>
          <li><a id="Menu-Support-Support_Tickets-All_Active_Tickets" href="supporttickets.php?view=active">{$_ADMINLANG.support.allactive}</a></li>
          {foreach from=$menuticketstatuses item=status}<li><a id="Menu-Support-Support_Tickets-{$status.title|replace:' ':'_'}" href="supporttickets.php?view={$status.title}">{$status.title}</a></li>{/foreach}
        </ul>
      </li>
      {/if}
      {if in_array("Open New Ticket",$admin_perms)}
      <li><a id="Menu-Support-Open_New_Ticket" href="supporttickets.php?action=open">{$_ADMINLANG.support.opennewticket}</a></li>
      {/if}
      {if in_array("Manage Predefined Replies",$admin_perms)}
      <li><a id="Menu-Support-Predefined_Replies" href="supportticketpredefinedreplies.php">{$_ADMINLANG.support.predefreplies}</a></li>
      {/if}
      {if in_array("Manage Network Issues",$admin_perms)}
      <li class="expand"><a id="Menu-Support-Network_Issues" href="networkissues.php">{$_ADMINLANG.networkissues.title}</a>
        <ul>
          <li><a id="Menu-Support-Network_Issues-Open" href="networkissues.php">{$_ADMINLANG.networkissues.open}</a></li>
          <li><a id="Menu-Support-Network_Issues-Scheduled" href="networkissues.php?view=scheduled">{$_ADMINLANG.networkissues.scheduled}</a></li>
          <li><a id="Menu-Support-Network_Issues-Resolved" href="networkissues.php?view=resolved">{$_ADMINLANG.networkissues.resolved}</a></li>
          <li><a id="Menu-Support-Network_Issues-Create_New" href="networkissues.php?action=manage">{$_ADMINLANG.networkissues.addnew}</a></li>
        </ul>
      </li>
      {/if}
    </ul>
  </li>
  {if in_array("View Reports",$admin_perms)}
  <li><a id="Menu-Reports" title="Reports" href="reports.php">{$_ADMINLANG.reports.title} <i class="fa fa-angle-down"></i></a>
    <ul class="dropdown">
      <li><a id="Menu-Reports-Daily_Performance" href="reports.php?report=daily_performance">Daily Performance</a></li>
      <li><a id="Menu-Reports-Income_Forecast" href="reports.php?report=income_forecast">Income Forecast</a></li>
      <li><a id="Menu-Reports-Annual_Income_Report" href="reports.php?report=annual_income_report">Annual Income Report</a></li>
      <li><a id="Menu-Reports-New_Customers" href="reports.php?report=new_customers">New Customers</a></li>
      <li><a id="Menu-Reports-Ticket_Feedback_Scores" href="reports.php?report=ticket_feedback_scores">Ticket Feedback Scores</a></li>
      <li><a id="Menu-Reports-Batch_Invoice_PDF_Export" href="reports.php?report=pdf_batch">Batch Invoice PDF Export</a></li>
      <li><a id="Menu-Reports-More..." href="reports.php">More...</a></li>
    </ul>
  </li>
  {/if}
  <li><a id="Menu-Utilities" title="Utilities">{$_ADMINLANG.utilities.title} <i class="fa fa-angle-down"></i></a>
    <ul class="dropdown right-align">
      {if in_array("Email Marketer",$admin_perms)}
      <li><a id="Menu-Utilities-Email_Marketer" href="utilitiesemailmarketer.php">{$_ADMINLANG.utilities.emailmarketer}</a></li>
      {/if}
      {if in_array("Link Tracking",$admin_perms)}
      <li><a id="Menu-Utilities-Link_Tracking" href="utilitieslinktracking.php">{$_ADMINLANG.utilities.linktracking}</a></li>
      {/if}
      {if in_array("Browser",$admin_perms)}
      <li><a id="Menu-Utilities-Browser" href="browser.php">{$_ADMINLANG.utilities.browser}</a></li>
      {/if}
      {if in_array("Calendar",$admin_perms)}
      <li><a id="Menu-Utilities-Calendar" href="calendar.php">{$_ADMINLANG.utilities.calendar}</a></li>
      {/if}
      {if in_array("To-Do List",$admin_perms)}
      <li><a id="Menu-Utilities-To-Do_List" href="todolist.php">{$_ADMINLANG.utilities.todolist}</a></li>
      {/if}
      {if in_array("WHOIS Lookups",$admin_perms)}
      <li><a id="Menu-Utilities-WHOIS_Lookups" href="whois.php">{$_ADMINLANG.utilities.whois}</a></li>
      {/if}
      {if in_array("Domain Resolver Checker",$admin_perms)}
      <li><a id="Menu-Utilities-Domain_Resolver" href="utilitiesresolvercheck.php">{$_ADMINLANG.utilities.domainresolver}</a></li>
      {/if}
      {if in_array("View Integration Code",$admin_perms)}
      <li><a id="Menu-Utilities-Integration_Code" href="systemintegrationcode.php">{$_ADMINLANG.utilities.integrationcode}</a></li>
      {/if}
      {if in_array("WHM Import Script",$admin_perms)}
      <li><a id="Menu-Utilities-cPanel_WHM_Import" href="whmimport.php">{$_ADMINLANG.utilities.cpanelimport}</a></li>
      {/if}
      {if in_array("Database Status",$admin_perms) || in_array("System Cleanup Operations",$admin_perms) || in_array("View PHP Info",$admin_perms)}
      <li class="expand"><a id="Menu-Utilities-System" href="#">{$_ADMINLANG.utilities.system} <i class="fa fa-angle-left"></i></a>
        <ul>
          {if in_array("Database Status",$admin_perms)}
          <li><a id="Menu-Utilities-System-Database_Status" href="systemdatabase.php">{$_ADMINLANG.utilities.dbstatus}</a></li>
          {/if}
          {if in_array("System Cleanup Operations",$admin_perms)}
          <li><a id="Menu-Utilities-System-System_Cleanup" href="systemcleanup.php">{$_ADMINLANG.utilities.syscleanup}</a></li>
          {/if}
          {if in_array("View PHP Info",$admin_perms)}
          <li><a id="Menu-Utilities-System-PHP_Info" href="systemphpinfo.php">{$_ADMINLANG.utilities.phpinfo}</a></li>
          {/if}
        </ul>
      </li>
      {/if}
      {if in_array("View Activity Log",$admin_perms) || in_array("View Admin Log",$admin_perms) || in_array("View Module Debug Log",$admin_perms) || in_array("View Email Message Log",$admin_perms) || in_array("View Ticket Mail Import Log",$admin_perms) || in_array("View WHOIS Lookup Log",$admin_perms)}
      <li class="expand"><a id="Menu-Utilities-Logs" href="#">{$_ADMINLANG.utilities.logs} <i class="fa fa-angle-left"></i></a>
        <ul>
          {if in_array("View Activity Log",$admin_perms)}
          <li><a id="Menu-Utilities-Logs-Activity_Log" href="systemactivitylog.php">{$_ADMINLANG.utilities.activitylog}</a></li>
          {/if}
          {if in_array("View Admin Log",$admin_perms)}
          <li><a id="Menu-Utilities-Logs-Admin_Log" href="systemadminlog.php">{$_ADMINLANG.utilities.adminlog}</a></li>
          {/if}
          {if in_array("View Module Debug Log",$admin_perms)}
          <li><a id="Menu-Utilities-Logs-Module_Log" href="systemmodulelog.php">{$_ADMINLANG.utilities.modulelog}</a></li>
          {/if}
          {if in_array("View Email Message Log",$admin_perms)}
          <li><a id="Menu-Utilities-Logs-Email_Message_Log" href="systememaillog.php">{$_ADMINLANG.utilities.emaillog}</a></li>
          {/if}
          {if in_array("View Ticket Mail Import Log",$admin_perms)}
          <li><a id="Menu-Utilities-Logs-Ticket_Email_Import_Log" href="systemmailimportlog.php">{$_ADMINLANG.utilities.ticketmaillog}</a></li>
          {/if}
          {if in_array("View WHOIS Lookup Log",$admin_perms)}
          <li><a id="Menu-Utilities-Logs-WHOIS_Lookup_Log" href="systemwhoislog.php">{$_ADMINLANG.utilities.whoislog}</a></li>
          {/if}
        </ul>
      </li>
      {/if}
    </ul>
  </li>
  <li><a id="Menu-Addons" title="Addons" href="addonmodules.php">{$_ADMINLANG.utilities.addonmodules} <i class="fa fa-angle-down"></i></a>
    <ul class="dropdown right-align">
      {foreach from=$addon_modules key=module item=displayname}
      <li><a id="Menu-Addons-{$displayname}" href="addonmodules.php?module={$module}">{$displayname}</a></li>
      {foreachelse}
      <li><a id="Menu-Addons-Addons_Directory" href="addonmodules.php">{$_ADMINLANG.utilities.addonsdirectory}</a></li>
      {/foreach}
    </ul>
  </li>
  <li><a id="Menu-Setup" title="Setup">{$_ADMINLANG.setup.title} <i class="fa fa-angle-down"></i></a>
    <ul class="dropdown right-align">
      {if in_array("Configure General Settings",$admin_perms)}
      <li><a id="Menu-Setup-General_Settings" href="configgeneral.php">{$_ADMINLANG.setup.general}</a></li>
      {/if}
      {if in_array("Configure Automation Settings",$admin_perms)}
      <li><a id="Menu-Setup-Automation_Settings" href="configauto.php">{$_ADMINLANG.setup.automation}</a></li>
      {/if}
      {if in_array("Configure Administrators",$admin_perms) || in_array("Configure Admin Roles",$admin_perms) || in_array("Configure Two-Factor Authentication",$admin_perms)}
      <li class="expand"><a id="Menu-Setup-Staff_Management" href="#">{$_ADMINLANG.setup.staff} <i class="fa fa-angle-left"></i></a>
        <ul>
          {if in_array("Configure Administrators",$admin_perms)}
          <li><a id="Menu-Setup-Staff_Management-Administrator_Users" href="configadmins.php">{$_ADMINLANG.setup.admins}</a></li>
          {/if}
          {if in_array("Configure Admin Roles",$admin_perms)}
          <li><a id="Menu-Setup-Staff_Management-Administrator_Roles" href="configadminroles.php">{$_ADMINLANG.setup.adminroles}</a></li>
          {/if}
          {if in_array("Configure Two-Factor Authentication",$admin_perms)}
          <li><a id="Menu-Setup-Staff_Management-Two-Factor_Authentication" href="configtwofa.php">{$_ADMINLANG.setup.twofa}</a></li>
          {/if}
        </ul>
      </li>
      {else}
      <li><a id="Menu-Setup-Staff_Management-My_Account" href="myaccount.php">{$_ADMINLANG.global.myaccount}</a></li>
      {/if}
      {if in_array("Configure Currencies",$admin_perms) || in_array("Configure Payment Gateways",$admin_perms) || in_array("Configure Tax Setup",$admin_perms) || in_array("View Promotions",$admin_perms)}
      <li class="expand"><a id="Menu-Setup-Payments" href="#">{$_ADMINLANG.setup.payments} <i class="fa fa-angle-left"></i></a>
        <ul>
          {if in_array("Configure Currencies",$admin_perms)}
          <li><a id="Menu-Setup-Payments-Currencies" href="configcurrencies.php">{$_ADMINLANG.setup.currencies}</a></li>
          {/if}
          {if in_array("Configure Payment Gateways",$admin_perms)}
          <li><a id="Menu-Setup-Payments-Payment_Gateways" href="configgateways.php">{$_ADMINLANG.setup.gateways}</a></li>
          {/if}
          {if in_array("Configure Tax Setup",$admin_perms)}
          <li><a id="Menu-Setup-Payments-Tax_Rules" href="configtax.php">{$_ADMINLANG.setup.tax}</a></li>
          {/if}
          {if in_array("View Promotions",$admin_perms)}
          <li><a id="Menu-Setup-Payments-Promotions" href="configpromotions.php">{$_ADMINLANG.setup.promos}</a></li>
          {/if}
        </ul>
      </li>
      {/if}
      {if in_array("View Products/Services",$admin_perms) || in_array("Configure Product Addons",$admin_perms) || in_array("Configure Product Bundles",$admin_perms) || in_array("Configure Domain Pricing",$admin_perms) || in_array("Configure Domain Registrars",$admin_perms) || in_array("Configure Servers",$admin_perms)}
      <li class="expand"><a id="Menu-Setup-Products_Services" href="#">{$_ADMINLANG.setup.products} <i class="fa fa-angle-left"></i></a>
        <ul>
          {if in_array("View Products/Services",$admin_perms)}
          <li><a id="Menu-Setup-Products_Services-Products_Services" href="configproducts.php">{$_ADMINLANG.setup.products}</a></li>
          {/if}
          {if in_array("View Products/Services",$admin_perms)}
          <li><a id="Menu-Setup-Products_Services-Configurable_Options" href="configproductoptions.php">{$_ADMINLANG.setup.configoptions}</a></li>
          {/if}
          {if in_array("Configure Product Addons",$admin_perms)}
          <li><a id="Menu-Setup-Products_Services-Product_Addons" href="configaddons.php">{$_ADMINLANG.setup.addons}</a></li>
          {/if}
          {if in_array("Configure Product Bundles",$admin_perms)}
          <li><a id="Menu-Setup-Products_Services-Product_Bundles" href="configbundles.php">{$_ADMINLANG.setup.bundles}</a></li>
          {/if}
          {if in_array("Configure Domain Pricing",$admin_perms)}
          <li><a id="Menu-Setup-Products_Services-Domain_Pricing" href="configdomains.php">{$_ADMINLANG.setup.domainpricing}</a></li>
          {/if}
          {if in_array("Configure Domain Registrars",$admin_perms)}
          <li><a id="Menu-Setup-Products_Services-Domain_Registrars" href="configregistrars.php">{$_ADMINLANG.setup.registrars}</a></li>
          {/if}
          {if in_array("Configure Servers",$admin_perms)}
          <li><a id="Menu-Setup-Products_Services-Servers" href="configservers.php">{$_ADMINLANG.setup.servers}</a></li>
          {/if}
        </ul>
      </li>
      {/if}
      {if in_array("Configure Support Departments",$admin_perms) || in_array("Configure Ticket Statuses",$admin_perms) || in_array("Configure Support Departments",$admin_perms) || in_array("Configure Spam Control",$admin_perms)}
      <li class="expand"><a id="Menu-Setup-Support" href="#">{$_ADMINLANG.support.title} <i class="fa fa-angle-left"></i></a>
        <ul>
          {if in_array("Configure Support Departments",$admin_perms)}
          <li><a id="Menu-Setup-Support-Support_Departments" href="configticketdepartments.php">{$_ADMINLANG.setup.supportdepartments}</a></li>
          {/if}
          {if in_array("Configure Ticket Statuses",$admin_perms)}
          <li><a id="Menu-Setup-Support-Ticket_Statuses" href="configticketstatuses.php">{$_ADMINLANG.setup.ticketstatuses}</a></li>
          {/if}
          {if in_array("Configure Support Departments",$admin_perms)}
          <li><a id="Menu-Setup-Support-Escalation_Rules" href="configticketescalations.php">{$_ADMINLANG.setup.escalationrules}</a></li>
          {/if}
          {if in_array("Configure Spam Control",$admin_perms)}
          <li><a id="Menu-Setup-Support-Spam_Control" href="configticketspamcontrol.php">{$_ADMINLANG.setup.spam}</a></li>
          {/if}
        </ul>
      </li>
      {/if}
      {if in_array("View Email Templates",$admin_perms)}
      <li><a id="Menu-Setup-Email_Templates" href="configemailtemplates.php">{$_ADMINLANG.setup.emailtpls}</a></li>
      {/if}
      {if in_array("Configure Addon Modules",$admin_perms)}
      <li><a id="Menu-Setup-Addons_Modules" href="configaddonmods.php">{$_ADMINLANG.setup.addonmodules}</a></li>
      {/if}
      {if in_array("Configure Client Groups",$admin_perms)}
      <li><a id="Menu-Setup-Client_Groups" href="configclientgroups.php">{$_ADMINLANG.setup.clientgroups}</a></li>
      {/if}
      {if in_array("Configure Custom Client Fields",$admin_perms)}
      <li><a id="Menu-Setup-Custom_Client_Fields" href="configcustomfields.php">{$_ADMINLANG.setup.customclientfields}</a></li>
      {/if}
      {if in_array("Configure Fraud Protection",$admin_perms)}
      <li><a id="Menu-Setup-Fraud_Protection" href="configfraud.php">{$_ADMINLANG.setup.fraud}</a></li>
      {/if}
      {if in_array("Configure Order Statuses",$admin_perms) || in_array("Configure Security Questions",$admin_perms) || in_array("View Banned IPs",$admin_perms) || in_array("Configure Banned Emails",$admin_perms) || in_array("Configure Database Backups",$admin_perms)}
      <li class="expand"><a id="Menu-Setup-Other" href="#">{$_ADMINLANG.setup.other} <i class="fa fa-angle-left"></i></a>
        <ul>
          {if in_array("Configure Order Statuses",$admin_perms)}
          <li><a id="Menu-Setup-Other-Order_Statuses" href="configorderstatuses.php">{$_ADMINLANG.setup.orderstatuses}</a></li>
          {/if}
          {if in_array("Configure Security Questions",$admin_perms)}
          <li><a id="Menu-Setup-Other-Security_Questions" href="configsecurityqs.php">{$_ADMINLANG.setup.securityqs}</a></li>
          {/if}
          {if in_array("View Banned IPs",$admin_perms)}
          <li><a id="Menu-Setup-Other-Banned_IPs" href="configbannedips.php">{$_ADMINLANG.setup.bannedips}</a></li>
          {/if}
          {if in_array("Configure Banned Emails",$admin_perms)}
          <li><a id="Menu-Setup-Other-Banned_Emails" href="configbannedemails.php">{$_ADMINLANG.setup.bannedemails}</a></li>
          {/if}
          {if in_array("Configure Database Backups",$admin_perms)}
          <li><a id="Menu-Setup-Other-Database_Backups" href="configbackups.php">{$_ADMINLANG.setup.backups}</a></li>
          {/if}
        </ul>
      </li>
      {/if}
    </ul>
  </li>
  <li><a id="Menu-Help" title="Help">{$_ADMINLANG.help.title} <i class="fa fa-angle-down"></i></a>
    <ul class="dropdown right-align">
      <li><a id="Menu-Help-Documentation" href="http://docs.whmcs.com/" target="_blank">{$_ADMINLANG.help.docs}</a></li>
      {if in_array("Main Homepage",$admin_perms)}
      <li><a id="Menu-Help-License_Information" href="systemlicense.php">{$_ADMINLANG.help.licenseinfo}</a></li>
      {/if}
      {if in_array("Configure Administrators",$admin_perms)}
      <li><a id="Menu-Help-Change_License_Key" href="licenseerror.php?licenseerror=change">{$_ADMINLANG.help.changelicense}</a></li>
      {/if}
      {if in_array("Configure General Settings",$admin_perms)}
      <li><a id="Menu-Help-Check_For_Updates" href="systemupdates.php">{$_ADMINLANG.help.updates}</a></li>
      <li><a id="Menu-Help-Get_Help" href="systemsupportrequest.php">{$_ADMINLANG.help.support}</a></li>
      {/if}
      <li><a id="Menu-Help-Community_Forums" href="http://forum.whmcs.com/" target="_blank">{$_ADMINLANG.help.forums}</a></li>
    </ul>
  </li>
</ul>
