<?php
/**
 * Wowhead spell parser
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

/**
 * Class bbtips_spell
 *
 * As of Wow 4.0, the spell ranks were removed. Existing spell tags with spell rank will ignore the rank argument.
 * [spell]Power Word: Shield[/spell]`
 * [spell]Master of Beasts[/spell]`
 * in bbTips 1.0.4, recipes, guild perks and Glyph spells can be used.
 * [spell]Weak Troll's Blood Elixir[/spell]`
 * [spell]Mr. Popularity[/spell]`
 * []spell]Glyph of Barkskin[/spell]`
 *
 */
class bbtips_spell extends bbtips
{

	/**
	* Parses information
	* @access public
	**/
	function parse($name)
	{
	    global $phpbb_root_path, $phpEx;
	    
		if (trim($name) == '')
		{
		    return false;
		}

		if ( !class_exists('wowhead_cache')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_cache.' . $phpEx);    
        }
        $cache = new wowhead_cache();

		$rank = (!array_key_exists('rank', $this->args)) ? '' : $this->args['rank'];

		if (!$result = $cache->getObject($name, 'spell', $this->lang, $rank))
		{
			if (is_numeric($name))
			{
				$result = $this->_getSpellByID($name);
			}
			else
			{
				$result = $this->_getSpellByName($name, $rank);
			}

			if (!$result)
			{
				return $this->_notfound('spell', $name);
			}
			else
			{
				$cache->saveObject($result);
				return $this->_generateHTML($result, 'spell', '');
			}
		}
		else
		{
			
			return $this->_generateHTML($result, 'spell', '', $rank);
		}
	}

	/**
	* search using Dom parser
	* 
	* @param unknown_type $name
	* @param unknown_type $rank
	*/
	public function _getSpellByName($name, $rank = 0)
	{
		global $phpEx, $phpbb_root_path; 
        if ( !class_exists('simple_html_dom_node')) 
        {
            include ($phpbb_root_path . 'includes/bbdkp/bbtips/simple_html_dom.' . $phpEx); 
        }
        
        $this->make_searchurl($name, 'spell');
		$html = $this->gethtml($name, 'spell');
		
		if ($html == NULL)
		{
		    //in case of bad request
		    return false; 
		}
		
		//searches that return 1 result
		if (preg_match('#Location: \/spell=([0-9]{1,10})#s', $html, $match))
		{
			$spell = array(
				'name'			=>	ucwords(strtolower($name)),
				'search_name'	=>	$name,
				'itemid'		=>	$match[1],
				'rank'			=>	0,
				'type'			=>	'spell',
				'lang'			=>	$this->lang
			);
			
			return $spell;
		}
		
		// get the line we need to pull the data
		$line = $this->spellLine($html, $name);
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
			
			// decode the JSON result
			if (!$json = json_decode($line, true))
			{
				return false;
			}
				
			// loop through the resulting array and pull out the ones that match the name
			$json_array = array();
			foreach ($json as $spell)
			{
				$spell['name'] = substr($spell['name'], 1);
				if (stripslashes(strtolower($spell['name'])) == stripslashes(strtolower($name)))
				{
					// add it to the array
					$json_array[] = $spell;
				}
			}
			
			if (sizeof($json_array) == 0)
			{
				return false;
			}
			
			// grab first one since ranks dont exist anymore
			//$result = ($rank != '') ? $json_array[$rank - 1] : $json_array[sizeof($json_array) - 1];
			$result = $json_array[sizeof($json_array) - 1];

			$spell = array(	// finally return what we found
				'name'			=>	stripslashes($result['name']),
				'search_name'	=>	$name,
				'itemid'		=>	$result['id'],
				'rank'			=>	($rank != '') ? $rank : 0,
				'type'			=>	'spell',
				'lang'			=>	$this->lang
			);
			
			return $spell; 
			
		}
		
	}
	
	/**
	* Generates HTML for link
	* @access private
	**/
	function _generateHTML($info, $type, $size = '', $gems = '')
	{
	    $info['link'] = $this->_generateLink($info['itemid'], $type);
		{
			return $this->_replaceWildcards($this->patterns->pattern('spell'), $info);
		}

	}
	

	/**
	* Queries Wowhead for Spell info by ID
	* @access private
	**/
	function _getSpellByID($id)
	{
		if (!is_numeric($id))
		{
		    return false;
		}
			

        $this->make_searchurl($id, 'spell');
		$data = $this->gethtml($id, 'spell');
		
		if ($data == '$WowheadPower.registerSpell')
		{
			return false;
		}
		else
		{
			switch ($this->lang)
			{
				case 'de':
					$str = 'dede';
					break;
				case 'fr':
					$str = 'frfr';
					break;
				case 'es':
					$str = 'eses';
					break;
				case 'en':
				default:
					$str = 'enus';
					break;
			}
			if (preg_match('#name_' . $str . ': \'(.+?)\',#s', $data, $match))
			{
				return array(
					'name'			=>	stripslashes($match[1]),
					'itemid'		=>	$id,
					'search_name'	=>	$id,
					'type'			=>	'spell',
					'rank'			=>	'',
					'lang'			=>	$this->lang
				);
			}
			else
			{
				return false;
			}
		}
	}
	
	
	private function spellLine($data, $name)
	{
		$found = false;		// assume failure
		$parts = explode(chr(10), $data);
		$name = strtolower($name);
		foreach ($parts as $line)
		{
			if (strpos($line, "new Listview({template: 'spell'") !== false)
			{
				if (strpos($line, "new Listview({template: 'spell', id: 'abilities',") !== false)
				{
					if (strpos(strtolower($line), $name) !== false)
					{
						$found = true;
						break;
					}
				}
				elseif (strpos($line, "new Listview({template: 'spell', id: 'talents',") !== false)
				{
					if(strpos(strtolower($line),  $name) !== false)
					{
						$found = true;
						break;
					}
				}
				elseif (strpos($line, "new Listview({template: 'spell', id: 'glyphs',") !== false)
				{
					if (strpos(strtolower($line),  $name) !== false)
					{
						$found = true;
						break;
					}
				}
				elseif (strpos($line, "new Listview({template: 'spell', id: 'professions',") !== false)
				{
					if(strpos(strtolower($line),  $name) !== false)
					{
						$found = true;
						break;
					}
				}
				elseif (strpos($line, "new Listview({template: 'spell', id: 'uncategorized-spells',") !== false)
				{
					if (strpos(strtolower($line), $name) !== false)
					{
						$found = true;
						break;
					}
				}
				elseif (strpos($line, "new Listview({template: 'spell', id: 'guild-perks',") !== false)
				{
					if(strpos(strtolower($line),  $name) !== false)
					{
						$found = true;
						break;
					}
				}
				elseif (strpos($line, "new Listview({template: 'spell', id: 'recipes',") !== false )
				{
					if(strpos(strtolower($line), $name) !== false)
					{
						$found = true;
						break;
					}
				}
				elseif (strpos($line, "new Listview({template: 'spell', id: 'spells',") !== false)
				{
					if(strpos(strtolower($line), $name) !== false)
					{
						$found = true;
						break;
					}
				}
				elseif (strpos($line, "new Listview({template: 'spell', id: 'companions',") !== false)
				{
					if(strpos(strtolower($line), $name) !== false)
					{
						$found = true;
						break;
					}
				}
				
			}	
		}
		
		if ($found && sizeof($line) > 0)
		{
			// clean the line up to make it valid JSON
			$line = substr($line, strpos($line, 'data: [{') + 6);
			$line = str_replace('});', '', $line);
			return $line;				
		}
		else
		{
			return false;
		}
	}
	
	
	

	
}
?>