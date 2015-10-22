{if $id}
<h1>SRV Records for {$domain.domain}</h1>
<div class="alert alert-info" style="display:none;position:absolute;top:25px;right:30px;" id="ajax_loading">Loading</div>
<form method="POST" action="{$smarty.server.PHP_SELF}" id="srv_form">
	<input type="submit" value="Save" class="btn" />
	<input type="hidden" name="action" value="save_srv" />
	<input type="hidden" name="id" value="{$id}" />
	<table class="table" >
		<thead>
			<tr>
				<th title="Service type of this record.">Service</th>
				<th title="Transport protocol for this record, such as TCP or UDP.">Protocol</th>
				<th title="Priority for this record. Lowest priority values are used first, working toward higher priority values when lower values are unavailable. Use this value to designate backup service.">Priority</th>
				<th title="Proportion of time to use this record. Records of equal priority are added and the total normalized to 100%.">Weight</th>
				<th title="Port to use for this service.">Port</th>
				<th title="Fully qualified domain name for this SRV record.">Hostname / Target</th>
				<th></th>
			</tr>
		</thead>
		<tbody>
			{foreach from=$records item=v}
				<tr>
					<td><input type="text" name="records[{$v}][service]" class="span2" {if $v == 1}placeholder="_voice"{/if}/></td>
					<td><input type="text" name="records[{$v}][protocol]" class="span2" {if $v == 1}placeholder="UDP"{/if}/></td>
					<td><input type="text" name="records[{$v}][priority]" class="span1" {if $v == 1}placeholder="1"{/if}/></td>
					<td><input type="text" name="records[{$v}][weight]" class="span1" {if $v == 1}placeholder="1"{/if}/></td>
					<td><input type="text" name="records[{$v}][port]" class="span1" {if $v == 1}placeholder="81"{/if}/></td>	
					<td><input type="text" name="records[{$v}][target]" class="span4" {if $v == 1}placeholder="domain.com"{/if}/>
						<input type="hidden" name="records[{$v}][hostid]" value="" />
					</td>
					<td><button data-id="{$v}" class="btn mini delete"><i class="icon-trash"></i></button></td>
				</tr>
			{/foreach}
		</tbody>
	</table>
	<input type="submit" value="Save" class="btn" />
</form>
<script type="text/javascript">
{literal}
jQuery(function($) {
	var $loader = $("#ajax_loading"),
	loading = $loader.html(),
	loader_timeout;
	$(".delete").click(function  () {
		var id = $(this).data('id'),
		keys = ['service', 'protocol', 'priority', 'weight', 'port', 'target'];
		//Don't allow deleting unset records
		if ($("input[name='records["+id+"][hostid]']").val() == "")
			return false;
		$.each(keys, function  (k, v) {
			$("input[name='records["+id+"]["+v+"]']").val("");
		});
		return true;
	});
	$("#srv_form").submit(function  (e) {
		$loader.html(loading).show(); 
		var data = $(this).serializeArray();
		$.ajax({
			data: data,
			beforeSend: function  () {
				$loader.removeClass('alert-error alert-info alert-success').addClass('alert-info');
				clearTimeout(loader_timeout);
			},
			success: function  (data) {
				$.each(data, function  (index, record) {
					index++; //JS object 0 indexed
					$.each(record, function  (key, value) {
						$("input[name='records["+index+"]["+key+"]']").val(value);  	
					});
				});
				$loader.addClass('alert-success').removeClass('alert-info').html('Done');
			},
			complete: function  () {
				loader_timeout = setTimeout(function  () {
					$loader.slideUp(300);
				}, 3000);
			},
			error: function  (xhr) {
				$loader.show().html(xhr.responseText).addClass('alert-error').removeClass('alert-info');
			}
		});
		return false;
	}).trigger('submit');
});
{/literal}
</script>
{else}
	<h1>Select domain</h1>
	<form method="GET" action="{$smarty.server.PHP_SELF}" class="form-inline input-append" >
		<select name="id">
  			{foreach from=$domains item=v key=k}
  				<option value="{$k}">{$v.domain}</option>
			{/foreach}
		</select>
		<input type="submit" value="Go" class="btn btn-success" />
	</form>
{/if}