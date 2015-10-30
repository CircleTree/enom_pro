try {
	function precise_round(num, decimals) {
		return Math.round(num * Math.pow(10, decimals)) / Math.pow(10, decimals);
	}

	var sortTldXHR = null;
	jQuery(function ($) {
		$(".no-js").hide();
		$("#generateinvoice").bind('click', function () {
			var $invoice_email = $("#invoice_email");
			if ($invoice_email.is(':animated')) {
				return;
			}
			if (!$("#generateinvoice").is(':checked')) {
				$invoice_email.slideUp();
			} else {
				$invoice_email.slideDown();
			}
		});

		/**
		 * Dialog Set up
		 */
		$("#create_order_dialog").dialog({
			width:       450,
			dialogClass: 'enom_pro_output',
			autoOpen:    false
		});
		$("#enom_pro_dialog").dialog({
			width:       640,
			height:      640,
			autoOpen:    false,
			dialogClass: 'enom_pro_output',
			modal:       true,
			close:       function () {
				if (!$(this).data('no-refresh')) {
					$("body").addClass('loading').append('<div class="ui-widget-overlay body-loader"></div>');
					$("#enom_pro_pricing_table").find("input").attr('disabled', true);
					window.location.reload();
				}
			}
		});

		/**
		 * global Bootstrap JS
		 */
		$('.ep_tt').tooltip({
			container: 'body',
			placement: $(this).data('placement') && 'auto top'
		});
		if (typeof(jQuery.fn.popover) == "function") {
			$(".ep_pop").popover({
				trigger:   'hover',
				container: 'body'
			});
		}
		$(".ep_lightbox").on('click', function () {
			var $this = $(this), title = "", $dialog = $("#enom_pro_dialog");
			if ($this.data('title')) {
				title = $this.data('title');
			} else if ($this.attr('title')) {
				title = $this.attr('title');
			} else {
				title = 'eNom PRO';
			}

			if (title != "") {
				$dialog.dialog('option', 'title', title);
			}
			$dialog.dialog('open');
			var href = $this.attr('href');
			if ($this.data('target')) {
				href = $this.data('target');
			}
			if ($this.data('width')) {
				$dialog.dialog('option', 'width', $this.data('width')).dialog('option', "position", {
					my: "center",
					at: "center",
					of: 'body'
				});
			}
			if ($this.data('no-refresh')) {
				$dialog.data('no-refresh', true);
			} else {
				$dialog.data('no-refresh', false);
			}
			var $enomProDialogIFrame = $("#enom_pro_dialog_iframe");
			$enomProDialogIFrame.attr('src', href).on('load', function () {
				$enomProDialogIFrame.contents().find('#whmcsdevbanner').remove();
			});
			return false;
		});

		$('body').on("click", '.ui-widget-overlay', function () {
			$("#enom_pro_dialog").dialog("close");
		});

		$(".clearTLDSearch").on('click', function () {
			var $form = $(this).closest('form');
			$form.find('input[name=s]').val('').removeAttr('name');
			$form.trigger("submit");
		});

		$("#enom_pro_import_page").on('click', ".clear_search", function () {
			$('input[name=s][type=text]').val("");
			$("#search_form").trigger('submit');
			return false;
		});
		$(".bulkImport").on('submit reset recalculate save', function (e) {
			if (e.type == 'reset') {
				$(".clear_all").trigger('click');
				return true; //Allow browser to reset the form
			} else if (e.type == 'submit') {
				$(".clear_all").trigger('click');
				$(this).trigger('recalculate').trigger('save');
				return false;
			} else if (e.type == 'save') {
				var data = {
					min_markup_percent:       $('#percentMarkup').val(),
					min_markup_whole:         $("#wholeMarkup").val(),
					preferred_markup_percent: $("#preferredPercentMarkup").val(),
					preferred_markup_whole:   $("#preferredWholeMarkup").val(),
					round_to:                 $("#roundTo").val(),
					overwrite_whmcs:          ("on" === $("#overWriteWHMCS:checked").val() ? 'true' : 'false')
				};
				//Check if data has changed
				if (JSON.stringify(data) === JSON.stringify(enom_pro.lastSavedTLDPricing)) {
					return false;
				}
				//Cache data from last request
				enom_pro.lastSavedTLDPricing = data;
				//Do AJAX Save here
				$.ajax({
					url:  'addonmodules.php?module=enom_pro',
					data: $.extend({}, data, {action: 'save_tld_markup'})
				})
			} else if (e.type == 'recalculate') {
				var markup               = parseFloat($("#percentMarkup").val()) || 0,
						wholeMarkup          = parseFloat($("#wholeMarkup").val()) || 0,
						round                = parseFloat($("#roundTo").val()) || false,
						preferredMarkup      = parseFloat($("#preferredPercentMarkup").val()) || 0,
						preferredWholeMarkup = parseFloat($("#preferredWholeMarkup").val()) || 0,
						doRound              = (round != -1),
						newPriceDouble       = 0,
						$elems               = enom_pro.getTLDInputSet();
				$.each($elems, function (k, value) {
					//If min. is lt preferred, use preferred, else use minimum
					var $elem             = $(value),
							price             = parseFloat($elem.data('price')),
							newMinPrice       = price * ( 1 + (markup / 100)) + wholeMarkup,
							newPreferredPrice = price * ( 1 + (preferredMarkup / 100)) + preferredWholeMarkup,
							newMinPriceDouble = (newMinPrice < newPreferredPrice) ? newPreferredPrice : newMinPrice;
					newMinPriceDouble = Math.ceil(newMinPriceDouble * 100) / 100;
					var newPriceString   = newMinPriceDouble.toFixed(2),
							priceArray       = newPriceString.split("."),
							thisDollarAmount = parseFloat(priceArray[0]),
							thisCentAmount   = parseFloat(priceArray[1]);
					//Is Rounding enabled?
					if (doRound) {
						if (thisCentAmount >= round) {
							//Check if the decimal value is gte our rounding amount
							newPriceDouble = (thisDollarAmount + 1) + (round / 100);
						} else {
							newPriceDouble = thisDollarAmount + (round / 100);
						}
					} else {
						//No rounding.
						newPriceDouble = newMinPriceDouble;
					}
					$elem.val(newPriceDouble);
					if ($elem.data('year') == 1) {
						$elem.trigger('keyup');
					}
				});
			}
		});
		$(".savePricing").on('click', function () {
			$("#enom_pro_pricing_import").trigger('submit');
			return false;
		});
		$(".toggle_tld").on('click', function () {
			var $this     = $(this),
					tld       = $this.data('tld'),
					$input    = $("[data-tld='" + tld + "'][data-year=1]"),
					first_val = $input.val();
			if ("" == first_val || " " == first_val) {
				//Reset to defaul
				$.each($("[data-tld='" + tld + "']"), function (k, v) {
					$(v).val($(v).data('price'));
				});
			} else {
				//Clear
				$.each($("[data-tld='" + tld + "']"), function (k, v) {
					$(v).val('');
				});
			}
			$input.trigger('keyup');//Trigger our button handler
			return false;
		});
		var $years = $("[data-year=1]");
		if ($years.length > 0) {
			$years.on('keyup', function () {
				var $t           = $(this),
						tld          = $t.data('tld'),
						$thisTrigger = $(".toggle_tld[data-tld='" + tld + "']"),
						$action      = $('.tldAction[data-tld="' + tld + '"]');
				if ($t.val() == "") {
					$thisTrigger.html('Import eNom Pricing');
					var btnClass = 'btn-default';
					if ($action.data('whmcs')) {
						btnClass = 'btn-success';
					}
					$action.removeClass('btn-success btn-danger btn-default').addClass(btnClass);
				} else if ($t.val() == '-1.00') {
					$action.addClass('btn-danger').removeClass('btn-success btn-default');
				} else {
					$action.addClass('btn-success').removeClass('btn-danger btn-default');
					$thisTrigger.html('Clear Current Pricing');
				}
			});
		}
		$(".delete_tld").on('click', function () {
			var $this = $(this);
			$("[data-tld='" + $this.data('tld') + "']").val('-1.00').trigger('keyup');
			return false;
		});
		$(".mult_row").on('click', function () {
			var tld = $(this).data('tld');
			var val = $('input[data-tld="' + tld + '"][data-year=1]').val();
			if (val == "") {
				val = prompt('Please enter the 1-year price to multiply for this row', 9.99);
			}
			$.each($("[data-tld='" + tld + "']"), function (k, v) {
				var $elem = $(v), cell_val = precise_round(val * $elem.data('year'), 2);
				$elem.val(cell_val).trigger('keyup');
			});
			return false;
		});
		$("#enom_pro_pricing_import").on('submit', function () {
			$(".myPrice", '#enom_pro_pricing_import').each(function (k, v) {
				var val = $(v).val();
				if (val == '0.00' || val == "" || parseInt(val) === 0) {
					$(v).removeAttr('value').removeAttr('name');
				}
			});
			return true;
		});

		$(".clear_all").on('click', function () {
			$('[data-price]').val('');
			$("[data-price][data-year=1]").trigger('keyup');
			return false;
		});
		$(".deleteFromWHMCS").on("click", function () {
			$(".delete_tld").trigger("click");
			$(".clearDropdown").dropdown('toggle');
			return false;
		});

		$("[data-alert]").tooltip({
			title:     'Never show this message',
			container: 'body',
			placement: 'left'
		}).closest('.alert').on('close.bs.alert', function () {
			var $alert = $(this).find('[data-alert]');
			var alertID = $alert.data('alert');
			$alert.tooltip('hide');
			var alertData = {
				action: 'dismiss_alert',
				alert:  alertID
			};
			$.ajax({
				url:     'addonmodules.php?module=enom_pro',
				data:    alertData,
				success: function () {

				}
			});
		});

		var slide_time = $(".slideup").data('timeout');
		if (!slide_time) {
			slide_time = 2000;
		} else {
			slide_time = slide_time * 1000;
		}
		setTimeout(function () {
			$(".slideup").slideUp(1000);
		}, slide_time);
		var $news = $("#enom_pro_changelog");
		if ($news.length > 0) {
			$news.append(enom_pro.loadingString);
			$.ajax({
				url:      document.location.protocol +
									"//ajax.googleapis.com/ajax/services/feed/load?v=1.0&num=4&callback=?&q=" +
									encodeURIComponent("http://mycircletree.com/client-area/knowledgebaserss.php?id=43"),
				dataType: "json",
				success:  function (data) {
					$news.empty();
					var str = "";
					/** @namespace data.responseData.feed.entries */
					$.each(data.responseData.feed.entries, function (k, entry) {
						str += "<h4><a target=\"_blank\" href=\"" +
								entry.link +
								"\" title=\"View " +
								entry.title +
								" on our Website\">" +
								entry.title +
								"</a></h4><p>" +
								entry.content +
								"<a class=\"button button-mini\" style=\"float:right;\" target=\"_blank\" href=\"" +
								entry.link +
								"\">Read more...</a></p>";
					});
					str +=
							"<a class=\"alignright\" href=\"http://mycircletree.com/client-area/knowledgebase.php?action=displayarticle&id=43\" target=\"_blank\">" +
							"View Changelog</a>";
					$(str).appendTo($news);
				}
			});
		}
		var $betaLog = $("#enom_pro_beta_changelog");
		if ($betaLog.length > 0) {
			$betaLog.on('ep.load', function () {
				var $betaLogUL = $("<ul></ul>");
				$.ajax({
					data:     {action: 'get_beta_log'},
					dataType: 'json',
					success:  function (data) {
						$betaLog.empty();
						$betaLogUL.appendTo($betaLog);
						var newer = 'newer', nextIsOlder = false;
						$.each(data, function (k, value) {
							//Value has properties: date_iso, date (timestamp), sha, subject
							var badge = 'label label-info';
							if (nextIsOlder) {
								newer = 'older';
							}
							/** @namespace value.sha */
							/** @namespace value.relative_date */
							/** @namespace value.subject */
							if (enom_pro.version.search(value.sha) > -1) {
								badge += ' current-version';
								nextIsOlder = true;
							}
							var revString = '<span class="' +
									badge +
									' sha">' +
									value.sha +
									' <span class="enom-pro-icon-code-fork enom-pro-icon"></span></span>' +
									'<span class="label label-inverse date">' +
									value.relative_date +
									'</span>' +
									'<span class="betaLogItem">' +
									value.subject +
									'</span>';
							$betaLogUL.append('<li class="' + newer + '" data-hash="' + value.sha + '">' + revString + '</li>')
						});
					}
				});
			});
		}

		$(".enom_pro_output").on("click", ".enom_stat_button .btn", function () {
			if ($(this).hasClass("disabled")) {
				return false;
			}
			var tab = $(this).data("tab");
			if (!tab) {
				return true;
			}
			var loader = $(this).closest(".enom_stat_button").find(".enom_pro_loader");
			loader.removeClass('hidden');
			$.ajax({
				url:      $(this).attr("href"),
				success:  function (data) {
					$("#enom_pro_" + tab).html(data);
				},
				complete: function () {
					loader.addClass('hidden');
				},
				error:    function (xhr) {
					alert(xhr.responseText);
				}
			});
			return false;
		}).on('click', '.load_more', function () {
			var button = $(this), row = button.closest('tr'), loader = button.closest('td').find('.enom_pro_loader');
			loader.removeClass('hidden');
			var ajaxUrl = $(this).attr('href');
			$.ajax({
				url:     ajaxUrl,
				success: function (data) {
					$(".domain-widget-response tbody").append(data);
					button.add(row).hide();
					$(".ep_tt").tooltip();
					loader.remove();
				},
				error:   function (xhr) {
					var errString = '<tr><td colspan="7">' +
							'<div class="alert alert-danger">' +
							xhr.responseText +
							'</div>' +
							'</td>';
					$(".domain-widget-response tbody").append(errString);
					loader.remove();
				}
			});
			return false;
		});
		if ($(".doIPFetch").length > 0) {
			$.getJSON("http://www.telize.com/jsonip?callback=?", function (json) {
				/** @namespace json.ip */
				$(".doIPFetch").html('<input value="' +
														 json.ip +
														 '" onclick="this.select();"/>').removeClass('enom_pro_loader');
			});
		}
		$(".filePathToggle").on('click', function () {
			var $this = $(this), filepath = $this.data('path'), size = filepath.length + 7;
			$this.hide();
			$this.after('<input type="text" name="file[]" onclick="this.select();" size="' +
									size +
									'" value="' +
									filepath +
									'" />');
			return false;
		});
		enom_pro = $.extend(enom_pro, {
			upgradeSessionKey:          'dismissEnomProUpgrade',
			$betaLog:                   jQuery("#enom_pro_beta_changelog"),
			$upgradeAlert:              jQuery('#upgradeAlert'),
			$upgradeAlertSidebar:       $(".upgradeAlertHidden"),
			loadingString:              "<div class=\"enom_pro_loader\"></div>",
			init:                       function () {
				this.initAjaxHandler();
				if (this.isUpgradeAlertHidden()) {
					this.hideUpgradeAlert();
				} else {
					this.showUpgradeAlert();
				}
				this.$upgradeAlert.on('close.bs.alert', function (e) {
					//Stop Bootstrap from removing it from the DOM
					e.preventDefault();
					enom_pro.hideUpgradeAlert();
				});
			},
			initTLDSortPage:            function () {
				var $loader = $(".enom_pro_loader");
				$loader.removeClass('hidden');
				this.ajaxLoadJS('jquery.ajaxq.js').done(function () {
					$loader.addClass('hidden');
					$(".ep_sortable").sortable({
						placeholder: "sortable-placeholder",
						update:      function (e, ui) {
							$loader.removeClass('hidden');
							var sorted = $(this).sortable('toArray');
							$.ajaxq.abort('sort_tlds');
							$.ajaxq('sort_tlds', {
								url:     'addonmodules.php?module=enom_pro',
								method: 'POST',
								data:    {
									'action': 'sort_domains',
									'order':  sorted
								},
								success: function () {
									$loader.addClass('hidden');
								},
								error:   function (xhr) {
									alert('Server error saving order: ' + xhr.responseText);
								}
							});
						}
					});
				});

			},
			initAjaxHandler:            function () {
				$(".ep_ajax").on('click', function () {
					var $elem = $(this), origTitle = $elem.text();
					$elem.addClass('disabled').text('Please wait...');
					$.ajax({
						url:     $elem.attr('href'),
						success: function (message) {
							$elem.text(message);
							setTimeout(function () {
								$elem.text(origTitle).removeClass('disabled');
							}, 2000);
						}//TODO error callback handling
					});
					return false;
				});
			},
			/**
			 * Filters the TLD pricing import view result set
			 * @returns {Array}
			 */
			getTLDInputSet:             function () {
				var checks   = $(".tldCheck").filter(':checked'),
						inputSet = [];
				$.each(checks, function (k, v) {
					var tld     = $(v).prop('name'),
							filter  = $('#overWriteWHMCS').prop('checked') ? '' : ', [data-whmcs]',
							$inputs = $("[data-tld='" + tld + "']").filter(':not(a, .btn' + filter + ')');
					inputSet = $.merge(inputSet, $inputs);
				});
				return inputSet;
			},
			/**
			 * @property
			 */
			pricingImportSaveTimeout:   0,
			/**
			 * @constant int SAVE_INTERVAL How long to wait after a key-press before sending result to server
			 */
			SAVE_INTERVAL:              500,
			/**
			 * @constant int SAVE_INTERVAL_CLICK How long to wait after a click before sending result to server
			 */
			SAVE_INTERVAL_CLICK:        750,
			openBulkPricingEditor:      function () {
				this.showBulkPricingTurboEditor();
				this.setLocalStorage('openBulkPricingEditor', true);
			},
			closeBulkPricingEditor:     function () {
				this.hideBulkPricingTurboEditor();
				this.setLocalStorage('openBulkPricingEditor', false);
			},
			showBulkPricingTurboEditor: function () {
				$(window).on('keyup.ep', function (e) {
					if (27 == e.keyCode) {
						//Press ESC to close the editor
						enom_pro.closeBulkPricingEditor();
					}
				});
				$("#enom_pro_pricing_import_page").addClass("fixedBulk");
				$('html, body').css({
					'overflow': 'hidden',
					'height':   '100%'
				});
			},
			hideBulkPricingTurboEditor: function () {
				$(window).off('keyup.ep');//remove ESC to close the editor
				$("#enom_pro_pricing_import_page").removeClass("fixedBulk");
				$('html, body').css({
					'overflow': 'auto',
					'height':   'auto'
				});
			},
			/**
			 * TLD Pricing Import Page
			 */
			initPricingImport:          function () {
				this.getLocalStorage('openBulkPricingEditor') === 'true' ? this.showBulkPricingTurboEditor() : null;
				$('.close-bulk-editor').on('click', function () {
					enom_pro.closeBulkPricingEditor();
				});
				$('.open-bulk-editor').on('click', function () {
					enom_pro.openBulkPricingEditor();
					return false;
				});
				$('.dropdown-toggle').dropdown();
				$('.tldAction').on('click', function () {
					var $check = $(this).closest('td').find('input');
					if ($check.prop('checked')) {
						$check.trigger('click');
					} else {
						$check.trigger('click');
					}
				});

				$('.toggleAllTLDCheckboxes').on('click', function () {
					var $toggler = $(this);
					if ($toggler.data('checked')) {
						//Last click was to check all
						//This click will un-check all
						$('.tldCheck:checked').trigger('click');
						$toggler.data('checked', false);
					} else {
						//Last click was to un-check all
						//This click will check all
						$('.tldCheck:not(":checked")').trigger('click');
						$toggler.data('checked', true);
					}
					return false;
				});

				this.ajaxLoadJS('jquery.tableHover.js').done(function () {
					$("#enom_pro_pricing_table").tableHover({
						colClass:   "hover",
						headCols:   true,
						ignoreCols: [1]
					});
				}).fail(function (xhr) {
					console.error('Error loading JS:' + xhr.responseText);
				});
				this.ajaxLoadJS('jquery.ajaxq.js').done(function () {
					$('.tldCheck').on('change', function () {
						clearTimeout(enom_pro.pricingImportSaveTimeout);
						$.ajaxq.abort('tldCheck');
						enom_pro.pricingImportSaveTimeout = setTimeout(function () {
							var tldsCSV = '';//Store tlds in a CSV to save on our max_input_variables
							$.each($('.tldCheck'), function (key, value) {
								var $tldCheck = $(value),
										checked   = $tldCheck.prop('checked') ? '1' : '0';
								tldsCSV += $tldCheck.prop('name') + ';' + checked + ',';
							});
							$.ajaxq('tldCheck', {
								url:    enom_pro.adminurl,
								global: false,
								data:   {
									action: 'save_import_tlds',
									tlds:   tldsCSV
								}
							});
						}, enom_pro.SAVE_INTERVAL_CLICK);
					});
				}).fail(function (xhr) {
					console.error('Error loading JS:' + xhr.responseText);
				});
			},
			/**
			 * Domain import page
			 */
			initDomainImport:           function () {
				this.ajaxLoadJS('jquery.ajaxq.js').done(function () {

					var $importTableForm = $("#import_table_form"), whois_cache = Array, localStorage;
					$(document).on('ajaxComplete', function (e, xhr, settings) {
						if (-1 !== settings.url.indexOf('render_import_table')) {
							$(".domain_whois").trigger('getwhois');
						}
					});
					if (typeof (window.localStorage) == 'object') {
						localStorage = window.localStorage;
					} else {
						localStorage = false;
					}
					$("#search_form").on('submit', function () {
						var search = $(this).find('input[name=s][type=text]').val();
						$("input[name=s][type=hidden]").val(search);
						$importTableForm.trigger('submit');
						return false;
					});
					$("#filter_form").on('submit change', function () {
						$("input[name=start]").val(1);
						$("input[name=show_only]").val($(this).find('select').val());
						$importTableForm.trigger('submit');
						return false;
					});
					$("#per_page_form").on('submit change', function () {
						var $loader = $('.enom_pro_loader');
						$.ajax({
							url:        'addonmodules.php?module=enom_pro',
							data:       $(this).serialize(),
							beforeSend: function () {
								$loader.removeClass('hidden');
							},
							success:    function () {
								$("input[name=start]").val(1);
								$importTableForm.trigger('submit');
							}
						});
						return false;
					});
					$("#domains_target").on("click", ".pagination A", function () {
						if ($.ajaxq.isRunning('whois')) {
							var abort = confirm('This will abort fetching WHOIS results\n\nContinue?');
							if (abort) {
								$.ajaxq.abort('whois');
							} else {
								return false;
							}
						}
						var start = $(this).data('start');
						$("input[name=start]").val(start);
						$importTableForm.trigger('submit');
						return false;
					});
					$importTableForm.on('getwhois', ".domain_whois", function () {
						var $target = $(this), $loader = $target.find('.enom_pro_loader');
						var domain_name = $(this).data('domain');
						var data = false;
						if (localStorage && localStorage.getItem(domain_name)) {
							var string = localStorage.getItem(domain_name);
							data = JSON.parse(string);
						} else if (whois_cache[domain_name]) {
							data = whois_cache[domain_name];
						}
						if (data) {
							do_whois_results($target, data);
							return false;
						}
						$.ajaxq('whois', {
							url:        enom_pro.adminurl,
							global:     false,
							data:       {
								action: 'get_domain_whois',
								domain: domain_name
							},
							beforeSend: function () {
								$loader.removeClass('hidden');
							},
							success:    function (data) {
								if (localStorage) {
									try {
										var string = JSON.stringify(data);
										localStorage.setItem(domain_name, string);
									} catch (e) {
										whois_cache[domain_name] = data;
									}
								} else {
									whois_cache[domain_name] = data;
								}
								do_whois_results($target, data);
							},
							complete:   function (xhr) {
								if (xhr.statusText == 'abort') {
									do_whois_results($target, {"error": "Cancelled"});
								}
								$loader.addClass('hidden');
							},
							error:      function (xhr) {
								var json = 'Error loading WHOIS';
								try {
									json = $.parseJSON(xhr.responseText)
								} catch (err) {
									json = xhr.responseText;
								}
								do_whois_results($target, {
									"error": json
								});
							}
						});
					});
					window.onbeforeunload = function () {
						if (jQuery.ajaxq.isRunning('whois')) {
							return 'Fetching WHOIS still in progress\n' +
									'Abort?';
						}
						jQuery.ajaxq.abort('whois');
					};
					if (localStorage) {
						function getLabel() {
							return 'Clear LocalStorage (' + localStorage.length + ')';
						}

						$("#local_storage").on('refresh', function () {
							$(this).html('<a class="btn btn-info btn-xs" href="#">' +
													 getLabel() +
													 '<span class="enom-pro-icon enom-pro-icon-trash"></span></a>');
						}).on('click', '.btn', function () {
							localStorage.clear();
							$(this).find('a').html(getLabel());
							$importTableForm.trigger('submit');
							return false;
						});
					}
					function do_whois_results($target, data) {

						$("#local_storage").trigger('refresh');
						var $alert = $target.closest('.alert');
						/** @namespace data.email */
						if (data.email) {
							$alert.find('.create_order').data('email', data.email);
						}
						var $response = $target.find('.response');
						var label = data.error || ('WHOIS Email: <span class="whois-email">' + data.email + '</span>');
						$response.html(label);
						if (!data.error) {
							$alert.removeClass('alert-danger').addClass('alert-warning');
							$alert.find('.create_order').removeClass('btn-primary').addClass('btn-success');
						} else {
							$('<button class="btn btn-danger btn-xs"><span class="enom-pro-icon enom-pro-icon-warning"></span> Try Again?</button>')
									.on('click', function () {
												if (localStorage) {
													localStorage.removeItem($target.data('domain'));
												}
												$(this).addClass('disabled');
											}).appendTo($response);
						}
						$target.find('.enom_pro_loader').addClass('hidden');
					}

					var $message = $("#ajax_messages"), $process = $("#order_process"), last_domain = '';
					$process.hide();
					//Create Order
					$importTableForm.on('click', 'a.create_order', function () {
						var domain_name = $(this).data('domain');
						last_domain = domain_name;
						$("#import_next_button").hide();
						$("#domain_field").add('#domain_field2').val(domain_name);
						$("#create_order_dialog").dialog('open');
						var $invoiceEmail = $("#invoice_email");
						if ($("#generateinvoice").is(':checked') && $invoiceEmail.not(':visible')) {
							$invoiceEmail.show();
						}
						var $button = $(this), email = $button.data('email'), id_protect = $button.data('id-protect'), dns = $button.data('dns'), autorenew = $button.data('autorenew'), nextduedate = $button.data('nextduedate');
						if (email != "") {
							//Set the drop-down state
							var $clientSelect = $("#client_select");
							$clientSelect.select2("open");
							$clientSelect.data('select2').dropdown.$search.val(email).trigger('keyup')
						}
						$("[name=nextduedate]").val($button.data('nextduedate'));
						$("[name=nextduedatelabel]").val($button.data('nextduedatelabel'));
						$("[name=expiresdate]").val($button.data('expiresdate'));
						$("[name=expiresdatelabel]").val($button.data('expiresdatelabel'));
						toggle_checkbox($("#idprotection"), (id_protect == 1));
						toggle_checkbox($("#dnsmanagement"), (dns == 1));
						if (autorenew == 1) {
							$("#auto-renew-warning").show();
						} else {
							$("#auto-renew-warning").hide();
						}
						$message.add($process).hide();
						$process.slideDown(200);
						return false;
					});
					function toggle_checkbox($elem, checked) {
						if (checked) {
							$elem.attr('checked', true);
						} else {
							$elem.removeAttr('checked');
						}
					}

					$("#import_next_button").bind('click', function () {
						if ($(".create_order").length == 0) {
							$(".next a").trigger('click');
						} else {
							$($(".create_order")[0]).trigger('click');
						}
					});
					$("#create_order_form").bind('submit', function () {
						$message.removeClass('alert-danger alert-success').hide();
						$process.hide();
						var $loader = $(".enom_pro_loader", $(this));
						$loader.show();
						$("#import_next_button").hide();
						$.ajax({
							url:     'addonmodules.php?module=enom_pro',
							data:    $(this).serialize(),
							success: function (data) {
								if (data.success) {
									$process.hide();
									$message.addClass('alert-success');
									$importTableForm.trigger('submit');
									/** @namespace data.activated */
									var message = 'Created ' + (data.activated ? 'Active' : 'Pending') + ' Order #';
									if (data.activated) {
										/** @namespace data.domainid */
										/** @namespace data.orderid */
										message += data.orderid +
												'<a class="btn btn-xs btn-default" target="_blank" href="clientsdomains.php?domainid=' +
												data.domainid +
												'">View Domain</a>';
									} else {
										message += '<a class="btn btn-xs" target="_blank" href="orders.php?action=view&id=' +
												data.orderid +
												'">View Pending Order #' +
												data.orderid +
												'</a>';
									}
									/** @namespace data.invoiceid */
									if (data.invoiceid) {
										message += ' invoice #' +
												'<a class="btn btn-xs btn-default" target="_blank" href="invoices.php?action=edit&id=' +
												data.invoiceid +
												'">' +
												data.invoiceid +
												'</a>';
									}

									$message.html(message);

								} else {
									$message.addClass('alert-danger');
									$process.slideDown();
									$message.html(data.error);
								}
								$loader.hide();
								$message.slideDown();
							},
							error:   function (xhr, text) {
								$loader.hide();
								$message.addClass('alert-danger').html('WHMCS Error: ' + xhr.responseText).slideDown();
								$process.slideDown();
							}
						});
						return false;
					});

					$importTableForm.on('submit', function () {
						$.ajaxq.clear('whois');
						$(".enom_pro_loader").not('.small').removeClass('hidden');
						$("#domain_caches").hide();
						$.ajax({
							url:        'addonmodules.php?module=enom_pro',
							data:       $(this).serialize(),
							beforeSend: function () {
								$("#import_ajax_messages").addClass('hidden');
							},
							success:    function (data) {
								$("#import_next_button").show();
								$("#domains_target").html(data.html);
								/** @namespace data.cache_date */
								$(".domains_cache_time").html(data.cache_date);

							},
							error:      function (jqXHR, status) {
								$("#import_ajax_messages").html("Error: " + jqXHR.responseText).removeClass('hidden');
							},
							complete:   function () {
								$(".enom_pro_loader").addClass('hidden');
								$("#domain_caches").show();
							}
						});
						return false;
					}).trigger('submit');
				});//End ajax.done()
				this.ajaxLoadJS('select2.min.js').done(function () {
					$("#client_select").select2({
						ajax: {
							url:            enom_pro.adminurl + '&action=get_client_list',
							dataType:       'json',
							delay:          250,
							data:           function (params) {
								return {
									q:    params.term,
									page: params.page
								};
							},
							processResults: function (data, page) {
								return {
									results:    data.results,
									pagination: {
										more: data.more
									}
								};
							},
							cache:          true
						}
					});
				});
			},
			/**
			 *
			 * @param script filename in the /js directory to load
			 * @returns jQuery.Deferred use .done and .fail to handle cases
			 */
			ajaxLoadJS:                 function (script) {
				var url = enom_pro.adminurl + '&action=get_javascript&script=' + script;
				return $.getScript(url);
			},
			helpCacheKey:               'enom_pro_help',
			/**
			 * Public method for loading the help interface
			 */
			initHelpIndex:              function () {
				this.loadHelpIndex();
				this.initHelpSearch();
				this.initHelpDialog();
				this.initHelpEvents();
			},
			$helpDialog:                false,
			/**
			 * @access private
			 */
			initHelpDialog:             function () {
				this.$helpDialog = $(".helpDialog");
				this.$helpDialog.dialog({
					buttons:     {
						'Close': function () {
							$(this).dialog('close');
						}
					},
					height:      'auto',
					width:       '760px',
					dialogClass: 'enom_pro_output',
					maxHeight:   window.innerHeight,
					minWidth:    250,
					maxWidth:    760,
					autoOpen:    false,
					modal:       false
				});
			},
			initHelpEvents:             function () {
				$(".enom_pro_output").on('click', '.helpTrigger', function () {
					var help_id = $(this).data('help-id');
					enom_pro.loadHelpIDIntoDialog(help_id);
					return false;
				});
			},
			loadHelpIDIntoDialog:       function (help_id) {
				var $content = $("#helpDialogContent"), cachedContent = this.getHelpContent(help_id);
				this.$helpDialog.dialog('open');
				if (!cachedContent) {
					$content.html(enom_pro.loadingString);
					$.ajax({
						url:      document.location.protocol +
											"//ajax.googleapis.com/ajax/services/feed/load?v=1.0&num=1&callback=?&q=" +
											encodeURIComponent("http://mycircletree.com/client-area/knowledgebaserss.php?id=" + help_id),
						dataType: "json",
						success:  function (data) {
							var str = "", title;
							$.each(data.responseData.feed.entries, function (k, entry) {
								title = entry.title;
								str += "<div class=\"content\" >" + entry.content + "</div>";
							});
							cachedContent = {
								title: title,
								body:  str
							};
							enom_pro.setHelpContent(help_id, cachedContent);
							processHelp(cachedContent);
						}
					});
				} else {
					//Cached
					processHelp(cachedContent);
				}
				function processHelp(cachedContent) {
					$content.html(cachedContent.body.split('Â').join(''));
					enom_pro.$helpDialog.dialog('option', 'title', cachedContent.title);
					enom_pro.$helpDialog.dialog('option', "position", {
						my: "center",
						at: "center",
						of: window
					});
				}
			},
			/**
			 * Gets help content from LocalStorage
			 * @param id help id key
			 * @returns {*}
			 */
			getHelpContent:             function (id) {
				var help_key = this.helpCacheKey + id;

				if (this.support.localStorage() && window.localStorage.getItem(help_key)) {
					return JSON.parse(window.localStorage.getItem(help_key));
				}
				return false;
			},
			setHelpContent:             function (id, data) {
				var help_key = this.helpCacheKey + id;
				if (this.support.localStorage()) {
					window.localStorage.setItem(help_key, JSON.stringify(data));
				}
			},
			/**
			 * private method to fetch help
			 */
			loadHelpIndex:              function () {

				var homeHelpKey = 'Home', $searchField = $("#helpSearch"), search = $searchField.val(), doingSearch = false, $homeHelp = $("#homeHelpContent");
				if (search != "") {
					doingSearch = true;
					homeHelpKey += search;
				}
				if (enom_pro.getHelpContent(homeHelpKey)) {
					var str = enom_pro.getHelpContent(homeHelpKey);
					$homeHelp.empty();
					$(str).appendTo($homeHelp);
				} else {
					$homeHelp.html(enom_pro.loadingString);
					var feedURL = "http://mycircletree.com/client-area/knowledgebaserss.php?catid=11" + (
									enom_pro.isBeta ?
									"&rand=" +
									Math.random(1, 1000) : '');
					if (doingSearch) {
						feedURL += "&s=" + search;
					}

					var url = document.location.protocol +
							"//ajax.googleapis.com/ajax/services/feed/load?v=1.0&num=25&callback=?&q=" +
							encodeURIComponent(feedURL);
					$.ajax({
						url:      url,
						dataType: "json",
						success:  function (data) {
							$homeHelp.empty();
							str = "<ul>";
							if (data.responseData.feed.entries.length === 0) {
								str += '<li><div class="alert">No Search Results Found. Please try again.</div></li>'
							} else {
								$.each(data.responseData.feed.entries, function (k, entry) {
									var help_id = enom_pro.getParameterByName('id', entry.link);
									/** @namespace entry.contentSnippet */
									str += '<li>' +
											'<h4>' +
											'<a ' +
											'href="' +
											entry.link +
											'" ' +
											'class="helpTrigger" ' +
											'data-help-id="' +
											help_id +
											'">' +
											entry.title +
											'</a>' +
											'</h4>' +

											'<p class="snippet">' +
											entry.contentSnippet.split('Â').join('') +
											'</p>';
									str += '<a ' +
											'href="' +
											entry.link +
											'" ' +
											'class="helpTrigger readmore btn btn-sm btn-primary" ' +
											'data-help-id="' +
											help_id +
											'"' +
											'>' +
											'' +
											entry.title +
											'</a></li>';
								});
							}
							str += "</ul>";
							enom_pro.setHelpContent(homeHelpKey, str);
							$homeHelp.html(str);
						}
					});
				}
			},
			initHelpSearch:             function () {
				var $searchField = $('input[name=s]'), oldSearch = "", searchTimeout = null, $clearSearchButton = $('.searchWrap .enom-pro-icon-cancel-circle');
				$searchField.on('keyup', function () {
					var newSearch = $searchField.val();
					if (oldSearch === newSearch) {
						return false;
					}
					if ($searchField.val().length > 1) {
						$clearSearchButton.removeClass('hidden');
						if (searchTimeout != null) {
							clearTimeout(searchTimeout);
						}
						searchTimeout = setTimeout(enom_pro.loadHelpIndex, 500);
						oldSearch = newSearch;
					} else if ($searchField.val() == "") {
						//Restore defaults
						$clearSearchButton.addClass('hidden');
						clearTimeout(searchTimeout);
						enom_pro.loadHelpIndex();
					}
				}).on('clearSearch', function () {
					$searchField.val('');
					$searchField.trigger('keyup');
				});
				$clearSearchButton.on('click', function () {
					$searchField.trigger('clearSearch');
					return false;
				});

			},
			getHelpCacheCount:          function () {
				var helpCount = 0;
				jQuery.each(window.localStorage, function (key, value) {
					if (typeof key !== "string") {
						key = window.localStorage.key(key);
					}
					if (-1 != key.indexOf(enom_pro.helpCacheKey)) {
						helpCount++;
					}
				});
				return helpCount;
			},
			clearHelpCache:             function () {
				if (window.localStorage) {
					var cleared = 0;
					jQuery.each(window.localStorage, function (key, value) {
						if (typeof key !== "string") {
							key = window.localStorage.key(key);
						}
						if (null !== key && -1 !== key.indexOf(enom_pro.helpCacheKey)) {
							cleared++;
							window.localStorage.removeItem(key);
						}
					});
					return cleared;
				}
				return false;
			},
			lastSavedTLDPricing:        {
				min_markup_percent:       $('#percentMarkup').val(),
				min_markup_whole:         $("#wholeMarkup").val(),
				preferred_markup_percent: $("#preferredPercentMarkup").val(),
				preferred_markup_whole:   $("#preferredWholeMarkup").val(),
				round_to:                 $("#roundTo").val(),
				overwrite_whmcs:          ("on" === $("#overWriteWHMCS:checked").val() ? 'true' : 'false')
			},
			/**
			 * Shows changelog & hides sidebar notification
			 */
			showUpgradeAlert:           function () {
				this.deleteHideAlert();
				this.show(this.$upgradeAlert);
				this.$betaLog.trigger('ep.load');
				this.hide(this.$upgradeAlertSidebar);
			},
			/**
			 * Hides the changelog and restores the sidebar notification
			 */
			hideUpgradeAlert:           function () {
				if (this.support.localStorage()) {
					window.sessionStorage.setItem(this.upgradeSessionKey, true);
				}
				this.show(this.$upgradeAlertSidebar);
				this.hide(this.$upgradeAlert);
			},
			deleteHideAlert:            function () {
				if (this.support.localStorage()) {
					window.sessionStorage.removeItem(this.upgradeSessionKey);
				}
			},
			isUpgradeAlertHidden:       function () {
				if (!this.support.localStorage()) {
					return false;
				}
				return window.sessionStorage.getItem(this.upgradeSessionKey);
			},
			show:                       function ($item) {
				$item.removeClass('hidden').show();
			},
			hide:                       function ($item) {
				$item.addClass('hidden').hide();
			},
			/**
			 * Parses a URI for query variables
			 * @param name GET variable to fetch
			 * @param url URI to parse
			 * @returns {string}
			 */
			getParameterByName:         function (name, url) {
				name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
				var regexS = "[\\?&]" + name + "=([^&#]*)";
				var regex = new RegExp(regexS);
				var results = regex.exec(url);
				if (results === null) {
					return "";
				} else {
					return decodeURIComponent(results[1].replace(/\+/g, " "));
				}
			},
			/**
			 * @constant
			 */
			LOCAL_STORAGE_PREFIX:       'enom_pro_',
			/**
			 * Gets an item from local storage if supported
			 * @param key
			 * @returns {boolean} false of failure
			 */
			getLocalStorage:            function (key) {
				if (this.support.localStorage()) {
					return window.localStorage.getItem(this.getLocalStorageKey(key));
				}
				return false;
			},
			/**
			 *
			 * @param key
			 * @param value
			 * @returns {boolean} true on set, false on fail
			 */
			setLocalStorage:            function (key, value) {
				if (this.support.localStorage()) {
					window.localStorage.setItem(this.getLocalStorageKey(key), value);
					return true;
				}
				return false;
			},
			/**
			 * @private
			 * @param unPrefixedKey {string}
			 * @returns {string}
			 */
			getLocalStorageKey:         function (unPrefixedKey) {
				return this.LOCAL_STORAGE_PREFIX + unPrefixedKey;
			},
			/**
			 * Simple feature detection
			 * @property
			 */
			support:                    {
				/**
				 * @method localStorage
				 * @returns {boolean}
				 */
				localStorage: function () {
					try {
						return 'localStorage' in window && window['localStorage'] !== null;
					} catch (e) {
						return false;
					}
				}
			}

		});
		enom_pro.init();
	}); //end jQuery Ready
} catch (err) {
	alert('Enom PRO JS Error: ' + err);
}