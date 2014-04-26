<?php
/**
* bbdkp-wowhead cache class
*
* @version 1.0.4
* @copyright (c) 2010 bbdkp https://github.com/bbDKP/bbTips
* @author (c) 2009 bbDKP - Sajaki
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*  
**/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
 * wrapper sql interface
 *
 */
class bbtips_cache
{


	/**
	 * Gets Gem from 
	 *
	 * @param unknown_type $itemid
	 * @return unknown
	 */
	public function getGems($itemid)
	{
	    
	    global $db;
		$gems = array();
		$query_text = 'SELECT gemid FROM ' . BBTIPS_GEM_TBL . " WHERE itemid= '" . $itemid . "' ORDER BY slot ASC";
		$result = $db->sql_query($query_text);

		if ( $db->sql_affectedrows() == 0)
		{
			$db->sql_freeresult($result);
			return false;
		}
		else
		{
		    if ($db->sql_affectedrows() > 1  )
		    {
        		while (list($gemid) = $db->sql_fetchrow($result))
                {
                    array_push($gems, $row);
                }
                $db->sql_freeresult($result);
        		return $gems;
		    }
		    else // just 1
		    {
		        list($gemid) = $db->sql_fetchrow($result);
				$db->sql_freeresult($result);
				return $gemid;
		        
		    }
		}
	}
	
	/**
	* Saves Gem to 
	* @access public
	**/
	public function saveGems($gems)
	{
		if (!is_array($gems) || sizeof($gems) == 0)
		{
		    return false;
		}
	}


	
	/**
	* Gets object from cache table
	* @access public
	**/
	public function getObject($name, $type = 'item', $lang = '', $rank = '', $size = '')
	{
	    
	    global $db, $config;

		if (trim($lang) == '')
		{
		    $lang = $config['bbtips_lang'];
		}
		
		$search = $db->sql_like_expression($db->any_char . $db->sql_escape($name) . $db->any_char) ; 
		
		$query_text = 'SELECT itemid, name, search_name, quality, rank, type, lang, icon, icon_size
							 FROM ' . BBTIPS_CACHE_TBL . ' WHERE 
					 (search_name ' . $search . '
						  OR name '. $search;
		$query_text .= ")  AND lang='"  . $lang . "' AND type='"  . $type . "'";
		
		
		if (trim($rank) != '') 
		{ 
		    $query_text .= " AND rank='" . $rank . "'"; 
		}
		
		if (trim($size) != '') 
		{ 
		    $query_text .= " AND icon_size='" . $size . "'";  
		}
		
	    $result = $db->sql_query($query_text);
							
	    if ( $db->sql_affectedrows() == 0)
		{
			// not found in cache, return false
		    $db->sql_freeresult($result);
			return false;
		}
		else
		{
		    $row =  $db->sql_fetchrow($result);
			$db->sql_freeresult($result);		    
			return $row; 
		}
		
	}

	/**
	* Saves an object to 
	* @access public
	**/
	public function saveObject($info)
	{
	    global $db;
	      
		if (!is_array($info) || sizeof($info) == 0 || !isset($info['name']) || !isset($info['itemid']))
		{
			return false;    
		}

		$quality = (array_key_exists('quality', $info)) ? $info['quality'] : 0;
		$rank = (array_key_exists('rank', $info) && $info['rank'] != '') ? $info['rank'] :0 ;
		$icon = (array_key_exists('icon', $info)) ? $info['icon'] : 'NULL';
		$icon_size = (array_key_exists('icon_size', $info)) ? $info['icon_size'] : 'medium';
		
		$sql_ary = array(
    		'itemid'        => $info['itemid'],
    		'name'	        => $info['name'], 
    		'search_name'   => $info['search_name'], 
    		'quality'       => $quality,
    		'rank'          => $rank,
    		'type'          => (count($info['type'])> 0) ? $info['type'] : '',
    		'lang'          => $info['lang'], 
    		'icon'          => $icon, 
    		'icon_size'     => $icon_size, 
		);

        $sql = 'INSERT INTO ' . BBTIPS_CACHE_TBL . ' ' . $db->sql_build_array('INSERT', $sql_ary);
		
		$result = $db->sql_query($sql);
    	if (!$result)
		{
			global $user;
			$user->add_lang(array('mods/dkp_tooltips'));
			trigger_error(  sprintf($user->lang['BBTOOLTIPS_ERRORCACHING'], $info['name'] , BBTIPS_CACHE_TBL), E_USER_WARNING ) ;
			return false;
		}
		
	}
}
?>