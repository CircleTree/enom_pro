{$infobox}
<div id="replyingadmin"> {if $replyingadmin}
  <div class="errorbox">{$replyingadmin.name} {$_ADMINLANG.support.viewedandstarted} @ {$replyingadmin.time}</div>
  {/if} </div>
<h2>#{$tid} - {$subject}
  <select name="ticketstatus" id="ticketstatus">
    
  {foreach from=$statuses item=statusitem}<option{if $statusitem.title eq $status} selected{/if} style="color:{$statusitem.color}">{$statusitem.title}
    </option>
    {/foreach}
  
  </select>
  <a href="#" onclick="$('#ticketstatus').val('Closed');$('#ticketstatus').trigger('change');return false">{$_ADMINLANG.global.close}</a></h2>
<div class="ticketlastreply"><strong>{$_ADMINLANG.support.lastreply}:</strong> {$lastreply}</div>
{if $clientnotes}
<div class="panel" id="clientsimportantnotes"> {foreach from=$clientnotes item=note}
  <div class="ticketstaffnotes">
    <div><strong>{$note.adminuser}</strong> | {$note.modified}</div>
    <div>{$note.note}</div>
    <div class="text-right"><a class="button small" href="clientsnotes.php?userid={$clientsdetails.userid}&action=edit&id={$note.id}"><i class="fa fa-pencil-square-o"></i> Edit</a></div>
  </div>
  {/foreach} </div>
{/if}

{foreach from=$addons_html item=addon_html}
<div style="margin-bottom:15px;">{$addon_html}</div>
{/foreach}
<div id="tabs">
  <ul>
    <li id="tab0" class="tab"><a href="javascript:;">{$_ADMINLANG.support.addreply}</a></li>
    <li id="tab1" class="tab"><a href="javascript:;">{$_ADMINLANG.support.addnote}</a></li>
    <li id="tab2" class="tab" onclick="loadTab(2, 'customfields', 0)"><a href="javascript:;">{$_ADMINLANG.setup.customfields}</a></li>
    <li id="tab3" class="tab" onclick="loadTab(3,'tickets',0)"><a href="javascript:;">{$_ADMINLANG.support.clienttickets}</a></li>
    <li id="tab4" class="tab" onclick="loadTab(4,'clientlog',0)"><a href="javascript:;">{$_ADMINLANG.support.clientlog}</a></li>
    <li id="tab5" class="tab"><a href="javascript:;">{$_ADMINLANG.fields.options}</a></li>
    <li id="tab6" class="tab" onclick="loadTab(6,'ticketlog',0)"><a href="javascript:;">{$_ADMINLANG.support.ticketlog}</a></li>
  </ul>
</div>
<div id="tab0box" class="tabbox">
  <div id="tab_content">
    <form method="post" action="{$smarty.server.PHP_SELF}?action=viewticket&id={$ticketid}" enctype="multipart/form-data" name="replyfrm" id="replyfrm">
      <textarea name="message" id="replymessage" rows="14" style="width:100%;margin:0 0 10px 0;">{if $signature}{$signature}{/if}</textarea>
      <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
        <tr>
          <td width="15%" class="fieldlabel">Tools</td>
          <td class="fieldarea"><select name="postaction">
              <option value="return">{$_ADMINLANG.support.setansweredreturn}</option>
              <option value="answered">{$_ADMINLANG.support.setansweredremain}</option>
              
              {foreach from=$statuses item=statusitem}{if $statusitem.id > 4}
              
              <option value="setstatus{$statusitem.id}">{$_ADMINLANG.support.setto} {$statusitem.title} {$_ADMINLANG.support.andremain}</option>
              
              {/if}{/foreach}
              
              <option value="close">{$_ADMINLANG.support.closereturn}</option>
              <option value="note">{$_ADMINLANG.support.addprivatenote}</option>
            </select>
            <input type="submit" value="{$_ADMINLANG.support.addresponse} &raquo;" name="postreply" class="button success small" id="postreplybutton" />
            <div class="right">
              <input type="button" value="{$_ADMINLANG.support.insertpredef}" class="button small" id="insertpredef" />
              <input type="button" value="{$_ADMINLANG.support.insertkblink}" class="button small" onclick="window.open('supportticketskbarticle.php','kbartwnd','width=500,height=400,scrollbars=yes')" />
            </div>
            <div id="prerepliescontainer">
              <div class="box">
                <div class="right">
                  <input type="text" id="predefq" size="25" value="{$_ADMINLANG.global.search}" onfocus="this.value=(this.value=='{$_ADMINLANG.global.search}') ? '' : this.value;" onblur="this.value=(this.value=='') ? '{$_ADMINLANG.global.search}' : this.value;" />
                </div>
                <div id="prerepliescontent">{$predefinedreplies}</div>
              </div>
            </div></td>
        </tr>
        <tr>
          <td class="fieldlabel">{$_ADMINLANG.support.attachments}</td>
          <td class="fieldarea"><input type="file" name="attachments[]" size="85" />
            <a class="button small success" href="#" id="addfileupload"><i class="fa fa-plus-circle"></i> {$_ADMINLANG.support.addmore}</a><br />
            <div id="fileuploads"></div></td>
        </tr>
        {if $userid}
        <tr>
          <td class="fieldlabel">{$_ADMINLANG.support.addbilling}</td>
          <td class="fieldarea"><input type="text" name="billingdescription" size="60" value="{$_ADMINLANG.support.toinvoicedes}" onfocus="if(this.value=='{$_ADMINLANG.support.toinvoicedes}')this.value=''" />
            @
            <input type="text" name="billingamount" size="10" value="{$_ADMINLANG.fields.amount}" />
            <select name="billingaction">
              <option value="3" />
              
               {$_ADMINLANG.billableitems.invoiceimmediately}
              
              </option>
              <option value="0" />
              
               {$_ADMINLANG.billableitems.dontinvoicefornow}
              
              </option>
              <option value="1" />
              
               {$_ADMINLANG.billableitems.invoicenextcronrun}
              
              </option>
              <option value="2" />
              
               {$_ADMINLANG.billableitems.addnextinvoice}
              
              </option>
            </select></td>
        </tr>
        {/if}
      </table>
    </form>
  </div>
</div>
<div id="tab1box" class="tabbox">
  <div id="tab_content">
    <form method="post" action="{$smarty.server.PHP_SELF}?action=viewticket&id={$ticketid}">
      <input type="hidden" name="postaction" value="note" />
      <textarea name="message" id="replymessage" rows="14" style="width:100%"></textarea>
      <div class="text-center">
        <input type="submit" value="{$_ADMINLANG.support.addnote}" class="button" name="postreply" />
      </div>
    </form>
  </div>
</div>
<div id="tab2box" class="tabbox">
  <div id="tab_content"><i class="fa fa-circle-o-notch fa-spin"></i> {$_ADMINLANG.global.loading}...</div>
</div>
<div id="tab3box" class="tabbox">
  <div id="tab_content"><i class="fa fa-circle-o-notch fa-spin"></i> {$_ADMINLANG.global.loading}...</div>
</div>
<div id="tab4box" class="tabbox">
  <div id="tab_content"><i class="fa fa-circle-o-notch fa-spin"></i> {$_ADMINLANG.global.loading}...</div>
</div>
<div id="tab5box" class="tabbox">
  <div id="tab_content">
    <form method="post" action="{$smarty.server.PHP_SELF}?action=viewticket&id={$ticketid}">
      <table class="form" width="100%" border="0" cellspacing="2" cellpadding="3">
        <tr>
          <td width="15%" class="fieldlabel">{$_ADMINLANG.support.department}</td>
          <td class="fieldarea"><select name="deptid">
              
          {foreach from=$departments item=department}
              <option value="{$department.id}"{if $department.id eq $deptid} selected{/if}>{$department.name}</option>
              {/foreach}
            
            </select></td>
          <td width="15%" class="fieldlabel">{$_ADMINLANG.fields.clientid}</td>
          <td class="fieldarea"><input type="text" name="userid" size="15" id="clientsearchval" value="{$userid}" />
            <img src="images/icons/delete.png" alt="Cancel" class="absmiddle" id="clientsearchcancel" height="16" width="16">
            <div id="ticketclientsearchresults"></div></td>
        </tr>
        <tr>
          <td class="fieldlabel">{$_ADMINLANG.fields.subject}</td>
          <td class="fieldarea"><input type="text" name="subject" value="{$subject}" style="width:80%"></td>
          <td class="fieldlabel">{$_ADMINLANG.support.flag}</td>
          <td class="fieldarea"><select name="flagto">
              <option value="0">{$_ADMINLANG.global.none}</option>
              
              {foreach from=$staff item=staffmember}
              <option value="{$staffmember.id}"{if $staffmember.id eq $flag} selected{/if}>{$staffmember.name}</option>
              {/foreach}
            
            </select></td>
        </tr>
        <tr>
          <td class="fieldlabel">{$_ADMINLANG.fields.status}</td>
          <td class="fieldarea"><select name="status">
              
          {foreach from=$statuses item=statusitem}<option{if $statusitem.title eq $status} selected{/if} style="color:{$statusitem.color}">{$statusitem.title}
              </option>
              {/foreach}
            
            </select></td>
          <td class="fieldlabel">{$_ADMINLANG.support.priority}</td>
          <td class="fieldarea"><select name="priority">
              <option value="High"{if $priority eq "High"} selected{/if}>{$_ADMINLANG.status.high}</option>
              <option value="Medium"{if $priority eq "Medium"} selected{/if}>{$_ADMINLANG.status.medium}</option>
              <option value="Low"{if $priority eq "Low"} selected{/if}>{$_ADMINLANG.status.low}</option>
            </select></td>
        </tr>
        <tr>
          <td class="fieldlabel">{$_ADMINLANG.support.ccrecipients}</td>
          <td class="fieldarea"><input type="text" name="cc" value="{$cc}" size="40">
            ({$_ADMINLANG.transactions.commaseparated})</td>
          <td class="fieldlabel">{$_ADMINLANG.support.mergeticket}</td>
          <td class="fieldarea"><input type="text" name="mergetid" size="10">
            ({$_ADMINLANG.support.notocombine})</td>
        </tr>
      </table>
      <div class="text-center">
        <input type="submit" value="{$_ADMINLANG.global.savechanges}" class="button">
      </div>
    </form>
  </div>
</div>
<div id="tab6box" class="tabbox">
  <div id="tab_content"><i class="fa fa-circle-o-notch fa-spin"></i> {$_ADMINLANG.global.loading}...</div>
</div>
{if $numnotes}
<div id="privatenotes">
  <h2>{$_ADMINLANG.support.privatestaffnote}</h2>
  {foreach from=$notes item=note}
  <div class="ticketstaffnotes">
    <div><strong>{$note.admin}</strong> | {$note.date}</div>
    <div>{$note.message}</div>
    <div class="text-right"><a class="button small alert" onClick="doDeleteNote('{$note.id}');return false"><i class="fa fa-trash"></i> Delete</a></div>
  </div>
  {/foreach} </div>
{/if}

{if $relatedservices}
<div class="tablebg">
  <table class="datatable" id="relatedservicestbl" width="100%">
    <tr>
      <th>{$_ADMINLANG.fields.product}</th>
      <th>{$_ADMINLANG.fields.amount}</th>
      <th>{$_ADMINLANG.fields.billingcycle}</th>
      <th>{$_ADMINLANG.fields.signupdate}</th>
      <th>{$_ADMINLANG.fields.nextduedate}</th>
      <th>{$_ADMINLANG.fields.status}</th>
    </tr>
    {foreach from=$relatedservices item=relatedservice}
      <tr{if $relatedservice.selected} class="rowhighlight"{/if}>
    
    
      <td>{$relatedservice.name}</td>
      <td>{$relatedservice.amount}</td>
      <td>{$relatedservice.billingcycle}</td>
      <td>{$relatedservice.regdate}</td>
      <td>{$relatedservice.nextduedate}</td>
      <td>{$relatedservice.status}</td>
    </tr>
    {/foreach}
  </table>
</div>
{if $relatedservicesexpand}
<div id="relatedservicesexpand" class="text-right"><a class="button small" href="#" onclick="expandRelServices();return false">{$_ADMINLANG.support.expand}</a></div>
{/if}
{/if}

{if $numnotes || $relatedservices}<br />
{/if}
<form method="post" action="supporttickets.php" id="ticketreplies">
  <input type="hidden" name="id" value="{$ticketid}" />
  <input type="hidden" name="action" value="split" />
  <div id="ticketreplies"> {foreach from=$replies item=reply}
    <div class="{if $reply.admin}staff{/if}reply">
      <div class="leftcol">
        <div class="submitter"> {if $reply.admin}
          <div class="name">{$reply.admin}</div>
          <div class="title">{$_ADMINLANG.support.staff}</div>
          {if $reply.rating} <br />
          {$reply.rating}<br />
          <br />
          {/if}
          
          {else}
          <div class="name">{$reply.clientname}</div>
          <div class="title"> {if $reply.contactid}
            {$_ADMINLANG.fields.contact}
            {elseif $reply.userid}
            {$_ADMINLANG.fields.client}
            {else} <a href="mailto:{$reply.clientemail}">{$reply.clientemail}</a> {/if} </div>
          {if !$reply.userid && !$reply.contactid}
          <input type="button" value="{$_ADMINLANG.support.blocksender}" onclick="window.location='?action=viewticket&id={$ticketid}&blocksender=true&token={$csrfToken}'" class="btn-small" />
          {/if}
          
          {/if} </div>
        <div class="tools">
          <div class="editbtns{if $reply.id}r{$reply.id}{else}t{$ticketid}{/if}">
            <input type="button" value="{$_ADMINLANG.global.edit}" onclick="editTicket('{if $reply.id}r{$reply.id}{else}t{$ticketid}{/if}')" class="btn-small btn-inverse" />
            {if $deleteperm}
            <input type="button" value="{$_ADMINLANG.global.delete}" onclick="{if $reply.id}doDeleteReply('{$reply.id}'){else}doDeleteTicket(){/if}" class="btn-small btn-danger" />
            {/if} </div>
          <div class="editbtns{if $reply.id}r{$reply.id}{else}t{$ticketid}{/if}" style="display:none">
            <input type="button" value="{$_ADMINLANG.global.save}" onclick="editTicketSave('{if $reply.id}r{$reply.id}{else}t{$ticketid}{/if}')" class="btn-small btn-success" />
            <input type="button" value="{$_ADMINLANG.global.cancel}" onclick="editTicketCancel('{if $reply.id}r{$reply.id}{else}t{$ticketid}{/if}')" class="btn-small btn-inverse" />
          </div>
        </div>
      </div>
      <div class="rightcol">
        <div class="quoteicon"><a class="button small" href="#" onClick="quoteTicket('{if !$reply.id}{$ticketid}{/if}','{if $reply.id}{$reply.id}{/if}')"><i class="fa fa-quote-left"></i></a>{if $reply.id}
          <input type="checkbox" name="rids[]" value="{$reply.id}" />
          {/if}</div>
        <div class="postedon">Posted {if $reply.friendlydate}on {$reply.friendlydate}{else}today{/if} at {$reply.friendlytime}</div>
        <div class="msgwrap" id="content{if $reply.id}r{$reply.id}{else}t{$ticketid}{/if}">
          <div class="message"> {$reply.message} </div>
          {if $reply.numattachments}<hr />
          <h3>{$_ADMINLANG.support.attachments}</h3>
          {foreach from=$reply.attachments key=num item=attachment}
          {if $thumbnails}
          <div class="ticketattachmentcontainer"> <a href="../{$attachment.dllink}"><img src="../includes/thumbnail.php?{if $reply.id}rid={$reply.id}{else}tid={$ticketid}{/if}&i={$num}" class="ticketattachmentthumb" /><br />
            <i class="fa fa-paperclip"></i> {$attachment.filename}</a><br />
            <small><a href="{$attachment.deletelink}" onclick="return confirm('{$_ADMINLANG.support.delattachment|escape:'javascript'}')" class="textred">{$_ADMINLANG.support.remove}</a></small> </div>
          {else} <a href="../{$attachment.dllink}"><i class="fa fa-paperclip"></i> {$attachment.filename}</a> <small><a href="{$attachment.deletelink}" onclick="return confirm('{$_ADMINLANG.support.delattachment|escape:'javascript'}')" class="textred">{$_ADMINLANG.support.remove}</a></small><br />
          {/if}
          {/foreach}
          <div class="clear"></div>
          {/if} </div>
      </div>
      <div class="clear"></div>
    </div>
    {/foreach} </div>
  <p class="text-center"><a href="supportticketsprint.php?id={$ticketid}" target="_blank">{$_ADMINLANG.support.viewprintable}</a>{if $repliescount>1}<span class="right">
    <input type="button" value="{$_ADMINLANG.support.splitticketdialogbutton}" onclick="showDialog('splitticket')" class="btn-small" />
    </span>{/if}</p>
  {$splitticketdialog}
  <input type="hidden" name="splitdeptid" id="splitdeptid" />
  <input type="hidden" name="splitsubject" id="splitsubject" />
  <input type="hidden" name="splitpriority" id="splitpriority" />
  <input type="hidden" name="splitnotifyclient" id="splitnotifyclient" />
</form>
