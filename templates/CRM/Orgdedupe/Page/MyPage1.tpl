{if $orgdedupe_org_count eq 0}
	<p>There appear to be no duplicate/similar organisation names.</p> 
{else}
  <p>There are {$orgdedupe_dupe_count} possible duplicates among {$orgdedupe_org_count} possible organisations.</p>

	<h3>Possible organisations</h3>
	{include file="CRM/Orgdedupe/Page/NameTable.tpl" data=$orgdedupe_duped_names}

	<h3>Possible pairs</h3>
	{include file="CRM/Orgdedupe/Page/PairTable.tpl" data=$orgdedupe_poss_pairs}
{/if}

