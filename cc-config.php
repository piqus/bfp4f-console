<?php
/**
 * Command Console
 *
 * Configuration file.
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

/* Environment Configuration
 ***************************/

// set_time_limit(120);
date_default_timezone_set('UTC');

// Load composer vendors 
define('VENDOR_DIR', __DIR__ . '/test-vendor');

/* Load Classes for COMPOSER
 ***************************/
require_once VENDOR_DIR.'/autoload.php';

//# or if you don't have composer #//
// foreach (glob("src/T4G/BFP4F/Rcon/*.php") as $class) {
//     require_once $class;
// }

/* Connect to DB 
 ********************/
define('DB_TYPE', 'mysql');

define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', 'test');
define('DB_NAME', 'cmdcon');

// if (DB_TYPE=="mongodb") {
//     require_once __DIR__ . '/DB-mongo.php';
// } else {
    require_once __DIR__ . '/DB-pdosql.php';
// }

DB::init();


/* Limiter Configuration
 ***********************/

$config = array(

    /* ------------------------------------
     * SERVER
     * ------------------------------------
     */
    // Server RCON IP
    'server.ip' => "127.0.0.1",

    //Server RCON PORT
    'server.port' => (int) 1337,

    // Server RCON PASSWORD
    'server.password' => "rcon_password",
    
    /* ------------------------------------
     * GENERAL
     * ------------------------------------
     */

    // Is a whole script enabled (force disable script)? 
    'general.script_enabled' => true,

    /* ------------------------------------
     * DATABASE
     * ------------------------------------
     */

    // Collection/Table of Chat Logs
    'db.table_chat_log' => "cc_chat_log",

    // How many days chat log should be stored
    // # Default = 10 days
    'db.truncate_chat_log' => 10,

    // Collection/Table of Commands
    'db.table_commands' => 'cc_commands',
    'db.table_user_commands' => "cc_user_commands",
    // Collection/Table of Admins
    'db.table_admins' => 'cc_admins',

    // Collection/Table of Voting system
    'db.table_vote' => 'cc_vote',

    // Collection/Table of Error reports
    'db.table_errors' => 'cc_errors',
    'db.table_reports' => 'cc_reports',
    'db.table_kicklog' => "cc_kicked_players",

    /* ------------------------------------
     * SCRIPT
     * ------------------------------------
     */

    // How many messages script should fetch then look for command
    // # Default = 8 messages
    'chat.quantity' => 8,

    // Global message if command was not found
    // # %player% - origin of message
    // # %cmd% - typed command (first char: ! [exclamation mark] or | [pipe])
    'command.not_exist_message' => "%player% - command %cmd% not found",

    // Global message if player cannot use selected command (not adequate permission-level)
    // # %player% - origin of message
    // # %cmd% - typed command (first char: ! [exclamation mark] or | [pipe])
    'command.no_permission_message' => "%player% - command is %cmd% not available for you",
    'msg.ping.message' => "%player% your ping = %ping%",
    'msg.report.not_sent' => "raport_not_sent",
    'msg.player_report.not_sent' => "raport_not_sent",
    'msg.issue_report.not_sent' => "raport_not_sent",
    'msg.player_report.sent' => "raport_sent",
    'msg.too_many_results' => "too many results: %results%",
    'msg.no_results' => "no results",
    'msg.ab.status_on' => "autobalance on",
    'msg.ab.status_off' => "autobalance off",
    'msg.ab.wrong_param' => "autobalance wrong param",
    'msg.admin_search.no_admins_found' => "no admins found",
    'msg.admin_search.problem' => "admin search problem",
    'msg.admin_search.results' => "admin results: %results%",
    'msg.restart' => "Restart",
    'msg.no_such_game_type' => "no such a game type",
    'msg.no_such_map' => "no_such_map",
    'msg.change_map' => "change map",
    'msg.admins.online' => "%admins% >>>",
    'msg.admins.all' => "%admins% >>>",
    'msg.admin_search.no_admins_found' => "msg.admin_search.no_admins_found",
    'msg.admin_online.no_admins_found' => "msg.admin_online.no_admins_found",
    'msg.commands' => "%commands%",
    'msg.switch_player' => "%player% you are being switched between teams",

    'ak.enchanted_msg_enabled' => true,
    'ak.enchanted_msg' => "%player% : remaining time %left_time% (unbanned on %end_time%)",
    'ak.msg' => "exist on TimedKickList",
);

?>