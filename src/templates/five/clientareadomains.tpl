{include file="$template/pageheader.tpl" title=$LANG.clientareanavdomains desc=$LANG.clientareadomainsintro}

<div class="searchbox">
    <form method="post" action="clientarea.php?action=domains">
        <div class="input-append">
            <input type="text" name="q" value="{if $q}{$q}{else}{$LANG.searchenterdomain}{/if}" class="input-medium appendedInputButton" onfocus="if(this.value=='{$LANG.searchenterdomain}')this.value=''" /><button type="submit" class="btn btn-info">{$LANG.searchfilter}</button>
        </div>
    </form>
</div>

{if $enom_transfers}
    <h3>Pending Domain Transfers</h3>
        <table width="100%" border="0" align="center" cellpadding="10" cellspacing="0" class="table-striped">
        <thead>
            <tr><th>Domain Name</th><th>Domain Orders</th><th>Manage</th></tr>
        </thead>
    {foreach item=domain key=id from=$enom_transfers}
    <tbody id="enom_target">
        <tr>
        <td colspan="4" align="center" >
            {$LANG.loading}
        </td>
        </tr>
    </tbody>
    {/foreach}
        </table>
        <form action="{$smarty.server.PHP_SELF}" id="enom_refresh" method="get">
            <input type="hidden" name="action" value="domains"/>
            <input type="hidden" name="refresh" value="true"/>
            <input type="submit" class="btn" value="Refresh Transfer Statuses"/>
        </form>
        <script type="text/javascript">
        {literal}
            jQuery(document).ready(function($){
                $("#enom_refresh").on('submit', function  () {
                    var $target = $("#enom_target");
                    var data = $(this).serialize();
                    $target.html('{/literal}<tr><td colspan="4" align="center" >{$LANG.loading}</td></tr>{literal}');
                    $.ajax({
                        url:$(this).attr('action'), 
                        data: data, 
                        success: function  (json) {
                            $target.empty();
                            //Loop through each domain pending transfer 
                            $.each(json, function  (k,domain) {
                                //Loop through each order
                                var statuses = '<table class="datatable" width="100%" border="0" cellspacing="0" cellpadding="0">';
                                statuses += '<tr><th>Status</th><th>Order Date</th></tr>';
                                $.each(domain.statuses, function  (k,status) {
                                    statuses += '<tr>';
                                    statuses += '<td>'+status.statusdesc+'</td>';
                                    statuses += '<td>'+status.orderdate+'</td>';
                                    statuses += '</tr>';
                                });
                                statuses += '</table>';
                                $("<tr><td>"+domain.domain+"</td>"+
                                "<td>"+statuses+"</td>" +
                                "<td><a class=\"btn small\" href=\"{/literal}{$smarty.server.PHP_SELF}{literal}?action=domaindetails&id="+domain.id+"\">{/literal}{$LANG.managedomain}{literal}</a></td>" +
                                "</tr>").appendTo($target); 
                            });
                        }, 
                        error: function  (xhr) {
                            alert(xhr.responseText);
                        }
                    })
                    return false;
                }).trigger('submit'); 
            })
        {/literal}
        </script>
{/if}

<div class="resultsbox">
<p>{$numitems} {$LANG.recordsfound}, {$LANG.page} {$pagenumber} {$LANG.pageof} {$totalpages}</p>
</div>

<div class="clear"></div>
{literal}
<script>
$(document).ready(function() {
    $(".setbulkaction").click(function(event) {
      event.preventDefault();
      $("#bulkaction").val($(this).attr('id'));
      $("#bulkactionform").submit();
    });
});
</script>
{/literal}
<form method="post" id="bulkactionform" action="clientarea.php?action=bulkdomain">
<input id="bulkaction" name="update" type="hidden" />

<table class="table table-striped table-framed">
    <thead>
        <tr>
            <th class="textcenter"><input type="checkbox" onclick="toggleCheckboxes('domids')" /></th>
            <th{if $orderby eq "domain"} class="headerSort{$sort}"{/if}><a href="clientarea.php?action=domains{if $q}&q={$q}{/if}&orderby=domain">{$LANG.clientareahostingdomain}</a></th>
            <th{if $orderby eq "regdate"} class="headerSort{$sort}"{/if}><a href="clientarea.php?action=domains{if $q}&q={$q}{/if}&orderby=regdate">{$LANG.clientareahostingregdate}</a></th>
            <th{if $orderby eq "nextduedate"} class="headerSort{$sort}"{/if}><a href="clientarea.php?action=domains{if $q}&q={$q}{/if}&orderby=nextduedate">{$LANG.clientareahostingnextduedate}</a></th>
            <th{if $orderby eq "status"} class="headerSort{$sort}"{/if}><a href="clientarea.php?action=domains{if $q}&q={$q}{/if}&orderby=status">{$LANG.clientareastatus}</a></th>
            <th{if $orderby eq "autorenew"} class="headerSort{$sort}"{/if}><a href="clientarea.php?action=domains{if $q}&q={$q}{/if}&orderby=autorenew">{$LANG.domainsautorenew}</a></th>
            <th>&nbsp;</th>
        </tr>
    </thead>
    <tbody>
{foreach key=num item=domain from=$domains}
        <tr>
            <td class="textcenter"><input type="checkbox" name="domids[]" class="domids" value="{$domain.id}" /></td>
            <td><a href="http://{$domain.domain}/" target="_blank">{$domain.domain}</a></td>
            <td>{$domain.registrationdate}</td>
            <td>{$domain.nextduedate}</td>
            <td><span class="label {$domain.rawstatus}">{$domain.statustext}</span></td>
            <td>{if $domain.autorenew}{$LANG.domainsautorenewenabled}{else}{$LANG.domainsautorenewdisabled}{/if}</td>
            <td>
                <div class="btn-group">
                <a class="btn" href="clientarea.php?action=domaindetails&id={$domain.id}"> <i class="icon-wrench"></i> {$LANG.managedomain}</a>
                {if $domain.rawstatus == "active"}
                <a class="btn dropdown-toggle" href="#" data-toggle="dropdown"><span class="caret"></span></a>
                <ul class="dropdown-menu">
                    {if $domain.managens}<li><a href="clientarea.php?action=domaindetails&id={$domain.id}#tab3"><i class="icon-globe"></i> {$LANG.domainmanagens}</a></li>{/if}
                    <li><a href="clientarea.php?action=domaincontacts&domainid={$domain.id}"><i class="icon-user"></i> {$LANG.domaincontactinfoedit}</a></li>
                    <li><a href="clientarea.php?action=domaindetails&id={$domain.id}#tab2"><i class="icon-globe"></i> {$LANG.domainautorenewstatus}</a></li>
                    <li class="divider"></li>
                    <li><a href="clientarea.php?action=domaindetails&id={$domain.id}"><i class="icon-pencil"></i> {$LANG.managedomain}</a></li>
                </ul>
                {/if}
                </div>
            </td>
        </tr>
{foreachelse}
        <tr>
            <td colspan="7" class="textcenter">{$LANG.norecordsfound}</td>
        </tr>
{/foreach}
    </tbody>
</table>

<div class="btn-group">
<a class="btn btn-inverse" href="#" data-toggle="dropdown"><i class="icon-folder-open icon-white"></i> {$LANG.withselected}</a>
<a class="btn btn-inverse dropdown-toggle" href="#" data-toggle="dropdown"><span class="caret"></span></a>
<ul class="dropdown-menu">
    <li><a href="#" id="nameservers" class="setbulkaction"><i class="icon-globe"></i> {$LANG.domainmanagens}</a></li>
    <li><a href="#" id="autorenew" class="setbulkaction"><i class="icon-refresh"></i> {$LANG.domainautorenewstatus}</a></li>
    <li><a href="#" id="reglock" class="setbulkaction"><i class="icon-lock"></i> {$LANG.domainreglockstatus}</a></li>
    <li><a href="#" id="contactinfo" class="setbulkaction"><i class="icon-user"></i> {$LANG.domaincontactinfoedit}</a></li>
    {if $allowrenew}<li><a href="#" id="renew" class="setbulkaction"><i class="icon-repeat"></i> {$LANG.domainmassrenew}</a></li>{/if}
</ul>
</div></form>

{include file="$template/clientarearecordslimit.tpl" clientareaaction=$clientareaaction}

<div class="pagination">
    <ul>
        <li class="prev{if !$prevpage} disabled{/if}"><a href="{if $prevpage}clientarea.php?action=domains{if $q}&q={$q}{/if}&amp;page={$prevpage}{else}javascript:return false;{/if}">&larr; {$LANG.previouspage}</a></li>
        <li class="next{if !$nextpage} disabled{/if}"><a href="{if $nextpage}clientarea.php?action=domains{if $q}&q={$q}{/if}&amp;page={$nextpage}{else}javascript:return false;{/if}">{$LANG.nextpage} &rarr;</a></li>
    </ul>
</div>

</form>

<br />
<br />
