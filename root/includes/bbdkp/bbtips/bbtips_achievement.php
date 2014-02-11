<?php
/**
* wowhead Achievement parser
*
* @package bbDkp.includes
* @version 1.0.4
* @copyright (c) 2010 bbdkp https://github.com/bbDKP/bbTips
* @author: Adam "craCkpot" Koch (admin@crackpot.us) -- 
* @author: Sajaki (sajaki9@gmail.com)
*
**/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

//require base class
if (!class_exists('bbtips'))
{
    require($phpbb_root_path . 'includes/bbdkp/bbtips/bbtips.' . $phpEx);
}


/**
 * Class bbtips_achievement
 *
 * usage: [achievement]Breaking Out of Tol Barad[/achievement]
 * [achievement]4874[/achievement]
 * [ptrachievement]Explore Hyjal[/ptrachievement]
 * [achievement]Loremaster of Outland[/achievement]
 *
 */
class bbtips_achievement extends bbtips
{
    public function parse($name)
	{
	    global $phpbb_root_path, $phpEx;
	    
		if (trim($name) == '')
		{
			return false;
		}
		
		if (!class_exists('wowhead_cache')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/dbal.' . $phpEx);
        }
		$cache = new wowhead_cache();


		if (!$result = $cache->getObject($name, 'achievement', $this->lang))
		{
			// not in cache
			if (is_numeric($name))
			{
				$result = $this->_getAchievementByID($name);
			}
			else
			{
				$result = $this->_getAchievementByName($name);
			}

			if (!$result)
			{
				// not found
				
				return $this->NotFound('achievement', $name);
			}
			else
			{
				$cache->saveObject($result);
				
				return $this->_generateHTML($result, 'achievement');
			}
		}
		else
		{
			
			return $this->_generateHTML($result, 'achievement');
		}
	}

	/**
	* Queries Wowhead for Achievement info by ID
	* @acess private
	**/
	private function _getAchievementByID($id)
	{
		if (!is_numeric($id))
		{
		    return false;
		}


		$this->make_searchurl($id, 'achievement');
		$data = $this->gethtml($id, 'achievement');

		if ($data == '$WowheadPower.registerAchievement(1337, 25, {});')
		{
			return false;
		}
		else
		{
			if (preg_match('#<b class="q">(.+?)</b>#s', $data, $match))
			{
				return array(
						'name'			=>	stripslashes($match[1]),
						'itemid'		=>	$id,
						'search_name'	=>	$id,
						'type'			=>	'achievement',
						'lang'			=>	$this->lang

				);
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * Wowhead source parser Achievement by Name
	 * 
	 * @param string $name
	 * @return boolean|array NULL
	 */
	private function _getAchievementByName($name)
	{
		global $phpbb_root_path, $phpEx;
		
        if (trim($name) == '')
		{
			return false;
		}

		$this->make_searchurl($name, 'achievement');
		$data = $this->gethtml($name, 'achievement');
		
		if ( !class_exists('simple_html_dom_node')) 
        {
            include ($phpbb_root_path . 'includes/bbdkp/bbtips/simple_html_dom.' . $phpEx); 
        }

		$html = str_get_html ($data, $lowercase = true);
		
		// get name from meta tag
		$element = $html->find('meta[property=og&#x3A;title]'); 
		$achievementname = "";
		foreach($element as $attr)
		{
			$achievementname = (string) $attr->getattribute('content');
			$achievementname = html_entity_decode($achievementname);
		}
		
		// get link from meta tag
		$element = $html->find('link[rel=canonical]'); 
		foreach($element as $attr)
		{
			$achievementlink = (string) $attr->getattribute('href');
			$achievementlink  = html_entity_decode($achievementlink); 
			// target result : content="http://www.wowhead.com/achievement=4874/breaking-out-of-tol-barad"
			$linkarray = explode("/" , $achievementlink, 5);
			$achid = str_replace("achievement=", "", $linkarray[3])  ;
		}
		
		$html->clear(); 
        unset($html);
		
		if($name === $achievementname)
		{
			//success
			return array(
				'name'			=>	$achievementname,
				'search_name'	=>	$achievementname,
				'itemid'		=>	$achid,
				'type'			=>	'achievement',
				'lang'			=>	$this->lang
			);
					
		}
		else 
		{
			// not found
			return false;
		}
		
	}
	
	/**
	* Generates HTML for link
	* @access private
	**/
	private function _generateHTML($info, $type, $size = '', $rank = '', $gems = '')
	{
	    $info['link'] = $this->GenerateLink($info['itemid'], $type);
		return $this->ReplaceWildcards($this->patterns->pattern($type), $info);
	}
	
	
}
?>