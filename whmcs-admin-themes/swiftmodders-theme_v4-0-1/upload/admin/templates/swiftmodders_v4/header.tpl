<!DOCTYPE html>
<!--[if IE 9]><html class="lt-ie10" lang="en" ><![endif]-->
<html class="no-js" lang="en">
<head>
<meta charset="{$charset}" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>{$pagetitle} | {$companyname} Admin Panel (Powered by WHMCS)</title>
<link href="templates/{$template}/normalize.css" rel="stylesheet" media="screen" />
<link href="//fonts.googleapis.com/css?family=Ubuntu:400,700,400italic,700italic" rel="stylesheet" media="screen">
<link href="//maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css" rel="stylesheet" media="screen">
<link href="templates/{$template}/jquery-ui.min.css" rel="stylesheet" media="screen" />
<link href="templates/{$template}/style.css" rel="stylesheet" media="all" />
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js" ></script>
<script src="templates/{$template}/jquery-ui.min.js"></script>
{$headoutput}
</head>

<body class="{php}$file = pathinfo( __FILE__ );echo basename( $_SERVER['PHP_SELF'], '.' . $file[ 'extension' ] );$module = $_GET['module'];if ($module != '') {echo ' ' . $module;}{/php}">
{$headeroutput}
<div id="usermenu">
  <ul>
    <li>{$topBarNotification}</li>
    <li><a href="myaccount.php">{$_ADMINLANG.global.myaccount}</a></li>
    <li><a href="#" data-reveal-id="myNotes">{$_ADMINLANG.global.mynotes}</a></li>
    <li><a id="logout" href="logout.php">{$_ADMINLANG.global.logout}</a></li>
  </ul>
</div>
<div id="wrapper">
<nav class="top-bar" data-topbar role="navigation">
  <section class="top-bar-section">
    <ul class="right">
      <li class="has-form">
        <div class="row collapse">
          <form id="frmintellisearch">
            <input type="hidden" name="intellisearch" value="1" />
            <input type="hidden" name="token" value="{$csrfToken}" />
            <div class="small-8 columns">
              <input type="text" name="value" id="intellisearchval" placeholder="{$_ADMINLANG.global.search}..." />
            </div>
            <div class="small-4 columns">
              <input class="button expand alert intelli-submit" type="submit" value="&#xf002;" />
            </div>
          </form>
        </div>
      </li>
      <li class="show-for-large-up"><a href="../" target="_blank">{$_ADMINLANG.global.clientarea} <i class="fa fa-external-link-square"></i></a></li>
      <li class="has-dropdown show-for-large-up"> <a href="#"><i class="fa fa-user"></i> {$admin_username}</a>
        <ul class="dropdown">
          <li>{$topBarNotification}</li>
          <li><a href="myaccount.php">{$_ADMINLANG.global.myaccount}</a></li>
          <li><a href="#" data-reveal-id="myNotes">{$_ADMINLANG.global.mynotes}</a></li>
          <li><a id="logout" href="logout.php">{$_ADMINLANG.global.logout}</a></li>
        </ul>
      </li>
    </ul>
    <ul class="left show-for-large-up">
      <li class="date"><a>{$smarty.now|date_format:"%A, %d %B %Y, %H:%M %P"}</a></li>
      {if $minsidebar}
      <li class="sidebar-open"><a onclick="sidebarOpen();return false" data-tooltip aria-haspopup="true" class="has-tip" title="Open Sidebar"><i class="fa fa-outdent"></i></a></li>
      <li class="sidebar-close hide"><a onclick="sidebarClose();return false" data-tooltip aria-haspopup="true" class="has-tip" title="Close Sidebar"><i class="fa fa-indent"></i></a></li>
      {/if}
      {if !$minsidebar}
      <li class="sidebar-open hide"><a onclick="sidebarOpen();return false" data-tooltip aria-haspopup="true" class="has-tip" title="Open Sidebar"><i class="fa fa-outdent"></i></a></li>
      <li class="sidebar-close"><a onclick="sidebarClose();return false" data-tooltip aria-haspopup="true" class="has-tip" title="Close Sidebar"><i class="fa fa-indent"></i></a></li>
      {/if}
    </ul>
  </section>
</nav>
<header>
  <div class="mobile-menu hide-for-large-up"><a href="#mobile-menu"><i class="fa fa-bars"></i></a></div>
  <div class="logo"><a href="index.php">WHMCS</a></div>
  <div id="mmenu" class="menu">{include file="$template/menu.tpl"}</div>
  <div class="user-menu hide-for-large-up"><a href="#user-menu"><i class="fa fa-user"></i></a></div>
</header>
<div class="row">
<div class="content-container">
<div class="small-12 large-2 columns sidebar show-for-large-up{if $minsidebar} no-show{/if}">{include file="$template/sidebar.tpl"}</div>
<div class="small-12 {if $minsidebar}large-12{/if}{if !$minsidebar}large-10{/if} columns content">
<div class="icon-bar three-up"><a class="item" href="orders.php?status=Pending"><i class="fa fa-shopping-cart"></i>
  <label><span class="count{if $sidebarstats.orders.pending eq 0} hide{/if}">{$sidebarstats.orders.pending}</span>{$_ADMINLANG.stats.pendingorders}</label>
  </a><a class="item" href="invoices.php?status=Overdue"><i class="fa fa-credit-card"></i>
  <label><span class="count{if $sidebarstats.invoices.overdue eq 0} hide{/if}">{$sidebarstats.invoices.overdue}</span>{$_ADMINLANG.stats.overdueinvoices}</label>
  </a><a class="item" href="supporttickets.php"><i class="fa fa-life-ring"></i>
  <label><span class="count{if $sidebarstats.tickets.awaitingreply eq 0} hide{/if}">{$sidebarstats.tickets.awaitingreply}</span>{$_ADMINLANG.stats.ticketsawaitingreply}</label>
  </a></div>
{if $helplink}
<div class="right help"><a class="button small info" href="http://docs.whmcs.com/{$helplink}" target="_blank"><i class="fa fa-info-circle"></i> {$_ADMINLANG.help.contextlink}</a></div>
{/if}
<h1>{$pagetitle}</h1>
