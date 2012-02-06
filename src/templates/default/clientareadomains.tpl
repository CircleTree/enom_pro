{*
INSTALL INSTRUCTIONS
--ADD THE FOLLOWING CODE TO the CLIENTAREADOMAINS.tpl file in your active WHMCS template
*}
{if $enom_transfers}
	<h3>Pending Domain Transfers</h3>
		<table width="100%" border="0" align="center" cellpadding="10" cellspacing="0" class="zebra-striped">
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
				$("#enom_refresh").live('submit', function  () {
					var $target = $("#enom_target");
					var data = $(this).serialize();
					$target.html('{/literal}<tr><td colspan="4" align="center" >{$LANG.loading}</td></tr>{literal}');
					$.ajax({
						url:$(this).attr('action'), 
						data: data, 
						success: function  (json) {
							console.log(json);
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