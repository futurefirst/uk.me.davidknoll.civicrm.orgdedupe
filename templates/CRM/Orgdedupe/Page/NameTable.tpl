<table>
<tr><th>Normalised name</th><th># possible duplicates</th><th>IDs</th></tr>

{foreach from=$data item=eachRecord}
	<tr>
	<td>{$eachRecord.replaced_name}</td>
	<td>{$eachRecord.count}</td>
	<td>{$eachRecord.dupeids}</td>
	</tr>
{/foreach}

</table>

