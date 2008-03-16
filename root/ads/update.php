<?php
/**
*
* @package phpBB3 Advertisement Management
* @version $Id$
* @copyright (c) 2008 EXreaction, Lithium Studios
* @license http://opensource.org/licenses/gpl-license.php GNU Public License 
*
*/

if (!defined('IN_PHPBB') || !isset($ads_version))
{
	exit;
}

// Setup some stuff we will need.
include($phpbb_root_path . 'includes/functions_admin.' . $phpEx); // Needed for remove_comments function for some DB types
include($phpbb_root_path . 'includes/functions_install.' . $phpEx);
include($phpbb_root_path . 'includes/db/db_tools.' . $phpEx);
include($phpbb_root_path . 'includes/acp/auth.' . $phpEx);
include($phpbb_root_path . 'ads/eami.' . $phpEx);
$auth_admin = new auth_admin();
$db_tool = new phpbb_db_tools($db);
$dbmd = get_available_dbms($dbms);
$eami = new eami();

// Install if required.  If there are updates/additions in the future, DO NOT add the additions to this part.
if (!isset($config['ads_version']))
{
	// Add the tables
	run_file_queries($phpbb_root_path . 'ads/schemas/');

	// Add the permissions
	$auth_admin->acl_add_option(array(
		'local'		=> array(),
		'global'	=> array('a_ads'),
	));

	// Add the modules
	$sql_ary = array(
		'module_basename'	=> 'ads',
		'module_langname'	=> 'ACP_ADVERTISEMENT_MANAGEMENT',
		'module_mode'		=> 'default',
		'module_auth'		=> 'acl_a_ads',
	);
	$eami->add_module('acp', 'ACP_BOARD_CONFIGURATION', $sql_ary);

	// Add the config settings
	set_config('ads_enable', 1);
	set_config('ads_version', '0.3.0'); // Do not change this!
}

// No breaks!
switch ($config['ads_version'])
{
	case '0.3.0' :	
}

set_config('ads_version', $ads_version);

$cache->purge();

/**
* Run Queries from file
*/
function run_file_queries($dir)
{
	global $db, $dbmd, $dbms, $table_prefix;

	if ($dbms == 'mysql' || $dbms == 'mysqli')
	{
		if ($dbms == 'mysqli' || version_compare($db->mysql_version, '4.1.3', '>='))
		{
			$dbms_schema = 'mysql_41_schema.sql';
		}
		else
		{
			$dbms_schema = 'mysql_40_schema.sql';
		}
	}
	else
	{
		$dbms_schema = $dbms . '_schema.sql';
	}

	if (!file_exists($dir . $dbms_schema))
	{
		trigger_error('SCHEMA_NOT_EXIST');
	}

	$remove_remarks = $dbmd[$dbms]['COMMENTS'];
	$delimiter = $dbmd[$dbms]['DELIM'];

	$sql_query = @file_get_contents($dir . $dbms_schema);

	$sql_query = preg_replace('#phpbb_#i', $table_prefix, $sql_query);

	$remove_remarks($sql_query);

	$sql_query = split_sql_file($sql_query, $delimiter);

	foreach ($sql_query as $sql)
	{
		$db->sql_query($sql);
	}
}
?>