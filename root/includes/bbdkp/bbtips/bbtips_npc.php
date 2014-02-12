<?php
/**
 * Wowhead npc parser
 *
 * @version 1.0.4
 * @copyright (c) 2010 bbdkp https://github.com/bbDKP/bbTips
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author Adam "craCkpot" Koch (admin@crackpot.us)
 * @author Sajaki (sajaki9@gmail.com)
 *
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
 * bbtips_npc class
 *
 * usage:
 * [npc]Illidan Stormrage[/npc]
 * including the new Pandaria Battle pets :
 * [npc]Adder[/npc]
 *
 */
class bbtips_npc extends bbtips
{
	public $lang;
	public $patterns;
	public $args;

	public function parse($name)
	{
		if (trim($name) == '')
		{
			return false;
		}
		
		global $config, $phpEx, $phpbb_root_path; 
	
		$this->lang = $config['bbtips_lang'];
		
		if (!class_exists('wowhead_cache')) 
		{
            require($phpbb_root_path . 'includes/bbdkp/bbtips/dbal.' . $phpEx);
        }
		$cache = new wowhead_cache();
		
		if (!$result = $cache->getNPC($name, $this->lang))
		{
			// not found in cache
			$result = $this->_getNPCInfo($name);
			if (!$result)
			{
				// not found
				return $this->NotFound('NPC', $name);
			}
			else
			{
				// found, save it and display
				$cache->saveNPC($result);
				
				return $this->_generateHTML($result, 'npc');
			}
		}
		else
		{
			
			return $this->_generateHTML($result, 'npc');
		}
	}

	private function _getNPCInfo($name)
	{
		if (trim($name) == '')
		{
			return false;
		}

		if (!is_numeric($name))
		{
			
			$this->make_searchurl($name, 'npc');
			$data = $this->gethtml($name, 'npc');
			
			// get the id of the npc
			if (preg_match('#Location: /npc=([0-9]{1,10})#s', $data, $match))
			{
				return array(
					'npcid'			=>	$match[1],
					'name'			=>	ucwords(strtolower($name)),
					'search_name'	=>	$name,
					'lang'			=>	$this->lang
				);	
			}
			else
			{
				$npc = $this->_getIDFromSearch($name, $data);
				if (!$npc) 
				{
					return false; 
				}
				else 
				{
					return $npc; 
				}
				
			}
		}
		else
		{
						
			$this->make_searchurl($name, 'npc');
			$data = $this->gethtml($name, 'npc');
			
			$npc_name = $this->_getNPCNameFromID($data);
			return array(
				'npcid'			=>	$name,
				'name'			=>	$npc_name,
				'search_name'	=>	$name,
				'lang'			=>	$this->lang
			);
			
			
			
		}

	}

	private function _getIDFromSearch($name, $data)
	{
		if (trim($data) == '')
		{
			return false;
		}

		// the line we need to pull the info from
		$line = '';
		$parts = explode(chr(10), $data);
		foreach ($parts as $line)
		{
			if (strpos($line, "new Listview({template: 'npc', id: 'npcs',") !== false)
			{
				$line = substr($line, strpos($line, 'data: [{') + 6);
				$line = str_replace('});', '', $line);
				break 1;	
			}
		}
		
		if ($line == '')
		{
			return false;	
		}
		
		/* cleanup json
		 * see http://www.bbdkp.com/tracker.php?p=5&t=209
		* and http://www.wowhead.com/forums&topic=205251&p=3247970
		*/
		$line = str_replace("frombeta:'1'", '"frombeta":1' , $line);
        $line = str_replace('searchpopularity', '"searchpopularity"', $line);
        $linearray = $this->json_split_objects($line);

        foreach($linearray as $i => $jsonline)
        {
            break;
        }

        // json decode
        if (!$npc = json_decode($jsonline, true))
        {
            return false;
        }

        $npc = array(
            'npcid'			=>	$npc['id'],
            'name'			=>	$npc['name'],
            'search_name'	=>	$npc['name'],
            'lang'			=>	$this->lang
        );

        return $npc;

	}


	private function _getNPCNameFromID($data)
	{
		while (preg_match('#<h1>(.+?)</h1>#s', $data, $match))
		{
			if (strpos($match[1], "World of Warcraft") === false) {
				return $match[1];
			}
			else
			{
				$data = str_replace($match[0], '', $data);
			}
		}
	}
	
	/**
	* Generates HTML for link
	* @access private
	**/
	private function _generateHTML($info, $type, $size = '', $rank = '', $gems = '')
	{
	    $info['link'] = $this->GenerateLink($info['npcid'], 'npc');
		return $this->ReplaceWildcards($this->patterns->pattern($type), $info);
	}
	
}