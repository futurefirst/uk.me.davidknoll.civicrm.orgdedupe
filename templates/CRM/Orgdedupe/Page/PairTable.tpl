<table>
<tr><th>ID</th><th>First display name</th><th>ID</th><th>Second display name</th><th>Action</th></tr>

{foreach from=$data item=eachRecord}
	<tr>
  	<td>{$eachRecord.id_a}</td>
	  <td>{$eachRecord.display_name_a}</td>
	  <td>{$eachRecord.id_b}</td>
	  <td>{$eachRecord.display_name_b}</td>
	  <td><a title="Merge these two organisation records" href="{crmURL p='civicrm/contact/merge' q="reset=1&cid=`$eachRecord.id_a`&oid=`$eachRecord.id_b`&action=update&rgid=16"}">Merge</a></td>
	</tr>
{/foreach}

</table>

