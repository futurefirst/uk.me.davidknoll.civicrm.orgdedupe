{if $orgdedupe_org_count eq 0}
  <p>{ts}There appear to be no duplicate/similar organisation names.{/ts}</p> 
{else}
  <p>{ts 1=$orgdedupe_dupe_count 2=$orgdedupe_org_count}There are %1 possible duplicates among %2 possible organisations.{/ts}</p>
  {* {include file="CRM/Orgdedupe/Page/NameTable.tpl" data=$orgdedupe_duped_names} *}
  {include file="CRM/Orgdedupe/Page/PairTable.tpl" data=$orgdedupe_poss_pairs}
{/if}
