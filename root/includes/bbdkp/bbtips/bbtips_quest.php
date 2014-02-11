<?php
/**
 * Wowhead quest  parser
 *
 * @version 1.0.4
 * @copyright (c) 2010 bbdkp https://github.com/bbDKP/bbTips
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Sajaki (sajaki9@gmail.com)
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
 * Class bbtips_quest
 *
 *
 * usage : [quest]A Dire Situation[/quest]
 *
 *
 */
class bbtips_quest extends bbtips
{
	public $lang;
	public $patterns;
	private $args = array();


	public function parse($name)
	{
		if (trim($name) == '')
		{
		    return false;
		}

		global $config, $phpEx, $phpbb_root_path; 
		
		if (!class_exists('wowhead_cache')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/dbal.' . $phpEx);
        }
		$cache = new wowhead_cache();

		if (!$result = $cache->getObject($name, 'quest', $this->lang))
		{
				// not in cache
			if (is_numeric($name))
			{
				// by id
				$result = $this->_getQuestByID($name);
			}
			else
			{
				// by name
				$result = $this->_getQuestByName($name);
			}

			if (!$result)
			{
				// not found
				
				return $this->NotFound('quest', $name);
			}
			else
			{
				$cache->saveObject($result);
				
				return $this->_generateHTML($result, 'quest');
			}
		}
		else
		{
			// found in cache
			
			return $this->_generateHTML($result, 'quest');
		}
	}

	/**
	* Generates HTML for link
	* @access private
	**/
	private function _generateHTML($info, $type, $size = '', $rank = '', $gems = '')
	{
	    $info['link'] = $this->GenerateLink($info['itemid'], $type);
	    $html = $this->ReplaceWildcards($this->patterns->pattern($type), $info);
	    return $html; 
	}
	
	
	
	/**
	* Queries Wowhead for Quest info by ID
	* @access private
	**/
	private function _getQuestByID($id)
	{
		if (!is_numeric($id))
		{
				return false;
		}

		
		$this->make_searchurl($id, 'quest');
		$data = $this->gethtml($id, 'quest');
			
		// wowhead doesn't have the info
		if ($data == '$WowheadPower.registerQuest(' . $id . ', {});')
		{
			return false;
		}
		else
		{
			// gets the quest's name
			if (preg_match('#<b class="q">(.+?)</b>#s', $data, $match))
			{
				return array(
					'name'			=>	stripslashes($match[1]),
					'itemid'		=>	$id,
					'search_name'	=>	$id,
					'type'			=>	'quest',
					'lang'			=> $this->lang
				);
			}
			else
			{
				return false;
			}
		}
	}

	
	
	/**
	* Queries Wowhead for Quest by Name
	* @access private
	**/
	private function _getQuestByName($name)
	{
		if (trim($name) == '')
		{
		    return false;
		}
		
		$this->make_searchurl($name, 'quest');
		$html = $this->gethtml($name, 'quest');
		
		if (!$html)
		{
			return false;
		}
		
		// make sure it didn't redirect
		if (preg_match('#Location: \/quest=([0-9]{1,10})#s', $html, $match))
		{
			$quest =  array(
				'name'			=>	ucwords(strtolower($name)),
				'search_name'	=>	$name,
				'type'			=>	'quest',
				'itemid'		=>	$match[1],
				'lang'			=>	$this->lang
			);	
			
			return $quest; 
		}
		
		// get the JSON line from the data
		$line = $this->_questLine($html);
		if (!$line)
		{
			return false;
		}
		else
		{
			/* cleanup json
			 * see http://www.bbdkp.com/tracker.php?p=5&t=209
			* and http://www.wowhead.com/forums&topic=205251&p=3247970
			*/
			$line = str_replace("frombeta:'1'", '"frombeta":1' , $line);
			
			// decode the json
			if (!$json = json_decode($line, true))
			{
				return false;
			}
			
			foreach ($json as $quests)
			{
				if (stripslashes(strtolower($quests['name'])) == stripslashes(strtolower($name)))
				{
					$quest = array(
						'name'			=>	$quests['name'],
						'search_name'	=>	$name,
						'type'			=>	'quest',
						'itemid'		=>	$quests['id'],
						'lang'			=>	$this->lang
					);
					return $quest; 
				}
			}
			
			return false;
		}
			
	}
	
	/*
	 * loop lines and extract from json 
	 */
	private function _questLine($data)
	{
		$parts = explode(chr(10), $data);
		foreach ($parts as $line)
		{
			if (strpos($line, "new Listview({template: 'quest', id: 'quests',") !== false)
			{
				$line = substr($line, strpos($line, 'data: [{') + 6);
				$line = str_replace('});', '', $line);
				return $line;
				break;
			}
		}
		
		return false;
	}
	
	
	
	
}
?>