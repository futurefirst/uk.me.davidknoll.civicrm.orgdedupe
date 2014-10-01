<div id="orgdedupe-pairs">
  <h3 id="orgdedupe-pairs-header">{ts}Possible pairs{/ts}</h3>
  <table id="orgdedupe-pairs-table">
    <thead>
      <tr><th>{ts}Contact ID A{/ts}</th><th>{ts}Display name A{/ts}</th><th>{ts}Contact ID B{/ts}</th><th>{ts}Display name B{/ts}</th><th>{ts}Action{/ts}</th></tr>
    </thead>
    <tbody>

      {foreach from=$data item=eachRecord}
        <tr>
          <td><a title="{ts}View contact{/ts} {$eachRecord.id_a}" href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$eachRecord.id_a`"}">{$eachRecord.id_a}</a></td>
          <td>{$eachRecord.display_name_a}</td>
          <td><a title="{ts}View contact{/ts} {$eachRecord.id_b}" href="{crmURL p='civicrm/contact/view' q="reset=1&cid=`$eachRecord.id_b`"}">{$eachRecord.id_b}</a></td>
          <td>{$eachRecord.display_name_b}</td>
          <td><a title="{ts}Merge these two organisation records{/ts}" href="{crmURL p='civicrm/contact/merge' q="reset=1&cid=`$eachRecord.id_a`&oid=`$eachRecord.id_b`&action=update&rgid=16"}">{ts}Merge{/ts}</a></td>
        </tr>
      {/foreach}

    </tbody>
  </table>
  <br/><br/>
</div>

{literal}
  <script type="text/javascript">
    // Columns in the pairs table
    var COL_ID_A   = 0;
    var COL_NAME_A = 1;
    var COL_ID_B   = 2;
    var COL_NAME_B = 3;
    var COL_ACTION = 4;

    cj(document).ready(function() {
      // Expand/collapse the table when the header is clicked
      //cj('#orgdedupe-pairs-header').click(function() {
      //  cj('#orgdedupe-pairs-table').toggle();
      //}).css('cursor', 'pointer');

      // Apply DataTables to the table
      cj('#orgdedupe-pairs-table').dataTable({
        'aoColumnDefs': [
          {'bSortable': true,  'aTargets': [COL_ID_A]},
          {'bSortable': true,  'aTargets': [COL_NAME_A]},
          {'bSortable': true,  'aTargets': [COL_ID_B]},
          {'bSortable': true,  'aTargets': [COL_NAME_B]},
          {'bSortable': false, 'aTargets': ['_all']},
        ],
        'aaSorting':    [[COL_NAME_A, 'asc']],
        'bFilter':      false,
        'bInfo':        false,
        'bPaginate':    true,
      });
    });
  </script>
{/literal}
