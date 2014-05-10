try {
;(function(C){var A=function(Q){var S=Q.rows;var K=S.length;var P=[];for(var I=0;I<K;I++){var R=S[I].cells;var O=R.length;for(var H=0;H<O;H++){var N=R[H];var M=N.rowSpan||1;var J=N.colSpan||1;var L=-1;if(!P[I]){P[I]=[]}var E=P[I];while(E[++L]){}N.realIndex=L;for(var G=I;G<I+M;G++){if(!P[G]){P[G]=[]}var D=P[G];for(var F=L;F<L+J;F++){D[F]=1}}}}};var B=function(H){var E=0,F,D,G=(H.tHead)?H.tHead.rows:0;if(G){for(F=0;F<G.length;F++){G[F].realRIndex=E++}}for(D=0;D<H.tBodies.length;D++){G=H.tBodies[D].rows;if(G){for(F=0;F<G.length;F++){G[F].realRIndex=E++}}}G=(H.tFoot)?H.tFoot.rows:0;if(G){for(F=0;F<G.length;F++){G[F].realRIndex=E++}}};C.fn.tableHover=function(D){var E=C.extend({allowHead:true,allowBody:true,allowFoot:true,headRows:false,bodyRows:true,footRows:false,spanRows:true,headCols:false,bodyCols:true,footCols:false,spanCols:true,ignoreCols:[],headCells:false,bodyCells:true,footCells:false,rowClass:"hover",colClass:"",cellClass:"",clickClass:""},D);return this.each(function(){var N=[],M=[],J=this,F,K=0,O=[-1,-1];if(!J.tBodies||!J.tBodies.length){return }var G=function(U,X){var W,V,T,R,Q,S;for(T=0;T<U.length;T++,K++){V=U[T];for(R=0;R<V.cells.length;R++){W=V.cells[R];if((X=="TBODY"&&E.bodyRows)||(X=="TFOOT"&&E.footRows)||(X=="THEAD"&&E.headRows)){S=W.rowSpan;while(--S>=0){M[K+S].push(W)}}if((X=="TBODY"&&E.bodyCols)||(X=="THEAD"&&E.headCols)||(X=="TFOOT"&&E.footCols)){S=W.colSpan;while(--S>=0){Q=W.realIndex+S;if(C.inArray(Q+1,E.ignoreCols)>-1){break}if(!N[Q]){N[Q]=[]}N[Q].push(W)}}if((X=="TBODY"&&E.allowBody)||(X=="THEAD"&&E.allowHead)||(X=="TFOOT"&&E.allowFoot)){W.thover=true}}}};var L=function(R){var Q=R.target;while(Q!=this&&Q.thover!==true){Q=Q.parentNode}if(Q.thover===true){H(Q,true)}};var I=function(R){var Q=R.target;while(Q!=this&&Q.thover!==true){Q=Q.parentNode}if(Q.thover===true){H(Q,false)}};var P=function(T){var R=T.target;while(R&&R!=J&&!R.thover){R=R.parentNode}if(R.thover&&E.clickClass!=""){var Q=R.realIndex,U=R.parentNode.realRIndex,S="";C("td."+E.clickClass+", th."+E.clickClass,J).removeClass(E.clickClass);if(Q!=O[0]||U!=O[1]){if(E.rowClass!=""){S+=",."+E.rowClass}if(E.colClass!=""){S+=",."+E.colClass}if(E.cellClass!=""){S+=",."+E.cellClass}if(S!=""){C("td, th",J).filter(S.substring(1)).addClass(E.clickClass)}O=[Q,U]}else{O=[-1,-1]}}};var H=function(R,T){if(T){C.fn.tableHoverHover=C.fn.addClass}else{C.fn.tableHoverHover=C.fn.removeClass}var V=N[R.realIndex]||[],S=[],U=0,Q,W;if(E.colClass!=""){while(E.spanCols&&++U<R.colSpan&&N[R.realIndex+U]){V=V.concat(N[R.realIndex+U])}C(V).tableHoverHover(E.colClass)}if(E.rowClass!=""){Q=R.parentNode.realRIndex;if(M[Q]){S=S.concat(M[Q])}U=0;while(E.spanRows&&++U<R.rowSpan){if(M[Q+U]){S=S.concat(M[Q+U])}}C(S).tableHoverHover(E.rowClass)}if(E.cellClass!=""){W=R.parentNode.parentNode.nodeName.toUpperCase();if((W=="TBODY"&&E.bodyCells)||(W=="THEAD"&&E.headCells)||(W=="TFOOT"&&E.footCells)){C(R).tableHoverHover(E.cellClass)}}};A(J);B(J);for(F=0;F<J.rows.length;F++){M[F]=[]}if(J.tHead){G(J.tHead.rows,"THEAD")}for(F=0;F<J.tBodies.length;F++){G(J.tBodies[F].rows,"TBODY")}if(J.tFoot){G(J.tFoot.rows,"TFOOT")}C(this).bind("mouseover",L).bind("mouseout",I).click(P)})}})(jQuery);
var whois_xhrs = [];
function abort_whois_xhrs ()
{
	if (whois_xhrs.length > 0) {
    	jQuery.each(whois_xhrs, function  (k, v) {
    		if (typeof(v) == 'object') {
    			v.abort();
    		} else {
    			console.log('abort', v);
    		}
        });
	}
}
function precise_round(num,decimals){
	return Math.round(num*Math.pow(10,decimals))/Math.pow(10,decimals);
}
jQuery(function($) {
    $("#generateinvoice").bind('click', function() {
        var $invoice_email = $("#invoice_email");
        if ($invoice_email.is(':animated'))
            return;
        if (!$("#generateinvoice").is(':checked'))
            $invoice_email.slideUp();
        else
            $invoice_email.slideDown();
    });
    $("#create_order_dialog").dialog({
        width : 450,
        autoOpen : false,
    });
    $("#import_table_form").on('ajaxComplete', function(e, xhr, settings) {
        $(".domain_whois").trigger('getwhois');
    });
    var whois_cache = Array,
    localStorage;
    if (typeof (window.localStorage) == 'object') {
        localStorage = window.localStorage;
    } else {
        localStorage = false;
    }
    $(".mult_row").on('click', function  (){
    	var tld = $(this).data('tld');
    	var val = $(this).closest('table').find('input').val();
    	if (val == "") {
    		alert('Please enter the price to multiply for this row');
    		return false;
    	}
    	console.log('tld', tld);
    	console.log('val', val);
    	$.each($("[data-tld='"+tld+"']"), function  (k,v){
    		var $elem = $(v),
    		cell_val = precise_round(val * $elem.data('year'), 2);
    		$elem.val(cell_val);
		});
    	return false;
    });

    $('.ep_tt').tooltip({
        container: 'body',
        placement: 'auto top'
    });

    $(".ep_lightbox").on('click', function  () {
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
    		$dialog.dialog('option', 'width', $this.data('width')).dialog('option', "position", { my: "center", at: "center", of: 'body' } );
    	}
    	if ($this.data('no-refresh')) {
    		$dialog.data('no-refresh',true);
    	} else {
    		$dialog.data('no-refresh',false);
    	}
    	$("#enom_pro_dialog_iframe").attr('src', href);
    	return false;
    });
    
    $("#enom_pro_dialog").dialog({
    	width: 640,
    	height: 640,
    	autoOpen:false,
    	modal: true,
    	close: function  () {
    		if (! $(this).data('no-refresh')) {
    			$("body").addClass('loading').append('<div class="ui-widget-overlay body-loader"></div>');  
    			$("#enom_pro_pricing_table input").attr('disabled', true);
    			window.location.reload();
    		}
    	}
    });
    $('body').on("click", '.ui-widget-overlay', function() {
        $("#enom_pro_dialog").dialog("close");
    });   
    $("#import_table_form").on('getwhois', ".domain_whois", function(e) {
        var $target = $(this);
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
        whois_xhrs.push($.ajax({
            url : 'addonmodules.php?module=enom_pro',
            global : false,
            data : {
                action : 'get_domain_whois',
                domain : domain_name
            },
            success : function(data, xhr) {
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
            complete: function  (xhr)
            {
            	if (xhr.statusText == 'abort') {
            		do_whois_results($target, {"email": "Cancelled"});
            	}
            },
            error: function  (xhr)
            {
            	do_whois_results($target, {"email": xhr.responseText});
            }
        })
        );
        return false;
    });
    window.onbeforeunload = function  () {
    	abort_whois_xhrs();
    };
    if (localStorage) {
        function getLabel() {
            return 'Clear LocalStorage (' + localStorage.length + ')';
        }
        
        $("#local_storage").on('refresh', function  () {
            $(this).html('<a class="btn btn-info btn-xs" href="#">' + getLabel()+ '</a>');
        }).on('click', '.btn', function  () {
            localStorage.clear();
            $(this).find('a').html(getLabel());
            $("#import_table_form").trigger('submit');
            return false;
        });
    }
    function do_whois_results($target, data) {
        $("#local_storage").trigger('refresh');
        $target.closest('.alert').find('.create_order').data('email',
                data.email);
        $target.find('.enom_pro_loader').addClass('hidden');
        $target.find('.response').html(data.email);
    }
    var $message = $("#ajax_messages"), $process = $("#order_process");
    $process.hide(), last_domain = '';
    //Create Order
    $("#import_table_form").on('click', 'a.create_order', function() {
        var domain_name = $(this).data('domain');
        last_domain = domain_name;
        $("#import_next_button").hide();
        $("#domain_field").add('#domain_field2').val(domain_name);
        $("#create_order_dialog").dialog('open');
        if ($("#generateinvoice").is(':checked') && $("#invoice_email").not(':visible')) {
        	$("#invoice_email").show();
        }
        var $button = $(this),
        	email = $button.data('email'),
        	id_protect = $button.data('id-protect'),
        	dns = $button.data('dns'),
        	autorenew = $button.data('autorenew'),
        	nextduedate = $button.data('nextduedate');
        if (email != "") {
            $("option[data-email='" + email + "']").attr('selected', true);
        }
        $("[name=nextduedate]").val($button.data('nextduedate')); 
        $("[name=nextduedatelabel]").val($button.data('nextduedatelabel')); 
        $("[name=expiresdate]").val($button.data('expiresdate'));
        $("[name=expiresdatelabel]").val($button.data('expiresdatelabel'));
        toggle_checkbox($("#idprotection") , (id_protect == 1 ? true : false));
        toggle_checkbox($("#dnsmanagement") , (dns == 1 ? true : false));
        if (autorenew == 1) {
        	$("#auto-renew-warning").show(); 
        } else {
        	$("#auto-renew-warning").hide(); 
        }
        $message.add($process).hide();
        $process.slideDown(200);
        return false;
    });
    function toggle_checkbox ($elem, checked){
    	if (checked) {
    		$elem.attr('checked', true);
    	} else {
    		$elem.removeAttr('checked');
    	}
    }
    $("#import_next_button").bind('click', function  (){
    	if ($(".create_order").length == 0) {
    		$(".next a").trigger('click'); 
    	} else {
    		$($(".create_order")[0]).trigger('click');
    	}
    });
    $("#create_order_form").bind('submit', function() {
        $message.removeClass('alert-danger alert-success').hide();
        $process.hide();
        var $loader = $(".enom_pro_loader", $(this));
        $loader.show();
        $("#import_next_button").hide();
        $.ajax({
	                url : 'addonmodules.php?module=enom_pro',
	                data : $(this).serialize(),
	                success : function(data) {
	                    if (data.success) {
	                        $process.hide();
	                        $message.addClass('alert-success');
	                        $("#import_table_form").trigger('submit').on('ajaxComplete', function() {
	                            var $new_elem = $("[data-domain='"+ last_domain+ "']").closest('.alert');
	                            $new_elem.removeClass('alert-success');
	                            setTimeout(function() {
	                            	$new_elem.addClass('alert-success');
	                            }, 250);
	                            setTimeout(function() {
	                                $new_elem.removeClass('alert-success');
	                            }, 500);
	                            setTimeout(function() {
	                                $new_elem.addClass('alert-success');
	                            }, 750);
	                            $("#import_next_button").show(); 
	                        });
	                        var message = 'Created '+(data.activated ? 'Active' : 'Pending') + ' Order #';
	                        if (data.activated) {
	                        	message += data.orderid +
	                        	'<a class="btn btn-xs btn-default" target="_blank" href="clientsdomains.php?domainid='+data.domainid+'">View Domain</a>';;
	                        } else {
	                        	message += '<a class="btn btn-xs" target="_blank" href="orders.php?action=view&id='+data.orderid+'">View Pending Order #'+ data.orderid +'</a>';;
	                        }
	                        if (data.invoiceid) {
	                        	message += ' invoice #' + 
	                        	'<a class="btn btn-xs btn-default" target="_blank" href="invoices.php?action=edit&id='+data.invoiceid+'">'+ data.invoiceid +'</a>';
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
	                error : function(xhr, text) {
	                    $loader.hide();
	                    $message.addClass('alert-danger').html(
	                            'WHMCS Error: '
	                                    + xhr.responseText)
	                            .slideDown();
	                    $process.slideDown();
	                }
            });
        return false;
    });
    $(".no-js").hide(); 
    $("#import_table_form").on('submit', function() {
        $(".enom_pro_loader").not('.small').removeClass('hidden');
        abort_whois_xhrs();
        $("#domain_caches").hide(); 
        $.ajax({
            url : 'addonmodules.php?module=enom_pro',
            data : $(this).serialize(),
            beforeSend: function  ()
            {
            	$("#import_ajax_messages").addClass('hidden'); 
            },
            success : function(data) {
                
                $("#domains_target").html(data.html);
                $(".domains_cache_time").html(data.cache_date);
                
            }, 
            error: function  (jqXHR, status)
            {
            	$("#import_ajax_messages").html("Error: "+jqXHR.responseText).removeClass('hidden');
            },
            complete: function  ()
            {
            	$(".enom_pro_loader").addClass('hidden');
            	$("#domain_caches").show();
            }
        });
        return false;
    }).trigger('submit');
    $("#search_form").on('submit', function  (){
    	var search = $(this).find('input[name=s][type=text]').val();
    	$("input[name=s][type=hidden]").val(search); 
    	$("#import_table_form").trigger('submit');
    	return false;
	});
    $("#enom_pro_import_page").on('click',".clear_search", function  (){
    	$('input[name=s][type=text]').val("");
    	$("#search_form").trigger('submit');
    	return false;
    });
    $(".bulkImport").on('submit reset recalculate', function  (e){
        if (e.type == 'reset') {
            $(".clear_all").trigger('click');
            return true; //Allow browser to reset the form
        } else if (e.type == 'submit') {
            $(".clear_all").trigger('click');
            $(this).trigger('recalculate');
            return false;
        } else if (e.type == 'recalculate') {
            var markup = $("#percentMarkup").val(),
                round = $("#roundTo").val();
            if ($('#overWriteWHMCS').prop('checked')) {
                var $elems = jQuery('[data-price]');
            } else {
                var $elems = jQuery('[data-price]').not('[data-whmcs]');
            }
            $elems.each(function  (){
                var $elem = $(this);
                var newPrice = $elem.data('price') * ( 1 + (markup / 100));
                var newPriceDouble = newPrice.toFixed(2);
                //Check if the decimal value is gt our rounding amount
                var thisDollarAmount = newPriceDouble.split(".")[1];
                if (thisDollarAmount >= round) {
                    //Need to bump the dollar amount to the next one
                    //IE round to .95 amount 3.98 -> 4.95
                    var Dollar = parseInt(newPriceDouble) + 1;
                    console.log('rounding up to $', Dollar);
                } else {
                    var Dollar = parseInt(newPriceDouble);
                }
                newPriceDouble = Dollar + '.' + round;
                $elem.val(newPriceDouble);
                if ($elem.data('year') == 1 ) {
                    $elem.trigger('keyup');
                }
            });
        }
    });
    $(".savePricing").on('click', function  (){
        $("#enom_pro_pricing_import").trigger('submit');
        return false;
    });
    $(".toggle_tld").on('click', function  (){
    	var $this = $(this),
    	tld = $this.data('tld');
        var $input = $("[data-tld='" + tld + "'][data-year=1]");
        var first_val = $input.val()
    	if ("" == first_val || " " == first_val) {
            //Reset
    		$.each($("[data-tld='"+tld+"']"), function  (k,v) {
    			$(v).val($(v).data('price'));
			});
    	} else {
            //Clear
    		$.each($("[data-tld='"+tld+"']"), function  (k,v) {
    			$(v).val('');
			});
    	}
        $input.trigger('keyup');//Trigger our button handler
    	return false;
    });
    $(".toggle_years").on('click', function  (){
    	var $this = $(this),
    	year = $this.data('year');
    	var first_val = $($("[data-year='"+year+"']")[1]).val();
    	if ("" == first_val || " " == first_val) {
    		$.each($("[data-year='"+year+"']"), function  (k,v) {
    			$(v).val($(v).data('price'));
    		});
    	} else {
    		$.each($("[data-year='"+year+"']"), function  (k,v) {
    			$(v).val('');
    		});
    	}
    	return false;
    });
    var $years = $("[data-year=1]");
    if ($years.length > 0 ) {
        $years.on('keyup', function  (){
            var $t = $(this),
                tld = $t.data('tld'),
                $thisTrigger = $(".toggle_tld[data-tld='"+tld+"']");
            if ($t.val() == "") {
               $thisTrigger.addClass('btn-default').removeClass('btn-danger').html('&rarr;');
            } else {
                $thisTrigger.addClass('btn-danger').removeClass('btn-default').html('&otimes;');
            }
        });
    }

    $(".delete_tld").on('click', function  () {
    	var $this = $(this);
    	$("[data-tld='"+$this.data('tld')+"']").val('-1.00'); 
    	return false;
    }); 
    $("#enom_pro_pricing_import").on('submit', function  () {
    	$("input", '#enom_pro_pricing_import').each(function  (k,v){
    		var val = $(v).val();
    		if (val == '0.00' || val == "" || parseInt(val) == 0) {
    			$(v).removeAttr('value').removeAttr('name');
    		}
    	});
    });
    $(".clear_all").on('click', function  (){
    	$('[data-price]').val('');
        $("[data-price][data-year=1]").trigger('keyup');
        return false;
    });
    $(".deleteFromWHMCS").on("click", function  (){
        $(".delete_tld").trigger("click");
        $(".clearDropdown").dropdown('toggle')
        return false;
    });
    $("#domains_target").on("click", ".pager A", function() {
    	var start = $(this).data('start');
        $("input[name=start]").val(start);
    	abort_whois_xhrs();
        $("#import_table_form").trigger('submit');
        return false;
    });
    $("[data-alert]").closest('.alert').on('close.bs.alert', function() {
        var alertID = $(this).find('[data-alert]').data('alert');
        var alertData = {
            action: 'dismiss_alert',
            alert: alertID
        };
        $.ajax({
            url: 'addonmodules.php?module=enom_pro',
            data: alertData
       });
    });
    $("#filter_form").on('submit change', function() {
        $("input[name=start]").val(1);
        $("input[name=show_only]").val($(this).find('select').val());
        $("#import_table_form").trigger('submit');
        return false;
    });
    var slide_time = $(".slideup").data('timeout');
    if (! slide_time) {
    	slide_time = 2000;
    } else {
    	slide_time = slide_time * 1000;
    }
    setTimeout(function() {
        $(".slideup").slideUp(1000);
    }, slide_time);
    $("#per_page_form").on('submit change', function() {
        var $loader = $('.enom_pro_loader');
        $.ajax({
            url : 'addonmodules.php?module=enom_pro',
            data : $(this).serialize(),
            beforeSend : function() {
                $loader.removeClass('hidden');
            },
            success : function() {
                $("input[name=start]").val(1);
                $("#import_table_form").trigger('submit');
            }
        });
        return false;
    });
    $("#enom_pro_pricing_table").tableHover({colClass: "hover", ignoreCols: [1,2,3,4]}); 
	var $news = $("#enom_pro_changelog"),
	loadingString = "<h3>Loading Changelog</h3><div class=\"enom_pro_loader\"></div>";
 	if ($news.length > 0) {
		$news.append(loadingString);
		$.ajax({
		    url: document.location.protocol + "//ajax.googleapis.com/ajax/services/feed/load?v=1.0&num=4&callback=?&q=" + encodeURIComponent("http://mycircletree.com/client-area/knowledgebaserss.php?id=43"),
		    dataType: "json",
		    success: function(data) {
		      	$news.empty();
		      	var str = "";
				$.each(data.responseData.feed.entries, function (k,entry) {
					console.log('entry', entry);
					str += "<h4><a target=\"_blank\" href=\""+entry.link+"\" title=\"View "+entry.title+" on our Website\">"
					  + entry.title+"</a></h4><p>" + 
					  entry.content + "<a class=\"button button-mini\" style=\"float:right;\" target=\"_blank\" href=\""+entry.link+"\">Read more...</a></p>";
				});
				str +=	"<a class=\"alignright\" href=\"http://mycircletree.com/client-area/knowledgebase.php?action=displayarticle&id=43\" target=\"_blank\">"
					   + "View Changelog</a>";
				$(str).appendTo($news); 
		    }
		});
 	}
    $("body").on("click", ".enom_stat_button .btn", function  () {
        if ($(this).hasClass("disabled")) {
            return false;
        }
        var tab = $(this).data("tab");
        if (! tab) {
            return true;
        }
        var $loader = $(this).closest(".enom_stat_button").find(".enom_pro_loader");
        $loader.removeClass('hidden');
        $.ajax({
                   url: $(this).attr("href"),
                   success: function  (data){
                       $("#enom_pro_"+tab).html(data);
                   },
                   complete: function  (){
                       $loader.addClass('hidden');
                   },
                   error: function  (xhr){
                       console.log(xhr.responseText);
                   }
               });
        return false;
    });
}); //end jQuery Ready
} catch (err) {
	alert('Enom PRO JS Error: ' + err);
	
}