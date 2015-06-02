{if !$numcustomfields}
    <div align="center">{$_ADMINLANG.support.nocustomfields}</div>
{else}
    <form method="post" action="{$smarty.server.PHP_SELF}?action=viewticket&id={$ticketid}&sub=savecustomfields">
    	{$csrfTokenHiddenInput}
        <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
        {foreach from=$customfields item=customfield}
            <tr>
                <td width="25%" class="fieldlabel">{$customfield.name}</td>
                <td class="fieldarea">{$customfield.input}</td>
            </tr>
        {/foreach}
        </table>
        <img src="images/spacer.gif" height="10" width="1" /><br />
        <div align="center">
            <input type="submit" value="{$_ADMINLANG.global.savechanges}" class="button">
        </div>
    </form>
{/if}
