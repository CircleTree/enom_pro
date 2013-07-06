jQuery(function($) {
	var $message = $("#ajax_messages"),
	$process = $("#order_process");
	$process.hide(),
	last_domain = '';
	$("#import_table_form").on('click', 'a.create_order', function  () {
		var domain_name = $(this).data('domain');
		last_domain = domain_name;
		$("#domain_field").add('#domain_field2').val(domain_name); 
		$("#create_order_dialog").dialog('open');
		var $button = $(this),
		email = $button.data('email');
		if (email != "") {
			$("option[data-email='"+email+"']").attr('selected', true);
		}
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
	$("#import_table_form").on('ajaxComplete', function  (e, xhr, settings) {
		$(".domain_whois").trigger('getwhois'); 
	});
	var whois_cache = Array;
	if (typeof(window.localStorage) == 'object') {
		var localStorage = window.localStorage;
	} else {
		var localStorage = false;
	}
	$("#import_table_form").on('getwhois', ".domain_whois", function  (e) {
		var $target = $(this);
		var domain_name = $(this).data('domain');
		if (localStorage && localStorage.getItem(domain_name)){
			var string = localStorage.getItem(domain_name);
			var data = JSON.parse(string); 
		} else if (whois_cache[domain_name]) {
			var data = whois_cache[domain_name];
		}
		if (data) {
			do_whois_results($target, data);
			return data;
		}
		$.ajax({
			url: 'addonmodules.php?module=enom_pro',
			global: false,
			data: {action: 'get_domain_whois', domain: domain_name },
			success: function  (data) {
				do_whois_results($target, data);
				if (localStorage) {
					var string = JSON.stringify(data);
					localStorage.setItem(domain_name, string);
				} else {
					whois_cache[domain_name] = data;
				}
			}
		});
		return false;
	});
	if (localStorage) {
		function  getLabel() 
		{
			return 'Clear LocalStorage (' + localStorage.length + ')';
		}
		$("#local_storage").append('<a class="btn btn-info btn-mini" href="#">'+getLabel()+'</a>').on('click', function  () {
					localStorage.clear();
					$(this).find('a').html(getLabel());
					$("#import_table_form").trigger('submit');
					return false;
				});		 
	}
	function  do_whois_results ($target, data) 
	{
		$target.closest('.alert').find('.create_order').data('email', data.email);
		$target.find('.enom_pro_loader').addClass('hidden');
		$target.find('.response').html(data.email);
	}
	var $loader = $(".enom_pro_loader"); 
	$("#create_order_form").bind('submit', function  () {
		$message.removeClass('alert-error alert-success').hide();
		$process.hide();
		$.ajax({
			url: 'addonmodules.php?module=enom_pro',
			data: $(this).serialize(),
			success: function  (data) {
					if (data.success) {
						$process.hide();
						$message.addClass('alert-success');
						$("#import_table_form").trigger('submit').on('ajaxComplete', function  () {
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
	$("#import_table_form").on('submit', function  () {
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
	$("#import_table_form").submit(); 
	$("#domains_target").on("click", ".pager A", function  () {
		$("input[name=start]").val($(this).data('start'));
		$("#import_table_form").trigger('submit');
		return false;
	});
	$("#filter_form").on('submit change', function  () {
		$("input[name=start]").val(1);
		$("input[name=show_only]").val($(this).find('select').val()); 
		$("#import_table_form").trigger('submit');
		return false;
	});
	setTimeout(function  () {
		$(".slideup").slideUp(1000) 
	}, 2000);
	$("#per_page_form").on('submit change', function  () {
		var $loader = $('.enom_pro_loader');
		$.ajax({
			url: 'addonmodules.php?module=enom_pro',
			data: $(this).serialize(),
			beforeSend: function  () 
			{
				$loader.removeClass('hidden');
			},
			success: function  () 
			{
				$("input[name=start]").val(1);
				$("#import_table_form").trigger('submit');
			}
		});
		return false;		
	});
});