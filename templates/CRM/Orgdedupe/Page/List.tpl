{if $orgdedupe_org_count eq 0}
  <p>There appear to be no duplicate/similar organisation names.</p> 
{else}
  <p>There are {$orgdedupe_dupe_count} possible duplicates among {$orgdedupe_org_count} possible organisations.</p>
  {* {include file="CRM/Orgdedupe/Page/NameTable.tpl" data=$orgdedupe_duped_names} *}
  {include file="CRM/Orgdedupe/Page/PairTable.tpl" data=$orgdedupe_poss_pairs}
{/if}
