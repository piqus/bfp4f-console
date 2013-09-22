<?php
/**
 * Command Console
 *
 * Common Commands.
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
 */


class CommonCommands extends Commands
{
	public function sendMsg($message='')
	{
		$output = self::enchanceMessage($message);
		if (empty($output)) {
			$output = $message;
		}
		self::$rcc->send($output);
		return true;
	}

	public function showPing()
	{
		foreach (self::$players as $id => $player) {
			if (self::$msg->origin == $player->name) {
				$output = str_replace("%ping%", $player->ping, self::$config['msg.ping.message']);
				$output = str_replace("%player%", $player->name, $output);
				self::$rcc->send($output);
				return true;
			}
		}
		return false;
	}
	
	public function report($soldier_name, $issue='')
	{
		var_dump(func_get_args());
		$author = null;
		// Get author of report
		foreach (self::$players as $id => $player) {
			if (self::$msg->origin == $player->name) {
				$author = $player;
				break;
			}
		}
		if (empty($author)) {
			self::$rcc->sendPlayer(self::$msg->index, self::$config['msg.report.not_sent']);
			return false;
		}

		// Check if it player report or just issue report
		if (isset($soldier_name) && !empty($soldier_name) && $reported = self::findPlayer($soldier_name)) {
			// PlayerReport
			if (DB::reportPlayer(self::$config['db.table_reports'], $author->name, $reported->name, $issue, $author->nucleusId, $author->cdKeyHash, $reported->nucleusId, $reported->cdKeyHash)) {
				self::$rcc->sendPlayer(self::$msg->index, self::$config['msg.player_report.sent']);
				return true;
			} else {
				self::$rcc->sendPlayer(self::$msg->index, self::$config['msg.player_report.not_sent']);
				return false;					
			}
		} elseif (isset($soldier_name) && !empty($soldier_name)) {
			$issue = $soldier_name . " " . $issue;
			if (DB::reportIssue(self::$config['db.table_reports'], $author->name, $issue, $author->nucleusId, $author->cdKeyHash)) {
				self::$rcc->sendPlayer(self::$msg->index, self::$config['msg.issue_report.sent']);
				return true;
			} else {
				self::$rcc->sendPlayer(self::$msg->index, self::$config['msg.issue_report.not_sent']);
				return false;					
			}
		} else {
			// Problem with report
			self::$rcc->sendPlayer(self::$msg->index, self::$config['msg.report.not_sent']);
			return false;
		}
	}

	public function kickPlayer($soldier_name, $reason = null)
	{
		if (($player = self::findPlayer($soldier_name))!==false) {
			self::$rcp->kick($player->index, $reason);
			return true;
		} else {
			return false;
		}
	}

	public function banPlayer($soldier_name, $time = "perm", $reason = null)
	{		
		if (($player = self::findPlayer($soldier_name))!==false) {
			self::$rcp->ban($player->index, $time, $reason);
			return true;
		} else {
			return false;
		}
	}

	public function timeKickPlayer($soldier_name, $time, $reason = null)
	{
		$time = self::convertTime($time);
		if (($player = self::findPlayer($soldier_name))!==false) {
			$output = self::enchanceMessage($reason);
			DB::addMisbehavingPlayer(self::$config['db.table_kicklog'], $player->name, $time, $reason, $player->profileId, $player->cdKeyHash);
			self::$rcp->kick($player->index, $reason);
			return true;
		} else {
			return false;
		}
	}

	public function warnPlayer($soldier_name, $reason = null)
	{
		if (($player = self::findPlayer($soldier_name))!==false) {
			$output = self::enchanceMessage($reason);
			self::$rcc->send($output);
			return true;
		} else {
			return false;
		}
	}

	public function sendPM($recipient, $message='')
	{
		if (($player = self::findPlayer($recipient))!==false) {
			$output = self::enchanceMessage($message);
			if (empty($output)) {
				$output = $message;
			}
			self::$rcc->sendPlayer($player->index, $output);
			return true;
		} else {
			return false;
		}
	}

	public function switchAutobalance($status = "1")
	{
		switch ($status) {
			case '1':
			case 'on':
				self::$rcp->switchAutobalance("1");
				self::$rcc->send(self::$config['msg.ab.status_on']);
				return true;
				break;
			case '0':
			case 'off':
				self::$rcp->switchAutobalance("0");
				self::$rcc->send(self::$config['msg.ab.status_off']);
				return true;
				break;
			
			default:
				self::$rcc->sendPlayer(self::$msg->origin, self::$config['msg.ab.wrong_param']);
				return false;
				break;
		}
	}

	public function restart()
	{
		self::$srv->restartMap();
		if (isset(self::$config['msg.restart']) && !empty(self::$config['msg.restart']) && self::$config['msg.restart'] != false) {
			self::$rcc->send(self::$config['msg.restart']);
		}		
		return true;
	}

	public function nextMap()
	{
		self::$srv->skip();
		return true;
	}

	public function pause()
	{
		if (isset(self::$config['msg.pause']) && !empty(self::$config['msg.pause']) && self::$config['msg.pause'] != false) {
			self::$rcc->send(self::$config['msg.pause']);
		}
		self::$srv->pause();
		return true;
	}

	public function unpause()
	{
		if (isset(self::$config['msg.unpause']) && !empty(self::$config['msg.unpause']) && self::$config['msg.unpause'] != false) {
			self::$rcc->send(self::$config['msg.unpause']);
		}
		self::$srv->unpause();
		return true;
	}

	public function togglePause()
	{
		if (isset(self::$config['msg.toggle_pause']) && !empty(self::$config['msg.toggle_pause']) && self::$config['msg.toggle_pause'] != false) {
			self::$rcc->send(self::$config['msg.toggle_pause']);
		}
		self::$srv->togglePause();
		return true;
	}

	public function changeMap($type, $map)
	{
		$game_type = null;
		switch (true) {
			case strcasecmp($type, "gpm_cq") != true:
			case strcasecmp($type, "cq") != true:
			case strcasecmp($type, "gpm_nv") != true:
			case strcasecmp($type, "nv") != true:
			case strcasecmp($type, "conquest") != true:
				$game_type = "gpm_cq";
				break;
			case strcasecmp($type, "gpm_ti") != true:
			case strcasecmp($type, "ti") != true:
			case strcasecmp($type, "titan") != true:
				$game_type = "gpm_ti";
				break;
			case strcasecmp($type, "gpm_coop") != true:
			case strcasecmp($type, "coop") != true:
				$game_type = "gpm_coop";
				break;
			case strcasecmp($type, "gpm_ca") != true:
			case strcasecmp($type, "ca") != true:
			case strcasecmp($type, "conqassault") != true:
			case strcasecmp($type, "conquestassault") != true:
			case strcasecmp($type, "conquest_assault") != true:
				$game_type = "gpm_ca";
				break;
			case strcasecmp($type, "gpm_hoth") != true:
			case strcasecmp($type, "hoth") != true:
			case strcasecmp($type, "v2") != true:
			case strcasecmp($type, "vengence") != true:
				$game_type = "gpm_hoth";
				break;
			case strcasecmp($type, "assault") != true:
			case strcasecmp($type, "as") != true:
			case strcasecmp($type, "sa") != true:
			case strcasecmp($type, "ass") != true:
			case strcasecmp($type, "assa") != true:
			case strcasecmp($type, "gpm_sa") != true:
				$game_type = "gpm_sa";
				break;
			case strcasecmp($type, "gpm_rush") != true:
			case strcasecmp($type, "rush") != true:
				$game_type = "gpm_rush";
				break;
			case strcasecmp($type, "gpm_tdm") != true:
			case strcasecmp($type, "deathmatch") != true:
			case strcasecmp($type, "teamdeathmatch") != true:
			case strcasecmp($type, "team_deathmatch") != true:
				$game_type = "gpm_tdm";
				break;
			
			default:
				self::$rcc->sendPrivate(self::$msg->index, self::$config['msg.no_such_game_type']);
				$game_type = "gpm_sa";
				break;
		}

		$change_map = null;
		switch (true) {
			case strcasecmp($map, "karkand") != true:
			case strcasecmp($map, "strike_at_karkand") != true:
			case strcasecmp($map, "strike at karkand") != true:
			case strcasecmp($map, "karkand_assault") != true:				
			case strcasecmp($map, "karkand-assault") != true:				
			case strcasecmp($map, "karkand assault") != true:				
				$change_map = "strike_at_karkand";
				$change_map = "kar";
				$change_map = "kark";
				$type = "gm_sa";
				break;
			case strcasecmp($map, "oman") != true:				
			case strcasecmp($map, "gulf_of_oman") != true:				
			case strcasecmp($map, "gulf of oman") != true:				
				$change_map = "gulf_of_oman";
				break;
			case strcasecmp($map, "sharqi") != true:				
				$change_map = "sharqi";
				break;
			case strcasecmp($map, "Dalian_plant") != true:				
			case strcasecmp($map, "Dalian plant") != true:				
			case strcasecmp($map, "dalian") != true:				
			case strcasecmp($map, "delian") != true:				
			case strcasecmp($map, "daliant") != true:				
			case strcasecmp($map, "dal") != true:				
			case strcasecmp($map, "dali") != true:				
				$change_map = "dalian_plant";
				break;
			case strcasecmp($map, "Downtown") != true:				
			case strcasecmp($map, "Basra") != true:				
			case strcasecmp($map, "Basrah") != true:				
			case strcasecmp($map, "Bas") != true:				
			case strcasecmp($map, "Basr") != true:				
				$change_map = "downtown";
				break;
			case strcasecmp($map, "Dragon_Valley") != true:				
			case strcasecmp($map, "Dragon Valley") != true:				
			case strcasecmp($map, "Dragon-Valley") != true:				
			case strcasecmp($map, "Dragon") != true:				
			case strcasecmp($map, "DV") != true:				
			case strcasecmp($map, "Drag") != true:				
			case strcasecmp($map, "Dra") != true:				
				$change_map = "dragon_valley";
				break;
			case strcasecmp($map, "karkand_rush") != true:				
			case strcasecmp($map, "karkand rush") != true:				
			case strcasecmp($map, "karkrush") != true:				
				$change_map = "karkand_rush";
				break;
			case strcasecmp($map, "Mashtuur_City") != true:				
			case strcasecmp($map, "Mashtuur City") != true:				
			case strcasecmp($map, "Mashtuur-City") != true:				
			case strcasecmp($map, "Mashtuur") != true:				
			case strcasecmp($map, "Mashtur") != true:				
			case strcasecmp($map, "Mash") != true:				
			case strcasecmp($map, "Mas") != true:				
				$change_map = "mashtuur_city";
				break;
			case strcasecmp($map, "Trail") != true:				
			case strcasecmp($map, "Myanmar") != true:				
			case strcasecmp($map, "Burma") != true:				
			case strcasecmp($map, "Birma") != true:				
			case strcasecmp($map, "Myan") != true:				
				$change_map = "trail";
				break;
			default:
				self::$rcc->sendPrivate(self::$msg->index, self::$config['msg.no_such_map']);
				return false;
				break;
		}

		if (isset(self::$config['msg.change_map']) && !empty(self::$config['msg.change_map']) && self::$config['msg.change_map'] != false) {
			self::$rcc->send(self::$config['msg.change_map']);
		}
		self::$srv->changeMap($change_map);
		return true;
	}

	public function listAvailableCommands($type = null)
	{
		if (isset($type) && $type == "owner") {
			$results =  DB::getCommandsByLevel(self::$config['db.table_user_commands'], 200);
		} elseif(isset($type) && $type == "admin") {
			$results = DB::getCommandsByLevel(self::$config['db.table_user_commands'], 100);
		} elseif (isset($type) && $type = "mod") {
			$results = DB::getCommandsByLevel(self::$config['db.table_user_commands'], 50);
		} else {
			$results = DB::getCommandsByLevel(self::$config['db.table_user_commands'], 0);
		}

		$commands = implode(", ", $results);
		$output = str_replace("%commands%", $commands, self::$config['msg.commands']);
		self::$rcc->send($output);
		return true;
	}

	public function listAdmins($type = "online")
	{
		if (isset($type) && $type == "list") {
			$results = DB::getAllAdmins();
			if (count($results) > 0) {
				$output = str_replace("%admins%", implode(", ", $results), self::$config['msg.admins.all']);
				self::$rcc->send($output);
				return true;
			} else {
				self::$rcc->send(self::$config['msg.admin_search.no_admins_found']);
				return true;
			}
		} else {
			$profileIds = array();
			$soldierIds = array();
			foreach (self::$players as $iter => $player) {
				$profileIds[] = $player->nucleusId;
				$soldierIds[] = $player->cdKeyHash;
			}
			$results = DB::getOnlineAdmins($profileIds, $soldierIds);
			if (count($results) > 0) {
				$output = str_replace("%admins%", implode(", ", $results), self::$config['msg.admins.online']);
				self::$rcc->send($output);
				return true;
			} else {
				self::$rcc->send(self::$config['msg.admin_online.no_admins_found']);
				return true;
			}
			$online = implode(", ", $admins);
			$adminString = implode(", ", $admins);
			$output = str_replace("%admins%", $adminString, self::$config['msg.admins.online']);
			self::$rcc->send($output);
			return true;
		}
	}

	public function searchAdmin($player)
	{
		$results = DB::getSpecifiedAdmins($player);
		if (is_array($results)) {
			if (count($results) > 0) {
				$output = str_replace("%admins%", implode(", ", $results), self::$config['msg.admin_search.results']);
				self::$rcc->send($output);
				return true;
			} else {
				self::$rcc->send(self::$config['msg.admin_search.no_admins_found']);
				return true;
			}
		} else {
			self::$rcc->send(self::$config['msg.admin_search.problem']);
			return false;
		}
	}

	public function switch($soldier_name)
	{
		$soldier_name = trim($soldier_name);
		if (($player = self::findPlayer($soldier_name))!==false) {
			$output = self::enchanceMessage($config['msg.switch_player'], $player);
			self::$rcc->send($output);
			self::$rcp->switchPlayer($player->index);
			return true;
		} else {
			return false;
		}
	}

}

?>