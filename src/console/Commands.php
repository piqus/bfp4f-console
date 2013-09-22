<?php
/**
 * Command Console
 *
 * Commands Abstract Class.
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

class Commands 
{
	public static $config;
	public static $rcp;
	public static $srv;
	public static $rcc;
	public static $players;
	public static $cmdlet;
	public static $admlet;
	public static $msg;
	public static $cmd_name;

	public function setConfigs($configs = null)
	{
		self::$config = $configs;
	}

	public function setRcpInst($rcp = null)
	{
		self::$rcp = $rcp;
	}

	public function setServInst($server = null)
	{
		self::$srv = $server;
	}

	public function setChatInst($chat = null)
	{
		self::$rcc = $chat;
	}

	public function setPlayers($players = null)
	{
		self::$players = $players;
	}

	public function setCmdlet($cmd = null)
	{
		self::$cmdlet = $cmd;
	}

	public function setAdmlet($adm = null)
	{
		self::$admlet = $adm;
	}

	public function setMsg($message = null)
	{
		self::$msg = $message;
	}

	public function setCommandName($command_name = null)
	{
		self::$cmd_name = $command_name;
	}

	public static function findPlayer($soldierName, $showErrors = true) 
	{
		$foundplayers = array();
		for ($i = 0; $i < count(self::$players); ++$i)
		{
			if (preg_match('/'.preg_quote($soldierName).'/i', self::$players[$i]->name)) {
				array_push($foundplayers, self::$players[$i]);
			}
		}
		$plc = count($foundplayers);
		if($plc > 1) {
			if ($showErrors==true) {
				$names = array();
				foreach ($foundplayers as $pl) {
					$names[] = $pl->name;
				}
				$output = str_replace("%results%", implode(", ", $names), self::$config['msg.too_many_results']);
				self::$rcc->sendPlayer(self::$msg->origin, $output);
			}
			return false;
		} elseif($plc == 1) {
			return $foundplayers[0];
		} else {
			if ($showErrors==true) {
				self::$rcc->sendPlayer(self::$msg->origin, self::$config['msg.no_results']);
			}
			return false;
		}
	}

	public static function enchanceMessage($message, $player = null)
	{
		if (isset($player) && !empty($player)) {
			$player = self::findPlayer(self::$msg->origin, false);
		}

		$message = str_replace("/%player%/", $player->name, $message);
		$message = str_replace("/%ping%/", $player->ping, $message);
		$message = str_replace("/%profileid%/", $player->nucleusId, $message);
		$message = str_replace("/%soldierid%/", $player->cdKeyHash, $message);

		switch(true)
	    {
	        case strpos($player->kit, 'Medic') !== false:
	            $kit = "medic";
	            break;
	    
	        case strpos($player->kit, 'Assault') !== false:
	            $kit = "assault";
	            break;
	    
	        case strpos($player->kit, 'Recon') !== false:
	            $kit = "recon";
	            break;
	    
	        case strpos($player->kit, 'Engineer') !== false:
	            $kit = "engineer";
	            break;

	        default:
	            //soldier is dead
	            $kit = "dead"; 
	            break;
	    }

		$message = str_replace("/%class%/", $kit, $message);
		
		return $message;
	}

	public static function findPlayerById($playerId) {
		
	}

	public static function convertTime($time)
	{
		switch(true)
	    {
	        case stripos($time, 'y') !== false:
	        	$type = "years";
	        	$duration = substr($time, 0, stripos($time, 'y'));
	            break;	    
	        case strpos($time, 'M') !== false:
	        	$type = "months";
	        	$duration = substr($time, 0, strpos($time, 'M'));
	        	break;
	        case stripos($time, 'mo') !== false:
	        	$type = "months";	        	
	        	$duration = substr($time, 0, stripos($time, 'mo'));
	            break;	    
	        case stripos($time, 'W') !== false:
	        	$type = "weeks";
	        	$duration = substr($time, 0, stripos($time, 'W'));
	            break;	    
	        case stripos($time, 'd') !== false:
	        	$type = "days";
	        	$duration = substr($time, 0, stripos($time, 'd'));
	            break;	    
	        case stripos($time, 'h') !== false:
	        	$type = "hours";	        	
	        	$duration = substr($time, 0, stripos($time, 'h'));
	            break;
	        case strpos($time, 'm') !== false:
	        	$duration = substr($time, 0, stripos($time, 'm'));
	        	$type = "minutes";	    
	        	break;    	
	        case strpos($time, 'min') !== false:
	        	$duration = substr($time, 0, stripos($time, 'min'));
	        	$type = "minutes";
	            break;
	        default:
	        	$duration = "1";
	        	$type = "hours";
	        	break;

	    }
	    return array('type' => $type, 'duration' => (int) $duration);
	}
}

?>