<?php
/**
 * Command Console
 *
 * Script - Worker.
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

/* Instantiate rcon 
 ********************/
use T4G\BFP4F\Rcon as rcon;

require_once __DIR__.'/test.php';

$rc = new rcon\Base();

/* Connect to server 
 ********************/
$rc->ip = $config['server.ip'];
$rc->port = (int) $config['server.port'];
$rc->pwd = $config['server.password'];

$rc->connect($cn, $cs);

// if ($cn !== 0) {
//     $err = "E: Game server is not responding;".PHP_EOL.
//            "E: Invalid credentials or server is down;".PHP_EOL.
//            "E: $cs ($cn)" . PHP_EOL;
//     error_log($err);
//     echo($err);
//     exit(1);
// }

$rc->init();


/* Retrieve Chat Data
 ********************/

// Instantiate Rcon Objects
$chat = new rcon\Chat();
$rcp = new rcon\Players();
$srv = new rcon\Server();

$players = $rcp->fetch();

/* Check TimeKickBans
 ********************/
foreach ($players as $player) {
    if ($player->connected === "1") {
        $kick = DB::getKicksByPlayer($player->nucleusId, $player->cdKeyHash);
        if (isset($canIKickIt) && $canIKickIt!==false) {
            if ($config['ak.enchanted_msg_enabled'] && !empty($config['ak.enchanted_msg'])) {
                $output = $config['ak.enchanted_msg'];
                $output = str_replace("%player%", $player->name, $output);
                $output = str_replace("%left_time%", $canIKickIt->remaining, $output);
                $output = str_replace("%end_time%", $canIKickIt->expiration_date, $output);
                $chat->send($output);
            }
            usleep(100);
            $rcp->kick($player->index, $config['ak.msg']);
        }
    }
}

// Get latest 10 chat messages
$quantity = $config['chat.quantity'];
// $messages = $chat->fetch($quantity);
$rcl = DB::getLatestMessages($config['db.table_chat_log'], $quantity);

// Clean up ChatLog Table
// DB::purgeChatLogTable($config['db.table_chat_log'], $config['db.truncate_chat_log']);

/* Off we go!
 ************/
foreach ($messages as $msg_index => $msg) {

    $skip = false;
    $msg_date = new DateTime($msg->time);    

    /* Check if command was executed (checked) before
     ************************************************/
    if (isset($rcl) && !empty($rcl)) {
        foreach ($rcl as $rcl_index => $rcl_msg) {
            $rcl_msg_date = new DateTime($rcl_msg->datetime);
            if (($rcl_msg->origin == $msg->origin && $rcl_msg->message == $msg->message && $rcl_msg_date == $msg_date)) {
                $skip = true;
            }
        }
    }

    if ($skip===false) {
        
        /* Chat message is a command.
         ****************************/
        if (isset($msg->message[0]) && ($msg->message[0] == "|" || $msg->message[0] == "!" || $msg->message[0] == "/")) {
            
            // Divide and conquer?
            $cmd = substr($msg->message, 1);
            $cmd = explode(" ", $cmd);

            // Lookup command in database
            $cmdPrepare = DB::lookupCommand(
                $config['db.table_user_commands'], 
                $config['db.table_commands'], 
                $config['db.table_admins'], 
                $cmd[0], 
                $msg->origin
            );
            // var_dump($cmdPrepare);
            // Uh Oh.. Lemme check you first ;-)
            if ($cmdPrepare->status === false && $cmdPrepare->deniedType == 1) {
                // Command not exist
                $output = $config['command.not_exist_message'];
                $output = str_replace('%player%', $msg->origin, $output);
                $output = str_replace('%cmd%', "!".$cmd[0], $output);
                if (isset($output) && !empty($output) && $output != false) {
                    $chat->send($output);
                }
            } elseif ($cmdPrepare->status === false && $cmdPrepare->deniedType == 2) {
                // Command is dissallowed for origin
                $output = $config['command.no_permission_message'];
                $output = str_replace('%player%', $msg->origin, $output);
                $output = str_replace('%cmd%', "!".$cmd[0], $output);
                if (isset($output) && !empty($output) && $output != false) {
                    $chat->send($output);
                }
            } elseif ($cmdPrepare->status === true) {
                
                // Woah! You know magic!
                try {
                    $class = new $cmdPrepare->cmd->class();

                    // Export variables to Command abstract class 
                    $class->setConfigs($config);
                    $class->setRcpInst($rcp);
                    $class->setServInst($srv);
                    $class->setChatInst($chat);
                    $class->setPlayers($players);
                    $class->setCmdlet($cmdPrepare->cmd);
                    $class->setAdmlet($cmdPrepare->adm);
                    $class->setMsg($msg);
                    $class->setCommandName(array_shift($cmd));
                    

                    $params = array();

                    // Pass non-string-with-spaces variables
                    for ($c=0; $c < (int) $cmdPrepare->cmd->count_params; $c++) { 
                        if (isset($cmd) && !empty($cmd)) {
                            $params[] = array_shift($cmd);
                        }
                    }

                    // Pass variables string with many spaces.
                    if (isset($cmd) && !empty($cmd)) {
                        $params[] = implode(" ", $cmd);
                    }

                    // Predefined arguments
                    if (isset($cmdPrepare->args) && !empty($cmdPrepare->args)) {
                        $json = @json_decode($cmdPrepare->args);
                        if (is_array($json) && !empty($json)) {
                            foreach ($json as $index => $item) {
                                if (!is_null($item)) {
                                    $params[$index] = $item;
                                }
                            }
                        }
                    }

                    // Execute command
                    call_user_func_array(array($class, $cmdPrepare->cmd->method), $params);
                } catch (Exception $e) {
                    DB::addErrorLog($config['db.table_errors'], json_encode($e->getMessage));
                }
            }

        }

        /* Add to chatlog.
         *****************/
        DB::addChatLog($config['db.table_chat_log'], $msg->message, $msg->origin, $msg_date->format("Y-m-d H:i:s"), $msg->type);
    }
}
// Notice to stdout
echo "Completed. " . date("Y-m-d H:i:s") . PHP_EOL;