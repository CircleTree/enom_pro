{include file="$template/pageheader.tpl" title=$LANG.domaintitle desc=$LANG.domaincheckerintro}

{if $inccode}
<div class="alert alert-error textcenter">
    {$LANG.captchaverifyincorrect}
</div>
{/if}

{if $bulkdomainsearchenabled}<p align="right"><a href="domainchecker.php?search=bulkregister">{$LANG.domainbulksearch}</a> | <a href="domainchecker.php?search=bulktransfer">{$LANG.domainbulktransfersearch}</a></p>{/if}

<form method="post" action="domainchecker.php" class="form-horizontal">

<div class="well">
    <p>{$LANG.domaincheckerenterdomain}</p>
    <br />
    <div class="textcenter">
        <div align="right" class="multitldbtn"><input type="button" value="{$LANG.searchmultipletlds} &raquo;" class="btn " onclick="jQuery('#tlds').slideToggle()" /></div>
        <input class="bigfield" name="domain" type="text" value="{if $domain}{$domain}{else}{$LANG.domaincheckerdomainexample}{/if}" onfocus="if(this.value=='{$LANG.domaincheckerdomainexample}')this.value=''" onblur="if(this.value=='')this.value='{$LANG.domaincheckerdomainexample}'" />
    </div>
    <div class="domcheckertldselect hide" id="tlds">
        {foreach from=$tldslist key=num item=listtld}
            <div class="col4 textcenter"><label class="full"><input type="checkbox" name="tlds[]" value="{$listtld}"{if in_array($listtld,$tlds) || !$tlds && $num==1} checked{/if}> {$listtld}</label></div>
        {/foreach}
        <div class="clear"></div>
    </div>

    <div class="textcenter">
        {if $capatacha}
        <div class="captchainput">
            <p>{$LANG.captchaverify}</p>
            {if $capatacha eq "recaptcha"}
            <p>{$recapatchahtml}</p>
            {else}
            <p><img src="includes/verifyimage.php" align="middle" /> <input type="text" name="code" class="input-small" maxlength="5" /></p>
            {/if}
        </div>
        {/if}
        <div class="internalpadding"><input type="submit" value="{$LANG.checkavailability}" class="btn btn-primary btn-large" />{if $condlinks.domaintrans} <input type="submit" name="transfer" value="{$LANG.domainstransfer}" class="btn btn-success btn-large" />{/if} <input type="submit" name="hosting" value="{$LANG.domaincheckerhostingonly}" class="btn btn-large" /></div>
    </div>
</div>

</form>

{if $lookup}

{if $invalidtld}
    <p class="fontsize3 domcheckererror textcenter">{$invalidtld|strtoupper} {$LANG.domaincheckerinvalidtld}</p>
{elseif $available}
    <p class="fontsize3 domcheckersuccess textcenter">{$LANG.domainavailable1} <strong>{$sld}{$ext}</strong> {$LANG.domainavailable2}</p>
{elseif $invalid}
    <p class="fontsize3 domcheckererror textcenter">{$LANG.ordererrordomaininvalid}</p>
{elseif $error}
    <p class="fontsize3 domcheckererror textcenter">{$LANG.domainerror}</p>
{else}
    <p class="fontsize3 domcheckererror textcenter">{$LANG.domainunavailable1} <strong>{$sld}{$ext}</strong> {$LANG.domainunavailable2}</p>
{/if}

{if !$invalid}

<br />

<div class="center80">

<form method="post" action="{$systemsslurl}cart.php?a=add&domain=register">

<table class="table table-striped table-framed">
    <thead>
        <tr>
            <th></th>
            <th>{$LANG.domainname}</th>
            <th class="textcenter">{$LANG.domainstatus}</th>
            <th class="textcenter">{$LANG.domainmoreinfo}</th>
        </tr>
    </thead>
    <tbody>
{foreach from=$availabilityresults key=num item=result}
    {if $invalidtld && $result.domain == $domain}
        {continue}
    {else}
        <tr>
            <td class="textcenter">{if $result.status eq "available"}<input type="checkbox" name="domains[]" value="{$result.domain}" {if $num eq "0" && $available}checked {/if}/><input type="hidden" name="domainsregperiod[{$result.domain}]" value="{$result.period}" />{else}X{/if}</td>
            <td>{$result.domain}</td>
            <td class="textcenter {if $result.status eq "available"}domcheckersuccess{else}domcheckererror{/if}">{if $result.status eq "available"}{$LANG.domainavailable}{else}{$LANG.domainunavailable}{/if}</td>
            <td class="textcenter">{if $result.status eq "unavailable"}<a href="http://{$result.domain}" target="_blank">WWW</a> <a href="#" onclick="popupWindow('whois.php?domain={$result.domain}','whois',650,420);return false">WHOIS</a>{else}<select name="domainsregperiod[{$result.domain}]">{foreach key=period item=regoption from=$result.regoptions}<option value="{$period}">{$period} {$LANG.orderyears} @ {$regoption.register}</option>{/foreach}</select>{/if}</td>
        </tr>
    {/if}
{/foreach}
</table>

<p align="center"><input type="submit" value="{$LANG.ordernowbutton} &raquo;" class="btn btn-danger" /></p>
{$namespinner}

</form>

</div>

{/if}

{else}

{include file="$template/subheader.tpl" title=$LANG.domainspricing}

<div class="center80">

<table class="table table-striped table-framed">
    <thead>
        <tr>
            <th class="textcenter">{$LANG.domaintld}</th>
            <th class="textcenter">{$LANG.domainminyears}</th>
            <th class="textcenter">{$LANG.domainsregister}</th>
            <th class="textcenter">{$LANG.domainstransfer}</th>
            <th class="textcenter">{$LANG.domainsrenew}</th>
        </tr>
    </thead>
    <tbody>
{foreach from=$tldpricelist item=tldprice}
        <tr>
            <td>{$tldprice.tld}</td>
            <td class="textcenter">{$tldprice.period}</td>
            <td class="textcenter">{if $tldprice.register}{$tldprice.register}{else}{$LANG.domainregnotavailable}{/if}</td>
            <td class="textcenter">{if $tldprice.transfer}{$tldprice.transfer}{else}{$LANG.domainregnotavailable}{/if}</td>
            <td class="textcenter">{if $tldprice.renew}{$tldprice.renew}{else}{$LANG.domainregnotavailable}{/if}</td>
        </tr>
{/foreach}
    </tbody>
</table>

{if !$loggedin && $currencies}
<form method="post" action="domainchecker.php">
<p align="right">{$LANG.choosecurrency}: <select name="currency" onchange="submit()">{foreach from=$currencies item=curr}
<option value="{$curr.id}"{if $curr.id eq $currency.id} selected{/if}>{$curr.code}</option>
{/foreach}</select> <input type="submit" value="{$LANG.go}" /></p>
</form>
{/if}

</div>

{/if}

<br /><br />
