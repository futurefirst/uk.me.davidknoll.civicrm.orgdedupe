<?php
/**
 * Organisation Name De-duplicator
 *
 * @author    David Knoll <david@futurefirst.org.uk>
 * @copyright 2014 Future First
 * @license   http://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0
 */

require_once 'CRM/Core/Page.php';

class CRM_Orgdedupe_Page_List extends CRM_Core_Page {

  // Prefix to variable names assigned for the template
  const PREFIX = 'orgdedupe_';

  /**
   * @var array $displayNameReplacements
   *   Replacements to perform on organisation names,
   *   in order that reasonably similar ones come out the same.
   */
  protected static $displayNameReplacements = array(
    '&'   => 'and',
    '\''  => '',
    '.'   => '',
    ' '   => '',
    '-'   => '',
    'inc' => '',
  );

  /**
   * @var array $dupedNames
   *   Names that have possible duplicates
   * @var array $possPairs
   *   Pairs of duplicate records, for all those names
   * @var int $orgCount
   *   Count organisations that have possible duplicates
   * @var int $dupeCount
   *   Count all the duplicates they have
   */
  protected $dupedNames = array();
  protected $possPairs  = array();
  protected $orgCount   = 0;
  protected $dupeCount  = 0;

  /**
   * Construct the nested REPLACE for queries involving the normalised name.
   *
   * @param string $input
   *   String or identifier to apply replacements to.
   * @param array $replacements
   *   Array mapping substrings to their replacements.
   * @return string
   *   A nested REPLACE() to be included in an SQL query.
   */
  protected static function constructReplace($input, $replacements) {
    $input = CRM_Core_DAO::escapeString($input);
    foreach ($replacements as $key => $value) {
      $params = array(
        array($key,   'String'),  // What to replace
        array($value, 'String'),  // What to replace it with
      );
      $input = CRM_Core_DAO::composeQuery(" REPLACE($input, %0, %1) ", $params);
    }
    return $input;
  }

  /**
   * Construct a query for all normalised names that have possible duplicates.
   *
   * Query returns the normalised name, duplicate count, and concatenated
   * list of IDs of duplicates.
   *
   * @return string
   *   SQL query string for the above.
   */
  protected static function constructQueryDupedNames() {
    $replace = self::constructReplace('`display_name`', self::$displayNameReplacements);
    return "
      SELECT $replace              AS `replaced_name`,
             COUNT(`display_name`) AS `count`,
             GROUP_CONCAT(DISTINCT `id` ORDER BY `id` ASC SEPARATOR ', ') AS `dupeids`

        FROM `civicrm_contact`

       WHERE `contact_type` LIKE '%Organization%'
         AND `is_deleted`   IS   NOT TRUE

    GROUP BY `replaced_name`
      HAVING COUNT(`display_name`) > 1

    ORDER BY `count`         DESC,
             `replaced_name` ASC
    ";
  }

  /**
   * Construct a query for all IDs/names matching a given normalised name.
   *
   * @param string $replacedName
   *   Normalised name for which to retrieve all possible duplicates
   * @return string
   *   SQL query string for the above.
   */
  protected static function constructQueryIdsForName($replacedName) {
    $replace = self::constructReplace('`display_name`', self::$displayNameReplacements);
    $replacedName = CRM_Core_DAO::escapeString($replacedName);
    return "
      SELECT `civicrm_contact`.`id`           AS `id`,
             `civicrm_contact`.`display_name` AS `display_name`,
             `civicrm_address`.`postal_code`  AS `postal_code`
        FROM `civicrm_contact`
   LEFT JOIN `civicrm_address`
          ON `civicrm_address`.`contact_id`  = `civicrm_contact`.`id`
         AND `civicrm_address`.`is_primary` IS TRUE
       WHERE `contact_type` LIKE '%Organization%'
         AND `is_deleted`   IS   NOT TRUE
         AND $replace       =    '$replacedName'
    ORDER BY `id` ASC
    ";
  }

  /**
   * Retrieve the list of normalised names with duplicates, and count them.
   */
  protected function retrieveNamesAndCounts() {
    $query = self::constructQueryDupedNames();
    $dao = CRM_Core_DAO::executeQuery($query);

    while ($dao->fetch()) {
      $this->dupedNames[] = array(
        'replaced_name' => $dao->replaced_name,
        'count'         => $dao->count,
        'dupeids'       => $dao->dupeids,
      );
      $this->orgCount++;
      $this->dupeCount += $dao->count;
    }
  }

  /**
   * Construct possible pairs of duplicate records for a given normalised name.
   *
   * Excludes pairs that have previously been marked as not duplicates.
   *
   * @param string $replacedName
   *   Normalised name for which to retrieve possible pairs of duplicates.
   */
  protected function retrievePairsForName($replacedName) {
    $query = self::constructQueryIdsForName($replacedName);
    $dao = CRM_Core_DAO::executeQuery($query);
    $dupesForName = array();

    // Copy duplicates for that name into an array.
    while ($dao->fetch()) {
      $dupesForName[] = array(
        'id'           => $dao->id,
        'display_name' => $dao->display_name,
        'postal_code'  => $dao->postal_code,
      );
    }
    $countDupes = count($dupesForName);

    // Add as a pair, each duplicate with the one before it.
    for ($countPairs = 1; $countPairs < $countDupes; $countPairs++) {
      // Check whether this pair has been marked as non-duplicate
      $exception = new CRM_Dedupe_DAO_Exception();
      // Exception pairs are stored with contact_id2 > contact_id1, make sure
      $exception->contact_id1 = min($dupesForName[$countPairs-1]['id'], $dupesForName[$countPairs]['id']);
      $exception->contact_id2 = max($dupesForName[$countPairs-1]['id'], $dupesForName[$countPairs]['id']);
      if ($exception->find()) {
        continue;
      }

      $this->possPairs[] = array(
        'id_a'           => $dupesForName[$countPairs-1]['id'],
        'display_name_a' => $dupesForName[$countPairs-1]['display_name'],
        'postal_code_a'  => $dupesForName[$countPairs-1]['postal_code'],
        'id_b'           => $dupesForName[$countPairs]['id'],
        'display_name_b' => $dupesForName[$countPairs]['display_name'],
        'postal_code_b'  => $dupesForName[$countPairs]['postal_code'],
      );
    }
  }

  /**
   * Run queries and assign results for the page template.
   */
  public function run() {
    $this->retrieveNamesAndCounts();
    foreach ($this->dupedNames as $rec) {
      $this->retrievePairsForName($rec['replaced_name']);
    }

    $this->assign(self::PREFIX . 'duped_names', $this->dupedNames);
    $this->assign(self::PREFIX . 'org_count',   $this->orgCount);
    $this->assign(self::PREFIX . 'dupe_count',  $this->dupeCount);
    $this->assign(self::PREFIX . 'poss_pairs',  $this->possPairs);

    parent::run();
  }
}
