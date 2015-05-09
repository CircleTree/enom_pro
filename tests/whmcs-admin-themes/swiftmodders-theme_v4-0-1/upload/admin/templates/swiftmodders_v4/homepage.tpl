{if $viewincometotals}
<div id="incometotals"><i class="fa fa-circle-o-notch fa-spin"></i> {$_ADMINLANG.global.loading}...</div>
{/if}

{if $maintenancemode}
<div data-alert class="alert-box alert"> {$_ADMINLANG.home.maintenancemode} </div>
{/if}

{$infobox}
<h2>{$_ADMINLANG.global.welcomeback} <strong>{$admin_username}</strong>!</h2>
{foreach from=$addons_html item=addon_html}
<div class="addon-box">{$addon_html}</div>
{/foreach}
<div class="row">
  <div class="homecolumn" id="homecol1">
    <div class="homewidget" id="sysinfo">
      <div class="widget-header">{$_ADMINLANG.global.systeminfo}</div>
      <div class="widget-content">
        <table class="datatable" width="100%">
          <thead>
            <tr>
              <th>{$_ADMINLANG.license.regto}</th>
              <th>{$_ADMINLANG.license.type}</th>
              <th>{$_ADMINLANG.license.expires}</th>
              <th>{$_ADMINLANG.global.version}</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{$licenseinfo.registeredname}</td>
              <td>{$licenseinfo.productname}</td>
              <td>{$licenseinfo.expires}</td>
              <td>{$licenseinfo.currentversion}{if $licenseinfo.updateavailable} <span class="textred"><strong>{$_ADMINLANG.license.updateavailable}</strong></span>{/if}</td>
            </tr>
          </tbody>
        </table>
        <div><strong>{$_ADMINLANG.global.staffonline}</strong>: {$adminsonline}</div>
      </div>
    </div>
    {foreach from=$widgets item=widget}
    <div class="homewidget" id="{$widget.name}">
      <div class="widget-header">{$widget.title}</div>
      <div class="widget-content"> {$widget.content} </div>
    </div>
    {/foreach} </div>
  <div class="homecolumn" id="homecol2"> </div>
</div>
<div id="geninvoices" title="{$_ADMINLANG.invoices.geninvoices}">
  <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 40px 0;"></span>{$_ADMINLANG.invoices.geninvoicessendemails}</p>
</div>
<div id="cccapture" title="{$_ADMINLANG.invoices.attemptcccaptures}">
  <p><span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 40px 0;"></span>{$_ADMINLANG.invoices.attemptcccapturessure}</p>
</div>
