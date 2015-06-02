</div>
<div class="small-12 columns sidebar hide-for-large-up">{include file="$template/sidebar.tpl"}</div>
</div>
</div>
<footer>
  <div class="copyright">Copyright &copy; <a href="http://www.whmcs.com/" target="_blank">WHMCompleteSolution</a>. All Rights Reserved.</div>
  <div class="design">Design by <a href="http://www.swiftmodders.com" target="_blank">SwiftModders</a></div>
</footer>
<div id="searchresults" class="reveal-modal" data-reveal>
  <div id="searchresultsscroller"></div>
  <a class="close-reveal-modal"><i class="fa fa-times"></i></a> </div>
<div id="myNotes" class="reveal-modal" data-reveal>
  <h3>{$_ADMINLANG.global.mynotes}</h3>
  <form id="frmmynotes">
    <input type="hidden" name="action" value="savenotes" />
    <input type="hidden" name="token" value="{$csrfToken}" />
    <textarea id="mynotesbox" name="notes" rows="15">{$admin_notes}</textarea>
    <input class="button small" type="button" value="{$_ADMINLANG.global.savechanges}" onclick="notesclose('1');return false" />
  </form>
  <a class="close-reveal-modal" onclick="notesclose('');return false"><i class="fa fa-times"></i></a> </div>
<div class="back-top"><a href="#"><i class="fa fa-chevron-circle-up"></i></a></div>
</div>
{$footeroutput} 
<script src="../includes/jscript/textext.js"></script> 
<script src="../includes/jscript/adminsearchbox.js"></script> 
<script src="templates/{$template}/swiftmodders.min.js"></script> 
<script>
var datepickerformat = "{$datepickerformat}";
{if $jquerycode}$(document).ready(function(){ldelim}
	{$jquerycode}
{rdelim});{/if}
{if $jscode}{$jscode}{/if}
$(document).foundation();
</script>
</body></html>