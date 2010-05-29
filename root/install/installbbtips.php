<?php
/**
 * bbdkp WOW edition
 * @package bbDkp-installer
 * @author sajaki9@gmail.com
 * @copyright (c) 2009 bbDkp <http://code.google.com/p/bbdkp/>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * 
 */

define('UMIL_AUTO', true);
define('IN_PHPBB', true);
define('ADMIN_START', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

// We only allow a founder install this MOD
if ($user->data['user_type'] != USER_FOUNDER)
{
    if ($user->data['user_id'] == ANONYMOUS)
    {
        login_box('', 'LOGIN');
    }

    trigger_error('NOT_AUTHORISED', E_USER_WARNING);
}

if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
    trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

if (!file_exists($phpbb_root_path . 'install/installbbtips.' . $phpEx))
{
    trigger_error('Warning! Install directory has wrong name. it must be \'install\'. Please rename it and launch again.', E_USER_WARNING);
}

// The name of the mod to be displayed during installation.
$mod_name = 'bbtips';

/*
* The name of the config variable which will hold the currently installed version
* You do not need to set this yourself, UMIL will handle setting and updating the version itself.
*/
$version_config_name = 'bbdkp_plugin_bbtips_version';

/*
* The language file which will be included when installing
*/
$language_file = 'mods/dkp_tooltips';

/*
* Optionally we may specify our own logo image to show in the upper corner instead of the default logo.
* $phpbb_root_path will get prepended to the path specified
* Image height should be 50px to prevent cut-off or stretching.
*/
//$logo_img = 'images/bbdkp.png';

/*
* Run Options 
*/
$options = array(
/*	'guildtag'	=> array('lang' => 'UMIL_GUILD', 'type' => 'text:40:255', 'explain' => false, 'select_user' => false),*/
/*'realm'	    => array('lang' => 'realm_name', 'type' => 'text:40:255', 'explain' => false, 'select_user' => false, 'default' => 'Lightbringer'),*/
'region'   => array('lang' => 'region', 'type' => 'select', 'function' => 'regionoptions', 'explain' => true),
'bbtips_lang'   => array('lang' => 'lang', 'type' => 'select', 'function' => 'langoptions', 'explain' => true),

'item'   => array('lang' => 'ITEM', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),
'itemico'   => array('lang' => 'ITEMICO', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),
'itemdkp'   => array('lang' => 'ITEMDKP', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),
'craft'   => array('lang' => 'CRAFT', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),
'quest'   => array('lang' => 'QUEST', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),
'spell'   => array('lang' => 'SPELL', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),
'achievement'   => array('lang' => 'ACHIEVEMENT', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),

/*'armory'   => array('lang' => 'ARMORY', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),
'profile'   => array('lang' => 'PROFILE', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),
'guild'   => array('lang' => 'GUILD', 'validate' => 'bool', 'type' => 'radio:yes_no', 'default' => true),
*/
);


/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/
$bbdkp_table_prefix = "bbeqdkp_";

/***************************************************************
 * 
 * Welcome to the bbtips installer
 * 
****************************************************************/


$versions = array(
    
    '0.3'    => array(

     // Lets add global config settings
	'config_add' => array(

		// source site
		array('bbtips_site', 'wowhead', true),
		array('bbtips_maxparse', 20, true),		
		
		// script source
		array('bbtips_localjs', 1, true),
		// automatic search
		array('bbtips_autsrch', 1, true),

		//		
		array('bbtips_realm', 'Lightbringer', true),
		array('bbtips_region', request_var('region', ''), true),
		
		// language choice
		array('bbtips_lang', 'en', true),

		// custom tooltip settings
		array('bbtips_ttshow', 1, true),
		array('bbtips_type', 'ttbbdkp', true),
		array('bbtips_label', 'Wowhead', true),
		

	),
            
     'table_add' => array ( 
        
        // adding new tables for wowhead-addin to replace itemstats                 
              array($bbdkp_table_prefix . 'wowhead_cache', array(
                    'COLUMNS'		=> array(
                       'id'			=> array('INT:8', NULL, 'auto_increment' ),
                       'itemid'		=> array('INT:8', 0 ),
		  			   'name'  		=> array('VCHAR_UNI:255', ''),
		  			   'search_name' => array('VCHAR_UNI:255', ''),
                       'quality'  	=> array('USINT', 0),
					   'rank' 	    => array('USINT', 0),
					   'type'  		=> array('VCHAR:255', ''),
					   'lang'  		=> array('VCHAR:255', ''),               	  
					   'icon'		=> array('VCHAR:255', ''),               	  
					   'icon_size'  => array('VCHAR:255', ''),
                    ),
                    'PRIMARY_KEY'	=> array('id'),
              ),
            ),
            
            array($bbdkp_table_prefix . 'wowhead_craftable', array(
                    'COLUMNS'        => array(
                       'itemid'	  => array('INT:10', 0),
                       'name'	      => array('VCHAR_UNI:255', ''),
		  				'search_name' => array('VCHAR_UNI:255', ''),
                       'quality'  	  => array('USINT', 0),
						'lang'  	  => array('VCHAR:255', ''),               	  
						'icon'  	  => array('VCHAR:255', ''),               	  
                    ),
              ),
            ),
            
            array($bbdkp_table_prefix . 'wowhead_craftable_reagent', array(
                    'COLUMNS'      => array(
                       'itemid'	=> array('INT:8', 0,), 
                       'reagentof'	=> array('INT:11', 0),        	
		  				'name'      => array('VCHAR_UNI:255', ''),
                       'quantity'  => array('USINT', 0),
						'quality'  	=> array('USINT', 0),        	  
						'icon'  	=> array('VCHAR:255', ''),               	  
                    ),
              ),
            ),
            
            array($bbdkp_table_prefix . 'wowhead_craftable_spell', array(
                    'COLUMNS'      => array(
                       'reagentof'	=> array('UINT', 0),        	
                       'spellid'  => array('UINT', 0),
						'name'  	=> array('VCHAR_UNI:255', ''),               	  
                    ),
              ),
            ),
            
            array($bbdkp_table_prefix . 'wowhead_itemset', array(
                    'COLUMNS'          => array(
                       'setid'	        => array('INT:8', 0),        	
                       'name'  	    => array('VCHAR_UNI:255', ''),  
            			'search_name'  	=> array('VCHAR_UNI:255', ''),  
                       'lang'          => array('VCHAR:2', ''),
						             	  
                    ),
              ),
            ),            

            array($bbdkp_table_prefix . 'wowhead_itemset_reagent', array(
                    'COLUMNS'      => array(
                       'setid'	    => array('INT:8', 0), 
                       'itemid'	=> array('UINT', 0), 
                       'name'  	=> array('VCHAR_UNI:255', ''),  
            			'quality'  	=> array('USINT', 0),
                       'icon'      => array('VCHAR:255', ''),
                    ),
              ),
            ),            
            
            array($bbdkp_table_prefix . 'wowhead_npc', array(
                    'COLUMNS'         => array(
                       'npcid'	       => array('INT:8', 0),  
                       'name'  	   => array('VCHAR_UNI:255', ''), 
                       'search_name'  => array('VCHAR_UNI:255', ''), 
            			'lang'          => array('VCHAR:2', ''),
                    ),
              ),
            ),          

         ),

        		
        // add the bbdkp modules to ACP using the info files, (old RC1 modules are already removed)
		'module_add' => array(
            
            array('acp', 'ACP_DKP_ITEM', array(
           		 'module_basename' => 'dkp_bbtooltips',
            	 'modes'           => array('bbtooltips'),
        		)),
        ),
         
         'custom' => array( 
            'bbdkp_caches',  'insert_bbcodes_wrapper' 
         ) 

    ),
    
      '0.3.1'    => array( 
     
     // no db change
     ), 
     
    
      '0.3.2'    => array( 
     
     // no db change
     ),      

      '0.3.3'    => array( 
     
     // no db change
     ),      
     
     '0.3.4'    => array( 
     
     // this is a bugfix because wowhead changed alot of backend urls
		'config_update'  => array(
		
			array('bbtips_maxparse', 200), 
			)   
     ), 
                

);

// Include the UMIF Auto file and everything else will be handled automatically.
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);
/**************************************
 *  
 * function for rendering region list
 * 
 */
function regionoptions($selected_value, $key)
{
	global $user;

    $regions = array(
    	'EU'     			=> "WoW European region", 
    	'US'     			=> "WoW US region",     	 
    );
    
    $default = 'US'; 
	$pass_char_options = '';
	foreach ($regions as $key => $region)
	{
		$selected = ($selected_value == $default) ? ' selected="selected"' : '';
		$pass_char_options .= '<option value="' . $key . '"' . $selected . '>' . $region . '</option>';
	}

	return $pass_char_options;
}


/**************************************
 *  
 * function for rendering region list
 * 
 */
function langoptions($selected_value, $key)
{
	global $user;

    $languages = array(
    	'en'     			=> "English", 
    	'de'     			=> "German",     	 
    	'fr'     			=> "French",     	 
    	'es'     			=> "Spanish",     	 
    	'ru'     			=> "Russian",     	 
    );
    
    $default = 'en'; 
	$pass_lang_options = '';
	foreach ($languages as $key => $lang)
	{
		$selected = ($selected_value == $default) ? ' selected="selected"' : '';
		$pass_lang_options .= '<option value="' . $key . '"' . $selected . '>' . $lang . '</option>';
	}

	return $pass_lang_options;
}


/**************************************
 *  
 * global function for clearing cache
 * 
 */
function bbdkp_caches($action, $version)
{
    global $db, $table_prefix, $umil, $bbdkp_table_prefix;
    
    $umil->cache_purge();
    $umil->cache_purge('imageset');
    $umil->cache_purge('template');
    $umil->cache_purge('theme');
    $umil->cache_purge('auth');
    
    return 'UMIL_CACHECLEARED';
}

/**
 * inserts bbcodes into database
 *
 * @param unknown_type $action
 * @param unknown_type $version
 */
function insert_bbcodes_wrapper($action, $version)
{
    global $db, $umil; 
   	switch ($action)
	{
		case 'install' :
		case 'update' :
			if(request_var('item', 0) == 1)
			{
				 insert_bbcodes($action, $version, 'item');
			}
		
			if(request_var('itemico', 0) == 1)
			{
				 insert_bbcodes($action, $version, 'itemico'); 			
			}
		
			if(request_var('itemdkp', 0) == 1)
			{
				 insert_bbcodes($action, $version, 'itemdkp'); 			
			}
		
			if(request_var('craft', 0) == 1)
			{
				 insert_bbcodes($action, $version, 'craft'); 			
			}
			
			if(request_var('quest', 0) == 1)
			{
				 insert_bbcodes($action, $version, 'quest'); 			
			}
			
			if(request_var('spell', 0) == 1)
			{
				 insert_bbcodes($action, $version, 'spell'); 			
			}
			
			if(request_var('achievement', 0) == 1)
			{
				 insert_bbcodes($action, $version, 'achievement'); 			
			}
			
			if(request_var('armory', 0) == 1)
			{
				 insert_bbcodes($action, $version, 'armory'); 			
			}
			
			if(request_var('profile', 0) == 1)
			{
				 insert_bbcodes($action, $version, 'profile'); 			
			}
			
			if(request_var('guild', 0) == 1)
			{
				 insert_bbcodes($action, $version, 'guild'); 			
			}
			 return array('command' => 'UMIL_BBCODE_ITEM_ADDED', 'result' => 'SUCCESS'); 
				
	      break;
		case 'uninstall' :

			delete_bbcodes($action, $version, 'item'); 
			delete_bbcodes($action, $version, 'itemico');
			delete_bbcodes($action, $version, 'itemdkp');
			delete_bbcodes($action, $version, 'craft');
			delete_bbcodes($action, $version, 'quest');
			delete_bbcodes($action, $version, 'spell');
			delete_bbcodes($action, $version, 'achievement');																		
			return array('command' => 'UMIL_BBCODE_ITEM_REMOVED', 'result' => 'SUCCESS'); 												
		    
		  break; 
        
	}
	

}

/**
 * inserts bbcodes into database
 *
 * @param string $action
 * @param string $version
 */
function insert_bbcodes($action, $version, $tag)
{	
	global $db, $user, $auth, $template, $cache;
	global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		// Set up mode-specific vars
		// build each field for the sql query
		$bbcode_match 	=	"[$tag]{SIMPLETEXT}[/$tag]";
		$bbcode_tpl 	=	$bbcode_match;// same as match
		$helpline = 'Wowhead ' . $tag;
			
		$sql = 'SELECT count(*) as checkcount FROM ' . BBCODES_TABLE . 
			" WHERE LOWER(bbcode_tag) = '" . $db->sql_escape(strtolower($tag)) . "'";
		$result = $db->sql_query($sql);
	    $checkcount = (int) $db->sql_fetchfield('checkcount');
	    
	    if ($checkcount >= 1)
	    {
	    	return; 
	    }
	    
		$db->sql_freeresult($result);
		
		// Include the bbcode class
		if (!class_exists('acp_bbcodes'))
		{
			require("{$phpbb_root_path}includes/acp/acp_bbcodes.$phpEx");
		}
		$acp_bbcodes = new acp_bbcodes;
		
		$data = $acp_bbcodes->build_regexp($bbcode_match, $bbcode_tpl);	
		
		// assign the other variables
		$sql_ary = array(
			'bbcode_tag'				=> $tag,
			'bbcode_match'				=> $bbcode_match,
			'bbcode_tpl'				=> $bbcode_tpl,
			'display_on_posting'		=> 1,
			'bbcode_helpline'			=> $helpline,
			'first_pass_match'			=> $data['first_pass_match'],
			'first_pass_replace'		=> $data['first_pass_replace'],
			'second_pass_match'			=> $data['second_pass_match'],
			'second_pass_replace'		=> $data['second_pass_replace']
		);
		
		// get max bbcodeid
		$sql = 'SELECT MAX(bbcode_id) as max_bbcode_id
			FROM ' . BBCODES_TABLE;
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		$db->sql_freeresult($result);
	
		if ($row)
		{
			$bbcode_id = $row['max_bbcode_id'] + 1;
	
			// Make sure it is greater than the core bbcode ids...
			if ($bbcode_id <= NUM_CORE_BBCODES)
			{
				$bbcode_id = NUM_CORE_BBCODES + 1;
			}
		}
		else
		{
			$bbcode_id = NUM_CORE_BBCODES + 1;
		}
	
		if ($bbcode_id > 1511)
		{
			trigger_error($user->lang['TOO_MANY_BBCODES'] . adm_back_link($this->u_action), E_USER_WARNING);
		}
		
		$sql_ary['bbcode_id'] = (int) $bbcode_id;
	
		$db->sql_query('INSERT INTO ' . BBCODES_TABLE . $db->sql_build_array('INSERT', $sql_ary));
		$cache->destroy('sql', BBCODES_TABLE);
		
		$lang = 'BBCODE_ADDED';
		$log_action = 'LOG_BBCODE_ADD';
		
		add_log('admin', $log_action, $data['bbcode_tag']);
					
}

/**
 * deletes bbcodes from database
 *
 * @param string $action
 * @param string $version
 */
function delete_bbcodes($action, $version, $tag)
{	
	global $db, $user, $auth, $template, $cache;
	global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;
	
	switch ($action)
	{
		case 'uninstall' :
			$sql = 'SELECT bbcode_id FROM ' . BBCODES_TABLE . " WHERE lower(bbcode_tag) = '" . $db->sql_escape(strtolower($tag)) . "'"; 
			$result = $db->sql_query($sql);
			$row = $db->sql_fetchrow($result);
			$db->sql_freeresult($result);
			if ($row)
			{
				$db->sql_query('DELETE FROM ' . BBCODES_TABLE . " WHERE bbcode_id = ".  $row['bbcode_id']);
				$cache->destroy('sql', BBCODES_TABLE);
				add_log('admin', 'LOG_BBCODE_DELETE', $tag);
			}
			break; 
	}


}


?>