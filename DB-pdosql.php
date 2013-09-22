<?php
/**
 * Command Console
 *
 * SQL Database Service provider
 *  
 * Console script for Battlefield Play4Free MH Clan.
 * Interprets in-game chat messages to commands.
 * 
 * @category BFP4F
 * @package  cmdcon
 * @author   piqus <ovirendo@gmail.com>
 * @license  RESTRICTED.
 * @version  0.1
 * @link     http://piqus.pl/
 *
 * purgeChatLogTable $config['db.table_chat_log'], $config['db.truncate_chat_log']
 */

/**
* SQL DB Service Provider
*/
class DB
{
	/**
	 * Stores PDO object.
	 * 
	 * @var object
	 */
	private static $_db;
	
	/**
	 * Some kind of Contructor
	 *
	 * Initializes connection and instantiate PDO.
	 */
	public static function init()
	{
		$dsn = 'mysql:host='.DB_HOST.';port='.DB_PORT.';dbname='.DB_NAME;
		try {
			self::$_db = new PDO($dsn, DB_USER, DB_PASS);
		} catch (PDOException $e) {
			echo 'Connection failed: ' . $e->getMessage();
		}

	}

	// Get latest $limit messages
	public static function getLatestMessages($table, $limit)
	{

		$query = "SELECT * FROM {$table} ORDER BY datetime LIMIT :limit";

		$stmt = self::$_db->prepare($query);
		$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
			
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_OBJ);
	}

	public static function lookupCommand($table_user_commands, $table_commands, $table_admins, $command_alias, $origin)
	{
		$response = array(
			'status' => false, 
			'deniedType' => 0,
			);

    	$query = "SELECT * FROM {$table_commands} JOIN {$table_user_commands} USING(command_id) ";
    	$query .= "WHERE alias = :command_alias AND enabled = 1 AND active = 1";

    	$stmt = self::$_db->prepare($query);
		$stmt->bindParam(':command_alias', $command_alias, PDO::PARAM_STR);
		$stmt->execute();

		$cmdlet = $stmt->fetch(PDO::FETCH_OBJ);

		if (!isset($cmdlet) || empty($cmdlet) || $cmdlet === false) {
			$response['deniedType'] = 1;
			return (object) $response;
		}

		$admlet = null;
		if ($cmdlet->required_level > 0) {
	    	$query = "SELECT * FROM {$table_admins} WHERE soldier_name = :soldier_name AND active = 1 AND level = :level";

			$stmt = self::$_db->prepare($query);
			$stmt->bindParam(':soldier_name', $origin, PDO::PARAM_STR);
			$stmt->bindParam(':level', $cmdlet->required_level, PDO::PARAM_INT);
			$stmt->execute();
			$admlet = $stmt->fetch(PDO::FETCH_OBJ);

			if (!isset($admlet) || empty($admlet) || $admlet === false) {
				$response['deniedType'] = 2;
				return (object) $response;
			}
		}

		$response = array(
			'status' => true,
			'cmd' => $cmdlet,
			'adm' => $admlet,
			);
		
		return (object) $response;
	}

	public static function addChatLog($table, $message, $origin, $time, $type)
	{
		$query  = "INSERT INTO " . $table . " ";
		$query .= "(origin, type, message, time) ";
		$query .= "VALUES ";
		$query .= "(:origin, :type, :message, :time);";

		$stmt = self::$_db->prepare($query);

		// $date = date("Y-m-d H:i:s");

		$stmt->bindParam(':origin', $origin, PDO::PARAM_STR);
		$stmt->bindParam(':type', $type, PDO::PARAM_INT);
		$stmt->bindParam(':message', $message, PDO::PARAM_STR);
		// $date = date("Y-m-d H:i:s");
		$stmt->bindParam(':time', $time, PDO::PARAM_STR);

		return $stmt->execute();
	}

	public static function reportIssue($table, $reporter, $issue, $reporter_profile, $reporter_soldier)
	{
		$query  = "INSERT INTO " . $table . " ";
		$query .= "(reporter, r_profile_id, r_soldier_id, issue, date_created) ";
		$query .= "VALUES ";
		$query .= "(:reporter, :r_profile_id, :r_soldier_id, :issue, :date);";

		$stmt = self::$_db->prepare($query);

		$stmt->bindParam(':reporter', $reporter, PDO::PARAM_STR);
		$stmt->bindParam(':r_profile_id', $reporter_profile, PDO::PARAM_STR);
		$stmt->bindParam(':r_soldier_id', $reporter_soldier, PDO::PARAM_STR);
		$stmt->bindParam(':issue', $issue, PDO::PARAM_STR);
		$date = date("Y-m-d H:i:s");
		$stmt->bindParam(':date', $date, PDO::PARAM_STR);

		return $stmt->execute();
	}

	public static function reportPlayer($table, $reporter, $suspect, $issue, $reporter_profile, $reporter_soldier, $suspect_profile, $suspect_soldier)
	{
		$query  = "INSERT INTO " . $table . " ";
		$query .= "(reporter, suspect, r_profile_id, r_soldier_id, s_profile_id, s_soldier_id, issue, date_created) ";
		$query .= "VALUES ";
		$query .= "(:reporter, :suspect, :r_profile_id, :r_soldier_id, :s_profile_id, :s_soldier_id, :issue, :date);";

		$stmt = self::$_db->prepare($query);

		$stmt->bindParam(':reporter', $reporter, PDO::PARAM_STR);
		$stmt->bindParam(':suspect', $suspect, PDO::PARAM_STR);
		$stmt->bindParam(':issue', $issue, PDO::PARAM_STR);
		$stmt->bindParam(':r_profile_id', $reporter_profile, PDO::PARAM_STR);
		$stmt->bindParam(':r_soldier_id', $reporter_soldier, PDO::PARAM_STR);
		$stmt->bindParam(':s_profile_id', $suspect_profile, PDO::PARAM_STR);
		$stmt->bindParam(':s_soldier_id', $suspect_soldier, PDO::PARAM_STR);
		$date = date("Y-m-d H:i:s");
		$stmt->bindParam(':date', $date, PDO::PARAM_STR);
		return $stmt->execute();
	}

	public static function getOnlineAdmins($profileIds, $soldierIds)
	{
		$query = "SELECT soldier_name FROM cc_admins ";
		$query .= "WHERE profile_id IN (".implode(", ", $profileIds).") AND soldier_id IN (".implode(", ", $soldierIds).") AND active = 1; ";

		$stmt = self::$_db->prepare($query);
		if ($stmt->execute()) {
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} else {
			return false;
		}
	}

	public static function getAllAdmins()
	{
		$query = "SELECT soldier_name FROM cc_admins ";
		$query .= "WHERE active = 1; ";

		$stmt = self::$_db->prepare($query);
		if ($stmt->execute()) {
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} else {
			return false;
		}
	}

	public static function getSpecifiedAdmins($soldierName)
	{
		$query = "SELECT soldier_name FROM cc_admins ";
		$query .= "WHERE active = 1 AND soldier_name LIKE :soldier_name; ";

		$stmt = self::$_db->prepare($query);
		$stmt->bindParam(':soldier_name', $soldierName, PDO::PARAM_STR);
		if ($stmt->execute()) {
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} else {
			return false;
		}		
	}

	public static function addMisbehavingPlayer($table, $soldier_name, $time, $reason, $profile_id, $soldier_id)
	{
		$query  = "INSERT INTO " . $table . " ";
		$query .= "(soldier_name, profile_id, soldier_id, reason, expiration_date) ";
		$query .= "VALUES ";
		$query .= "(:soldier_name, :profile_id, :soldier_id, :reason, :expiration_date);";

		$stmt = self::$_db->prepare($query);

		$stmt->bindParam(':soldier_name', $soldier_name, PDO::PARAM_STR);
		$stmt->bindParam(':profile_id', $profile_id, PDO::PARAM_STR);
		$stmt->bindParam(':soldier_id', $soldier_id, PDO::PARAM_STR);
		$stmt->bindParam(':reason', $reason, PDO::PARAM_STR);
		$date = $date = new DateTime();
		$expiration_date = $date->modify("+{$time['duration']} {$time['type']}")->format('Y-m-d H:i:s');
		$stmt->bindParam(':expiration_date', $expiration_date, PDO::PARAM_STR);
		
		return $stmt->execute();
	}

	public static function getCommandsByLevel($table, $level = 0)
	{
		$query = "SELECT CONCAT('!', alias) FROM {$table} WHERE required_level <= :level";
		$stmt = self::$_db->prepare($query);
		$stmt->bindParam(':level', $level, PDO::PARAM_INT);
		if ($stmt->execute()) {
			return $stmt->fetchAll(PDO::FETCH_COLUMN);
		} else {
			return false;
		}	
	}

	public static function addErrorLog($table, $context)
	{
		$query = "INSERT INTO {$table} (context) VALUES :context";
		$stmt = self::$_db->prepare($query);
		$stmt->bindParam(":context", $context, PDO::PARAM_STR);
		return $stmt->execute();
	}

	
}
?>