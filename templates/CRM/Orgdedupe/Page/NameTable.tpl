<div id="orgdedupe-names">
  <h3 id="orgdedupe-names-header">Possibly duplicated organisations</h3>
  <table id="orgdedupe-names-table">
    <thead>
      <tr><th>Normalised name</th><th>Number of duplicates</th><th>Contact IDs</th></tr>
    </thead>
    <tbody>

      {foreach from=$data item=eachRecord}
        <tr>
          <td>{$eachRecord.replaced_name}</td>
          <td>{$eachRecord.count}</td>
          <td>{$eachRecord.dupeids}</td>
        </tr>
      {/foreach}

    </tbody>
  </table>
  <br/><br/>
</div>

{literal}
  <script type="text/javascript">
    // Columns in the names table
    var COL_NAME  = 0;
    var COL_COUNT = 1;
    var COL_IDS   = 2;

    cj(document).ready(function() {
      // Expand/collapse the table when the header is clicked
      //cj('#orgdedupe-names-header').click(function() {
      //  cj('#orgdedupe-names-table').toggle();
      //}).css('cursor', 'pointer');

      // Apply DataTables to the table
      cj('#orgdedupe-names-table').dataTable({
        'aoColumnDefs': [
          {'bSortable': true,  'aTargets': [COL_NAME]},
          {'bSortable': true,  'aTargets': [COL_COUNT]},
          {'bSortable': false, 'aTargets': ['_all']},
        ],
        //'aaSorting':    [[COL_COUNT, 'desc']],
        'bFilter':      false,
        'bInfo':        false,
        'bPaginate':    true,
      });
    });
  </script>
{/literal}
