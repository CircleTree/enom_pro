<?php
/**
 * @var $this enom_pro
 */
?>
<div id="enom_pro_help_page">
	<div id="helpWrapper">
					<span class="searchWrap">
						<input type="text" id="helpSearch" name="s" size="10" autocomplete="false" spellcheck="true" placeholder="Search Help" />
						<span class="enom-pro-icon-cancel-circle hidden ep_tt" title="Clear Search"></span>
					</span>

		<div id="homeHelpContent">
		</div>
	</div>
</div>
<div class="help helpDialog" title="">
	<div id="helpDialogContent" class="enom_pro_output">
	</div>
</div>

<script>
	jQuery(function ($) {
		enom_pro.initHelpIndex();
	});
</script>