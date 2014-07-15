<?php

require_once 'CRM/Core/Page.php';

/*
const ORGDEDUPE_QUERY = "SELECT display_name, id AS org_id, count(display_name) AS count, contact_sub_type
  FROM civicrm_contact
  WHERE contact_type = \"Organization\" AND is_deleted = \"0\"
  GROUP BY REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(display_name, \"&\", \"and\"), \"'\", \"\"), \".\", \"\"), \" \", \"\"), \"-\", \"\"), \"inc\", \"\")
  HAVING count(display_name) > 1
  ORDER BY count DESC, display_name;";

const ORGDEDUPE_QUERY = "SELECT REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(display_name, \"&\", \"and\"), \"'\", \"\"), \".\", \"\"), \" \", \"\"), \"-\", \"\"), \"inc\", \"\") AS replaced_name, count(display_name) AS count, group_concat(DISTINCT id ORDER BY id ASC SEPARATOR \", \") AS dupeids
  FROM civicrm_contact
  WHERE contact_type = \"Organization\" AND is_deleted = \"0\"
  GROUP BY replaced_name
  HAVING count(display_name) > 1
  ORDER BY count DESC, replaced_name;";
          
const IDSFORNAME_QUERY_1 = "SELECT id, display_name
  FROM civicrm_contact
  WHERE contact_type = \"Organization\" AND is_deleted = \"0\"
  AND REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(display_name, \"&\", \"and\"), \"'\", \"\"), \".\", \"\"), \" \", \"\"), \"-\", \"\"), \"inc\", \"\") = \"";
*/

class CRM_Orgdedupe_Page_MyPage1 extends CRM_Core_Page {

  // Replacements to perform on organisation names,
  // in order that reasonably similar ones come out the same.
  private static $displayNameReplacements = array(
    '&' => 'and',
    '\'' => '',
    '.' => '',
    ' ' => '',
    '-' => '',
    'inc' => ''
  );

  private $dupedNames = array();  // Names that have possible duplicates
  private $possPairs = array();   // Pairs of duplicate records, for all those names

  // Construct the nested REPLACE for queries involving the normalised name.
  private static function constructReplace($input, $replacements) {
    foreach ($replacements as $key => $value) {
      $input = "REPLACE($input, \"$key\", \"$value\")";
    }
    return $input;
  }

  // Construct a query for all normalised names that have possible duplicates.
  private static function constructQueryDupedNames() {
    $replace = CRM_Orgdedupe_Page_MyPage1::constructReplace('display_name', CRM_Orgdedupe_Page_MyPage1::$displayNameReplacements);
    return "SELECT $replace AS replaced_name, count(display_name) AS count,
      group_concat(DISTINCT id ORDER BY id ASC SEPARATOR \", \") AS dupeids
      FROM civicrm_contact
      WHERE contact_type = \"Organization\" AND is_deleted = \"0\"
      GROUP BY replaced_name
      HAVING count(display_name) > 1
      ORDER BY count DESC, replaced_name;";
  }

  // Construct a query for all IDs/names matching a given normalised name.
  private static function constructQueryIdsForName($replacedName) {
    $replace = CRM_Orgdedupe_Page_MyPage1::constructReplace('display_name', CRM_Orgdedupe_Page_MyPage1::$displayNameReplacements);
    return "SELECT id, display_name
      FROM civicrm_contact
      WHERE contact_type = \"Organization\" AND is_deleted = \"0\"
      AND $replace = \"$replacedName\"
      ORDER BY id ASC;";
  }

  // Retrieve the list of normalised names with duplicates,
  // and assign it for the page template to see.
  private function retrieveNamesAndCounts($prefix) {
    $query = CRM_Orgdedupe_Page_MyPage1::constructQueryDupedNames();
    $dao = CRM_Core_DAO::executeQuery($query);
    $orgCount = 0;  // Count organisations that have possible duplicates
    $dupeCount = 0; // Count all the duplicates they have

    while ($dao->fetch()) {
      $this->dupedNames[] = array(
        'replaced_name' => $dao->replaced_name,
        'count' => $dao->count,
        'dupeids' => $dao->dupeids
      );
      $orgCount++;
      $dupeCount += $dao->count;
    }

    $this->assign($prefix.'duped_names', $this->dupedNames);
    $this->assign($prefix.'org_count', $orgCount);
    $this->assign($prefix.'dupe_count', $dupeCount);
  }

  // Retrieve the list of IDs/names matching a given normalised name,
  // construct possible pairs of duplicate records, add those pairs to a big list,
  // and assign it for the page template to see.
  private function retrievePairsForName($replacedName, $prefix) {
    $query = CRM_Orgdedupe_Page_MyPage1::constructQueryIdsForName($replacedName);
    $dao = CRM_Core_DAO::executeQuery($query);
    $dupesForName = array();

    $iRow = 0;
    while ($dao->fetch()) {
      $dupesForName[$iRow] = array('id' => $dao->id, 'display_name' => $dao->display_name);
      $iRow++;
    }

    $jRow = 1;
    while ($jRow < $iRow) {
      $this->possPairs[] = array(
        'id_a' => $dupesForName[$jRow-1]['id'],
        'display_name_a' => $dupesForName[$jRow-1]['display_name'],
        'id_b' => $dupesForName[$jRow]['id'],
        'display_name_b' => $dupesForName[$jRow]['display_name']
      );
      $jRow++;
    }

    $this->assign($prefix.'poss_pairs', $this->possPairs);
  }

  // Retrieve duplicated names and retrieve possible pairs for each.
  function run() {
    $this->retrieveNamesAndCounts('orgdedupe_');
    foreach($this->dupedNames as $rec) {
      $this->retrievePairsForName($rec['replaced_name'], 'orgdedupe_' );
    }
    parent::run();
  }
}

