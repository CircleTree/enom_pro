jQuery(function($) {
	var $message = $("#ajax_messages"),
	$process = $("#order_process");
	$process.hide(),
	last_domain = '';
	$("#import_table").on('click', 'a.create_order', function  () {
		var domain_name = $(this).data('domain');
		last_domain = domain_name;
		$("#domain_field").add('#domain_field2').val(domain_name); 
		$("#create_order_dialog").dialog('open');
		$message.add($process).hide();
		$process.slideDown(200);
		return false;
	});
	$("#generateinvoice").bind('click', function  () {
		var $invoice_email = $("#invoice_email");
		if ($invoice_email.is(':animated'))
			return;
		if (! $("#generateinvoice").is(':checked'))
			$invoice_email.slideUp();
		else 
			$invoice_email.slideDown();
	});
	$("#create_order_dialog").dialog({
		width: 450,
		autoOpen: false,
	});
	var $loader = $(".enom_pro_loader"); 
	$("#create_order_form").bind('submit', function  () {
		$message.removeClass('alert-error alert-success').hide();
		$process.hide();
		$loader.show();
		$.ajax({
			url: 'addonmodules.php?module=enom_pro',
			data: $(this).serialize(),
			success: function  (data) {
					if (data.success) {
						$process.hide();
						$message.addClass('alert-success');
						$loader.show();
						$("#import_table").load(window.location.href + ' #import_table', function  () {
							$loader.hide();
							$("#create_order_dialog").dialog('close');
							var $new_elem = $("[data-domain='"+last_domain+"']").closest('.alert');
							$new_elem.removeClass('alert-success');
							setTimeout(function  () {
								$new_elem.addClass('alert-success')
							},250);
							setTimeout(function  () {
								$new_elem.removeClass('alert-success')
							},500);
							setTimeout(function  () {
								$new_elem.addClass('alert-success')
							},750);
						});
					} else {
						$loader.hide();
						$message.addClass('alert-error');
						$process.slideDown(); 
					}
					$message.html(data.message).slideDown();
			},
			error: function  (xhr, text) {
				$loader.hide();
				$message.addClass('alert-error').html('WHMCS Error: ' + xhr.responseText).slideDown();
				$process.slideDown();
			}
		});
		return false;
	});
	$("#per_page_form").submit(function  () {
		var $loader = $("#per_page_form").find('.enom_pro_loader');
		$.ajax({
			url: 'addonmodules.php?module=enom_pro',
			data: $(this).serialize(),
			beforeSend: function  () 
			{
				$loader.removeClass('hidden');
			},
			success: function  () 
			{
				$("#import_table_form").trigger('submit');
			}
		});
		return false;		
	});
	if ($("#domains_target").length == 1) {
		$.ajax({
			data: {action: 'render_import_table'},
			success: function  (data) 
			{
				$(".enom_pro_loader").addClass('hidden'); 
				$("#domains_target").html(data); 
			}
		});
	} 
	$("#domains_target").on('submit', 'form', function  () {
		$(".enom_pro_loader").removeClass('hidden'); 
		$.ajax({
			url:'addonmodules.php?module=enom_pro', 
			data: $(this).serialize(),
			success: function  (data) 
			{
				$(".enom_pro_loader").addClass('hidden'); 
				$("#domains_target").html(data); 	
			}
		});
		return false;
	});
	$("#domains_target").on("click", ".pager A", function  () {
		$("input[name=start]").val($(this).data('start'));
		$("#import_table_form").trigger('submit');
		return false;
	});
});