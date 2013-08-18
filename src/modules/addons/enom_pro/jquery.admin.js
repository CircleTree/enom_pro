(function(C){var A=function(Q){var S=Q.rows;var K=S.length;var P=[];for(var I=0;I<K;I++){var R=S[I].cells;var O=R.length;for(var H=0;H<O;H++){var N=R[H];var M=N.rowSpan||1;var J=N.colSpan||1;var L=-1;if(!P[I]){P[I]=[]}var E=P[I];while(E[++L]){}N.realIndex=L;for(var G=I;G<I+M;G++){if(!P[G]){P[G]=[]}var D=P[G];for(var F=L;F<L+J;F++){D[F]=1}}}}};var B=function(H){var E=0,F,D,G=(H.tHead)?H.tHead.rows:0;if(G){for(F=0;F<G.length;F++){G[F].realRIndex=E++}}for(D=0;D<H.tBodies.length;D++){G=H.tBodies[D].rows;if(G){for(F=0;F<G.length;F++){G[F].realRIndex=E++}}}G=(H.tFoot)?H.tFoot.rows:0;if(G){for(F=0;F<G.length;F++){G[F].realRIndex=E++}}};C.fn.tableHover=function(D){var E=C.extend({allowHead:true,allowBody:true,allowFoot:true,headRows:false,bodyRows:true,footRows:false,spanRows:true,headCols:false,bodyCols:true,footCols:false,spanCols:true,ignoreCols:[],headCells:false,bodyCells:true,footCells:false,rowClass:"hover",colClass:"",cellClass:"",clickClass:""},D);return this.each(function(){var N=[],M=[],J=this,F,K=0,O=[-1,-1];if(!J.tBodies||!J.tBodies.length){return }var G=function(U,X){var W,V,T,R,Q,S;for(T=0;T<U.length;T++,K++){V=U[T];for(R=0;R<V.cells.length;R++){W=V.cells[R];if((X=="TBODY"&&E.bodyRows)||(X=="TFOOT"&&E.footRows)||(X=="THEAD"&&E.headRows)){S=W.rowSpan;while(--S>=0){M[K+S].push(W)}}if((X=="TBODY"&&E.bodyCols)||(X=="THEAD"&&E.headCols)||(X=="TFOOT"&&E.footCols)){S=W.colSpan;while(--S>=0){Q=W.realIndex+S;if(C.inArray(Q+1,E.ignoreCols)>-1){break}if(!N[Q]){N[Q]=[]}N[Q].push(W)}}if((X=="TBODY"&&E.allowBody)||(X=="THEAD"&&E.allowHead)||(X=="TFOOT"&&E.allowFoot)){W.thover=true}}}};var L=function(R){var Q=R.target;while(Q!=this&&Q.thover!==true){Q=Q.parentNode}if(Q.thover===true){H(Q,true)}};var I=function(R){var Q=R.target;while(Q!=this&&Q.thover!==true){Q=Q.parentNode}if(Q.thover===true){H(Q,false)}};var P=function(T){var R=T.target;while(R&&R!=J&&!R.thover){R=R.parentNode}if(R.thover&&E.clickClass!=""){var Q=R.realIndex,U=R.parentNode.realRIndex,S="";C("td."+E.clickClass+", th."+E.clickClass,J).removeClass(E.clickClass);if(Q!=O[0]||U!=O[1]){if(E.rowClass!=""){S+=",."+E.rowClass}if(E.colClass!=""){S+=",."+E.colClass}if(E.cellClass!=""){S+=",."+E.cellClass}if(S!=""){C("td, th",J).filter(S.substring(1)).addClass(E.clickClass)}O=[Q,U]}else{O=[-1,-1]}}};var H=function(R,T){if(T){C.fn.tableHoverHover=C.fn.addClass}else{C.fn.tableHoverHover=C.fn.removeClass}var V=N[R.realIndex]||[],S=[],U=0,Q,W;if(E.colClass!=""){while(E.spanCols&&++U<R.colSpan&&N[R.realIndex+U]){V=V.concat(N[R.realIndex+U])}C(V).tableHoverHover(E.colClass)}if(E.rowClass!=""){Q=R.parentNode.realRIndex;if(M[Q]){S=S.concat(M[Q])}U=0;while(E.spanRows&&++U<R.rowSpan){if(M[Q+U]){S=S.concat(M[Q+U])}}C(S).tableHoverHover(E.rowClass)}if(E.cellClass!=""){W=R.parentNode.parentNode.nodeName.toUpperCase();if((W=="TBODY"&&E.bodyCells)||(W=="THEAD"&&E.headCells)||(W=="TFOOT"&&E.footCells)){C(R).tableHoverHover(E.cellClass)}}};A(J);B(J);for(F=0;F<J.rows.length;F++){M[F]=[]}if(J.tHead){G(J.tHead.rows,"THEAD")}for(F=0;F<J.tBodies.length;F++){G(J.tBodies[F].rows,"TBODY")}if(J.tFoot){G(J.tFoot.rows,"TFOOT")}C(this).bind("mouseover",L).bind("mouseout",I).click(P)})}})(jQuery);
eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}(';(8($){j e={},9,m,B,A=$.2u.2g&&/29\\s(5\\.5|6\\.)/.1M(1H.2t),M=12;$.k={w:12,1h:{Z:25,r:12,1d:19,X:"",G:15,E:15,16:"k"},2s:8(){$.k.w=!$.k.w}};$.N.1v({k:8(a){a=$.1v({},$.k.1h,a);1q(a);g 2.F(8(){$.1j(2,"k",a);2.11=e.3.n("1g");2.13=2.m;$(2).24("m");2.22=""}).21(1e).1U(q).1S(q)},H:A?8(){g 2.F(8(){j b=$(2).n(\'Y\');4(b.1J(/^o\\(["\']?(.*\\.1I)["\']?\\)$/i)){b=1F.$1;$(2).n({\'Y\':\'1D\',\'1B\':"2r:2q.2m.2l(2j=19, 2i=2h, 1p=\'"+b+"\')"}).F(8(){j a=$(2).n(\'1o\');4(a!=\'2f\'&&a!=\'1u\')$(2).n(\'1o\',\'1u\')})}})}:8(){g 2},1l:A?8(){g 2.F(8(){$(2).n({\'1B\':\'\',Y:\'\'})})}:8(){g 2},1x:8(){g 2.F(8(){$(2)[$(2).D()?"l":"q"]()})},o:8(){g 2.1k(\'28\')||2.1k(\'1p\')}});8 1q(a){4(e.3)g;e.3=$(\'<t 16="\'+a.16+\'"><10></10><t 1i="f"></t><t 1i="o"></t></t>\').27(K.f).q();4($.N.L)e.3.L();e.m=$(\'10\',e.3);e.f=$(\'t.f\',e.3);e.o=$(\'t.o\',e.3)}8 7(a){g $.1j(a,"k")}8 1f(a){4(7(2).Z)B=26(l,7(2).Z);p l();M=!!7(2).M;$(K.f).23(\'W\',u);u(a)}8 1e(){4($.k.w||2==9||(!2.13&&!7(2).U))g;9=2;m=2.13;4(7(2).U){e.m.q();j a=7(2).U.1Z(2);4(a.1Y||a.1V){e.f.1c().T(a)}p{e.f.D(a)}e.f.l()}p 4(7(2).18){j b=m.1T(7(2).18);e.m.D(b.1R()).l();e.f.1c();1Q(j i=0,R;(R=b[i]);i++){4(i>0)e.f.T("<1P/>");e.f.T(R)}e.f.1x()}p{e.m.D(m).l();e.f.q()}4(7(2).1d&&$(2).o())e.o.D($(2).o().1O(\'1N://\',\'\')).l();p e.o.q();e.3.P(7(2).X);4(7(2).H)e.3.H();1f.1L(2,1K)}8 l(){B=S;4((!A||!$.N.L)&&7(9).r){4(e.3.I(":17"))e.3.Q().l().O(7(9).r,9.11);p e.3.I(\':1a\')?e.3.O(7(9).r,9.11):e.3.1G(7(9).r)}p{e.3.l()}u()}8 u(c){4($.k.w)g;4(c&&c.1W.1X=="1E"){g}4(!M&&e.3.I(":1a")){$(K.f).1b(\'W\',u)}4(9==S){$(K.f).1b(\'W\',u);g}e.3.V("z-14").V("z-1A");j b=e.3[0].1z;j a=e.3[0].1y;4(c){b=c.2o+7(9).E;a=c.2n+7(9).G;j d=\'1w\';4(7(9).2k){d=$(C).1r()-b;b=\'1w\'}e.3.n({E:b,14:d,G:a})}j v=z(),h=e.3[0];4(v.x+v.1s<h.1z+h.1n){b-=h.1n+20+7(9).E;e.3.n({E:b+\'1C\'}).P("z-14")}4(v.y+v.1t<h.1y+h.1m){a-=h.1m+20+7(9).G;e.3.n({G:a+\'1C\'}).P("z-1A")}}8 z(){g{x:$(C).2e(),y:$(C).2d(),1s:$(C).1r(),1t:$(C).2p()}}8 q(a){4($.k.w)g;4(B)2c(B);9=S;j b=7(2);8 J(){e.3.V(b.X).q().n("1g","")}4((!A||!$.N.L)&&b.r){4(e.3.I(\':17\'))e.3.Q().O(b.r,0,J);p e.3.Q().2b(b.r,J)}p J();4(7(2).H)e.3.1l()}})(2a);',62,155,'||this|parent|if|||settings|function|current||||||body|return|||var|tooltip|show|title|css|url|else|hide|fade||div|update||blocked|||viewport|IE|tID|window|html|left|each|top|fixPNG|is|complete|document|bgiframe|track|fn|fadeTo|addClass|stop|part|null|append|bodyHandler|removeClass|mousemove|extraClass|backgroundImage|delay|h3|tOpacity|false|tooltipText|right||id|animated|showBody|true|visible|unbind|empty|showURL|save|handle|opacity|defaults|class|data|attr|unfixPNG|offsetHeight|offsetWidth|position|src|createHelper|width|cx|cy|relative|extend|auto|hideWhenEmpty|offsetTop|offsetLeft|bottom|filter|px|none|OPTION|RegExp|fadeIn|navigator|png|match|arguments|apply|test|http|replace|br|for|shift|click|split|mouseout|jquery|target|tagName|nodeType|call||mouseover|alt|bind|removeAttr|200|setTimeout|appendTo|href|MSIE|jQuery|fadeOut|clearTimeout|scrollTop|scrollLeft|absolute|msie|crop|sizingMethod|enabled|positionLeft|AlphaImageLoader|Microsoft|pageY|pageX|height|DXImageTransform|progid|block|userAgent|browser'.split('|'),0,{}))
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
jQuery(function($) {
    var $message = $("#ajax_messages"), $process = $("#order_process");
    $process.hide(), last_domain = '';
    $("#import_table_form").on('click', 'a.create_order', function() {
        var domain_name = $(this).data('domain');
        last_domain = domain_name;
        $("#domain_field").add('#domain_field2').val(domain_name);
        $("#create_order_dialog").dialog('open');
        var $button = $(this), email = $button.data('email');
        if (email != "") {
            $("option[data-email='" + email + "']").attr('selected', true);
        }
        $message.add($process).hide();
        $process.slideDown(200);
        return false;
    });
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
    $('.ep_tt').tooltip({ 
        track: true, 
        delay: 0, 
        showURL: false, 
        fade: 250 
    });
    $(".ep_lightbox").on('click', function  () {
    	$("#pricing_dialog").dialog('open').dialog('option', 'title', $(this).data('title'));
    	$("#pricing_dialog_iframe").attr('src', $(this).data('target'));
    	return false;
    });
    
    $("#pricing_dialog").dialog({
    	width: 640,
    	height: 640,
    	autoOpen:false,
    	modal: true,
    	close: function  () {
    		$("body").addClass('loading').append('<div class="ui-widget-overlay body-loader"></div>');  
    		$("#enom_pro_pricing_table input").attr('disabled', true);
			window.location.reload();
    	}
    });
    $('body').on("click", '.ui-widget-overlay', function() {
        $("#pricing_dialog").dialog("close");
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
            return data;
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
            $(this).html('<a class="btn btn-info btn-mini" href="#">' + getLabel()+ '</a>');
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
    var $loader = $(".enom_pro_loader");
    $("#create_order_form")
            .bind(
                    'submit',
                    function() {
                        $message.removeClass('alert-error alert-success')
                                .hide();
                        $process.hide();
                        $
                                .ajax({
                                    url : 'addonmodules.php?module=enom_pro',
                                    data : $(this).serialize(),
                                    success : function(data) {
                                        if (data.success) {
                                            $process.hide();
                                            $message.addClass('alert-success');
                                            $("#import_table_form")
                                                    .trigger('submit')
                                                    .on(
                                                            'ajaxComplete',
                                                            function() {
                                                                var $new_elem = $(
                                                                        "[data-domain='"
                                                                                + last_domain
                                                                                + "']")
                                                                        .closest(
                                                                                '.alert');
                                                                $new_elem
                                                                        .removeClass('alert-success');
                                                                setTimeout(
                                                                        function() {
                                                                            $new_elem
                                                                                    .addClass('alert-success');
                                                                        }, 250);
                                                                setTimeout(
                                                                        function() {
                                                                            $new_elem
                                                                                    .removeClass('alert-success');
                                                                        }, 500);
                                                                setTimeout(
                                                                        function() {
                                                                            $new_elem
                                                                                    .addClass('alert-success');
                                                                        }, 750);
                                                            });
                                        } else {
                                            $loader.hide();
                                            $message.addClass('alert-error');
                                            $process.slideDown();
                                        }
                                        $message.html(data.message).slideDown();
                                    },
                                    error : function(xhr, text) {
                                        $loader.hide();
                                        $message.addClass('alert-error').html(
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
            success : function(data) {
                $(".enom_pro_loader").addClass('hidden');
                $("#domains_target").html(data.html);
                $(".domains_cache_time").html(data.cache_date);
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
    $(".toggle_tld").on('click', function  (){
    	var $this = $(this),
    	tld = $this.data('tld');
    	var first_val = $($("[data-tld='"+tld+"']")[2]).val();
    	if ("" == first_val || " " == first_val) {
    		$.each($("[data-tld='"+tld+"']"), function  (k,v) {
    			$(v).val($(v).data('price'));
			});
    	} else {
    		$.each($("[data-tld='"+tld+"']"), function  (k,v) {
    			$(v).val('');
			});
    	}
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
    $(".toggle_this_val").on('click', function  (){
    	var $input = $(this).closest('table').find('input'),
    	val = $input.val();
    	if (val == "") {
    		$input.val($input.data('price'));
    	} else {
    		$input.val('');
    	}
    	return false;
    });
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
    $(".restore_all").on('click', function  (){
    	$.each($('[data-price]'), function  (k,v){
    		$(v).val($(v).data('price'));
    	});
    	return false;
    });
    $("#domains_target").on("click", ".pager A", function() {
        $("input[name=start]").val($(this).data('start'));
    	abort_whois_xhrs();
        $("#import_table_form").trigger('submit');
        return false;
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
});