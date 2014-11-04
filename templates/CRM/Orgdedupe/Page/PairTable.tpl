<div id="orgdedupe-pairs">
  <h3 id="orgdedupe-pairs-header">{ts}Possible pairs{/ts}</h3>
  <table id="orgdedupe-pairs-table">
    <thead>
      <tr>
        <th>{ts}Contact ID A{/ts}</th>
        <th>{ts}Display name A{/ts}</th>
        <th>{ts}Postal code A{/ts}</th>
        <th>{ts}Contact ID B{/ts}</th>
        <th>{ts}Display name B{/ts}</th>
        <th>{ts}Postal code B{/ts}</th>
        <th>{ts}Action{/ts}</th>
      </tr>
    </thead>
    <tbody>

      {foreach from=$data item=eachRecord}
        <tr class="orgdedupe-pairs-row" data-id_a="{$eachRecord.id_a}" data-id_b="{$eachRecord.id_b}">
          <td>{$eachRecord.id_a}</td>
          <td>{$eachRecord.display_name_a}</td>
          <td>{$eachRecord.postal_code_a}</td>
          <td>{$eachRecord.id_b}</td>
          <td>{$eachRecord.display_name_b}</td>
          <td>{$eachRecord.postal_code_b}</td>
          <td>
            <a title="{ts}Merge these two organisation records{/ts}" href="{crmURL p='civicrm/contact/merge' q="reset=1&cid=`$eachRecord.id_a`&oid=`$eachRecord.id_b`&action=update&rgid=16"}">{ts}Merge{/ts}</a>
            |
            <a title="{ts}Mark these two organisations as not duplicates{/ts}" href="#" onClick="processDupes({$eachRecord.id_a}, {$eachRecord.id_b}, 'dupe-nondupe'); return false;">{ts}Not duplicates{/ts}</a>
          </td>
        </tr>
      {/foreach}

    </tbody>
  </table>
  <br/><br/>
</div>

{include file='CRM/common/dedupe.tpl'}
{literal}
  <script type="text/javascript">
    // Columns in the pairs table
    var COL_ID_A       = 0;
    var COL_NAME_A     = 1;
    var COL_POSTCODE_A = 2;
    var COL_ID_B       = 3;
    var COL_NAME_B     = 4;
    var COL_POSTCODE_B = 5;
    var COL_ACTION     = 6;

    cj(document).ready(function() {
      // Expand/collapse the table when the header is clicked
      //cj('#orgdedupe-pairs-header').click(function() {
      //  cj('#orgdedupe-pairs-table').toggle();
      //}).css('cursor', 'pointer');

      // Apply DataTables to the table
      // Allow proper numeric sort on the IDs while still making them links
      cj('#orgdedupe-pairs-table').dataTable({
        'aoColumnDefs': [
          {'aTargets': [COL_ID_A], 'bSortable': true, 'bUseRendered': false, 'sType': 'numeric', 'fnRender': function(data) {
            var aCurContents = data.aData[data.iDataColumn];
            var contactUrl   = '{/literal}{crmURL p='civicrm/contact/view' q='reset=1&cid='}{literal}' + aCurContents;
            var newContents  = '<a title="{/literal}{ts}View contact{/ts}{literal} ' + aCurContents + '" href="' + contactUrl + '">' + aCurContents + '</a>';
            return newContents;
          }},
          {'bSortable': true,  'aTargets': [COL_NAME_A]},
          {'bSortable': true,  'aTargets': [COL_POSTCODE_A]},
          {'aTargets': [COL_ID_B], 'bSortable': true, 'bUseRendered': false, 'sType': 'numeric', 'fnRender': function(data) {
            var aCurContents = data.aData[data.iDataColumn];
            var contactUrl   = '{/literal}{crmURL p='civicrm/contact/view' q='reset=1&cid='}{literal}' + aCurContents;
            var newContents  = '<a title="{/literal}{ts}View contact{/ts}{literal} ' + aCurContents + '" href="' + contactUrl + '">' + aCurContents + '</a>';
            return newContents;
          }},
          {'bSortable': true,  'aTargets': [COL_NAME_B]},
          {'bSortable': true,  'aTargets': [COL_POSTCODE_B]},
          {'bSortable': false, 'aTargets': ['_all']}
        ],
        'aaSorting':    [[COL_NAME_A, 'asc']],
        'bFilter':      false,
        'bInfo':        false,
        'bPaginate':    true
      });
    });
  </script>
{/literal}
