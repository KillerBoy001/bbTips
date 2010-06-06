<?php
/**
* bbdkp-wowhead Link Parser v3 - Item Icon Extension
*
* @package bbDkp.includes
* @version $Id $
* @Copyright (c) 2008 Adam Koch
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
* By: Adam "craCkpot" Koch (admin@crackpot.us) -- Adapted by bbdkp Team (sajaki9@gmail.com)
*
**/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}
 
class wowhead_itemico extends wowhead
{
	var $lang;
	var $patterns; 

	function wowhead_itemico()
	{
		global $phpEx, $phpbb_root_path; 
		
		if (!class_exists('wowhead_patterns')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_patterns.' . $phpEx); 
        }
        $this->patterns = new wowhead_patterns();
	}

	/**
	* Parse Item Icons
	* @access public
	**/
	function parse($name, $args = array())
	{
		global $config; 
		global $phpEx, $phpbb_root_path; 
		
		if (trim($name) == '')
		{
		    return false;
		}

		if (!class_exists('wowhead_cache')) 
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/wowhead_cache.' . $phpEx); 
        }
		$cache = new wowhead_cache();

		$size = (!array_key_exists('size', $args)) ? 'medium' : $args['size'];
		$this->lang = $config['bbtips_lang'];

		if (!$result = $cache->getObject($name, 'itemico', $this->lang, '', $size))
		{
			if (!$result = $this->_getItemIcon($name, $size))
			{
				
				return $this->_notfound('itemico', $name);
			}
			else
			{
				$cache->saveObject($result);
				
				return $this->_generateHTML($result, 'itemico', $size);
			}
		}
		else
		{
			
			return $this->_generateHTML($result, 'itemico', $size);
		}
	}
	
	/**
	* Generates HTML for link
	* @access private
	**/
	function _generateHTML($info, $type, $size = '', $rank = '', $gems = '')
	{
		 $info['link'] = $this->_generateLink($info['itemid'], $type);
		 return $this->_replaceWildcards($this->patterns->pattern('icon_' . $size), $info);
	
	}
	
	

	/**
	* Queries Wowhead for an Item's Icon
	* @access private
	**/
	function _getItemIcon($name, $size)
	{
		if (trim($name) == '')
			return false;

		// get XML data
		$data = $this->_read_url($name);

		if ($this->_useSimpleXML())
		{
			// PHP 5.x
			if (!$this->_allowSimpleXMLOptions())
			{
				$data = $this->_removeCData($data);
				$xml = simplexml_load_string($data, 'SimpleXMLElement');
			}
			else
			{
				$xml = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
			}

			if ($xml->error == '')
			{
				// woohoo, item found

				return array(
					'name'			=>	(string)$xml->item->name,
					'search_name'	=>	$name,
					'itemid'		=>	(string)$xml->item['id'],
					'icon'			=>	'http://static.wowhead.com/images/wow/icons/' . $size . '/' . strtolower($xml->item->icon) . '.jpg',
					'icon_size'		=>	$size,
					'lang'			=>	$this->lang,
					'type'			=>	'itemico'
				);
			}
			else
			{
				return false;
			}

			unset($xml);
		}
		
	}
}
?>