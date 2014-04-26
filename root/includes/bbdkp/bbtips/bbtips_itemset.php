<?php
/**
* Wowhead Itemset parser
*
* @version 1.0.4
* @copyright (c) 2010 bbdkp https://github.com/bbDKP/bbTips
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @author Adam "craCkpot" Koch (admin@crackpot.us)
* @author Sajaki (sajaki9@gmail.com)
* 
*/

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
 * Class bbtips_itemset
 *
 * example usage : [itemset]Sanctified Ymirjar Lord's Plate[/itemset]
 *
 * * Syntax
 * <code>
 * [itemset {parameters}]{name or ID}[/item]
 * </code>
 * example usage
 * <code>
 * [itemset]-259[/itemset]
 * [itemset]Sanctified Ymirjar Lord's Plate[/itemset]
 * </code>
 *
 */
class bbtips_itemset extends bbtips
{
	// variables
	public  $lang;
	private $itemset = array();
	private $itemset_items = array();
	private $setid;
	public  $patterns;
    public  $args;

	/**
	* Parses itemset bbcode
	* @access public
	**/
	public function parse($name)
	{
		if (trim($name) == '')
		{
			return false;
		}
		
		if (!$result = $this->getItemset($name))
		{
			// not found so call wowhead
			if(is_numeric($name))
			{
				$result = $this->_getItemsetByID($name); 
			}
			else 
			{
				// json lookup
				$result = $this->_getItemsetByName($name);
			}
				
			if (!$result)
			{
				// item not found 
				return $this->NotFound($name);
			}
			else
			{   //insert
				$this->saveItemset($this->itemset, $this->itemset_items);
				return $this->_generateHTML();
			}
			
		}
		else
		{
			// already in db
			$this->itemset = $result;
			$this->itemset_items = $this->_getItemsetReagents($this->itemset['setid']);
			return $this->_generateHTML();
		} 
	}

    /**
     * inserts an itemset
     *
     * @param array $itemset
     * @param array $items
     * @return boolean
     */
    public function saveItemset($itemset, $items )
    {
        global $db;


        if (!is_array($itemset) || !is_array($items) || !isset($itemset['setid']) || !isset($itemset['name']) )
        {
            return false;
        }

        // save the itemset first, then we'll handle each item
        $sql_ary = array(
            'setid'         => (int) $itemset['setid'],
            'name'    	    => $itemset['name'],
            'search_name'   => $itemset['search_name'],
            'lang'          => $itemset['lang'],
        );

        $sql = 'INSERT INTO ' . BBTIPS_ITEMSET_TBL . ' ' . $db->sql_build_array('INSERT', $sql_ary);
        $db->sql_query($sql);
        if ($db->sql_affectedrows() == 0)
        {
            global $user;
            $user->add_lang(array('mods/dkp_tooltips'));
            trigger_error(  sprintf($user->lang['BBTOOLTIPS_ERRORCACHING'], $itemset['name'] , BBTIPS_ITEMSET_TBL), E_USER_WARNING ) ;
            return false;
        }
        else
        {
            $sql = "DELETE FROM " . BBTIPS_ITEMSET_REAGENT_TBL . ' WHERE SETID = ' . (int) $itemset['setid'];
            $db->sql_query($sql);
            foreach ($items as $item)
            {
                $sql_ary = array(
                    'setid'     => (int) $itemset['setid'],
                    'itemid'    => (int) $item['itemid'],
                    'name'      => $item['name'],
                    'quality'   => $item['quality'],
                    'icon'      => $item['icon'],
                );
                $sql = "INSERT INTO " . BBTIPS_ITEMSET_REAGENT_TBL . ' ' .  $db->sql_build_array('INSERT', $sql_ary);
                $db->sql_query($sql);
                if ($db->sql_affectedrows() == 0)
                {
                    global $user;
                    $user->add_lang(array('mods/dkp_tooltips'));
                    trigger_error(  sprintf($user->lang['BBTOOLTIPS_ERRORCACHING'], $item['name'] , BBTIPS_ITEMSET_REAGENT_TBL), E_USER_WARNING ) ;
                    return false;
                }
            }
        }
    }

    /**
     * Gets an itemset
     *
     * @param string $name
     * @return string
     */
    private function getItemset($name)
    {
        global $db, $config;

        $search = $db->sql_like_expression($db->any_char . $db->sql_escape($name) . $db->any_char) ;

        $query_text = 'SELECT setid, name FROM ' . BBTIPS_ITEMSET_TBL . ' WHERE
					 (search_name ' . $search . '
						  OR name '. $search . "
					  )  AND lang='"  . $config['bbtips_lang'] . "'";

        $result = $db->sql_query($query_text);

        if ( $db->sql_affectedrows() == 0)
        {
            $db->sql_freeresult($result);
            return false;
        }
        else
        {
            $row =  $db->sql_fetchrow($result);
            return $row;
        }

    }

    /**
     * Gets itemset components
     *
     * @param int $id
     * @return array
     */
    private function _getItemsetReagents($id)
    {
        if (trim($id) == '')
        {
            return false;
        }
        global $db;

        $reagents = array();

        $query_text = 'SELECT itemid, name, quality, icon FROM ' . BBTIPS_ITEMSET_REAGENT_TBL . "
						WHERE setid='" . $id . "'
						ORDER BY name ASC";

        $result = $db->sql_query($query_text);

        if ( $db->sql_affectedrows() == 0)
        {
            $db->sql_freeresult($result);
            return false;
        }
        else
        {
            while ($row = $db->sql_fetchrow($result))
            {
                array_push($reagents, $row);
            }
            $db->sql_freeresult($result);
            return $reagents;
        }

    }


    private function _getItemsetByID($id)
	{
		if (trim($id) == '' || !is_numeric($id))
		{
			return false;
		}

		$this->make_searchurl($id, 'itemset');
		$data = $this->gethtml($id, 'itemset');
		
		if (trim($data) == '' || empty($data)) 
		{ 
			return false; 
		}
				
		// pull the properly formatted name
		$nameline = '';
		$parts = explode(chr(10), $data);
		foreach ($parts as $nameline)
		{	
			if (strpos($nameline, 'var g_pageInfo = {type:') !== false)
			{
				break 1;
			}
		}
		
		if (!preg_match("/name: '(.+?)'\};/", $nameline, $linematch))
		{
			return false;	
		}
		
		// quotes are escaped with backslashes in json
		$this->itemset = array(
			'setid'			=>	$id,
			'name'			=>	stripslashes($linematch[1]),
			'search_name'	=>	stripslashes($linematch[1]),
			'lang'			=>	$this->lang
		);
		
		// now pull the items
		while (preg_match('#<span class="q([0-6]{1})"><a href="\/item=(.+?)">(.+?)</a></span>#s', $data, $items))
		{
			$this->itemset_items[] = array(
				'setid'		=>	$id,
				'itemid'	=>	$items[2],
				'name'		=>	$items[3],
				'quality'	=>	$items[1],
				'icon'		=>	'http://static.wowhead.com/images/wow/icons/small/' . $this->_getItemIcon($items[2])
			);
			$data = str_replace($items[0], '', $data);
		}		
		
		if (sizeof($this->itemset) == 0 || sizeof($this->itemset_items) == 0)
			return false;
		else
			return true;	
	}
	
	function _getItemIcon($id)
	{
		$this->make_searchurl($id, 'item');
		$xml_data = $this->gethtml($id, 'item');
			
		libxml_use_internal_errors(true);
		// accounts for SimpleXML not being able to handle 3 parameters if you're using PHP 5.1 or below.
		if (!$this->AllowSimpleXMLOptions())
		{
			// remove CDATA tags
			$xml_data = $this->RemoveCData($xml_data);
			$xml = simplexml_load_string($xml_data, 'SimpleXMLElement');
		}
		else
		{
			$xml = simplexml_load_string($xml_data, 'SimpleXMLElement', LIBXML_NOCDATA);
		}
			
		$errors = libxml_get_errors();
		if (empty($errors))
		{
			 	libxml_clear_errors();
			 	
			 	if(isset($xml->error))
			 	{
			 		return false;
			 	}
			 	
			 	$iconname = strtolower($xml->item->icon) . '.jpg';
				unset($xml, $xml_data);
				return $iconname; 
		}
		else
		{
			// set error handler off - to free memory
			unset($xml);
			unset($errors); 
			libxml_clear_errors();
			return false;
		}			
	}
	
	
	
	/**
	* Returns the summary line we need for getting itemset items
	* @access private
	**/
	private function _summaryLine($data)
	{
		$parts = explode(chr(10), $data);
		foreach ($parts as $line)
		{
			if (strpos($line, "new Summary({id: 'itemset', template: 'itemset',") !== false)
			{
				$line = substr($line, strpos($line, 'data: [{') + 6);
				$line = str_replace('});', '', $line);
				return $line;
				break;
			}
			elseif (strpos($line, "new Listview({template: 'itemset', id: 'itemsets',") !== false)
			{
				$line = substr($line, strpos($line, 'data: [{') + 6);
				$line = str_replace('});', '', $line);
				return $line;
				break;	
			}
			elseif (strpos($line, "new Listview({template: 'itemset', id: 'item-sets',") !== false)
			{
				$line = substr($line, strpos($line, 'data: [{') + 6);
				$line = str_replace('});', '', $line);
				return $line;
				break;
			}
		}
		return false;
	}
	
	private function _getItemsetByName($name)
	{
		if (trim($name) == '')
		{
			return false;
		}

		$this->make_searchurl($name, 'itemset');
		$data = $this->gethtml($name, 'itemset');
		
		if (trim($data) == '' || empty($data)) 
		{ 
			return false; 
		}
				
		if (preg_match('#Location: \/itemset=([\-0-9]{1,10})#s', $data, $match))
		{
			// since it redirected to a new page, we must pull that data
			$this->make_searchurl($match[1], 'itemset');
			$data = $this->gethtml($match[1], 'itemset');
		
			$nameline = '';
			$parts = explode(chr(10), $data);
			foreach ($parts as $nameline)
			{	
				if (strpos($nameline, 'var g_pageInfo = {type:') !== false)
				{
					break 1;
				}
			}
			
			if (!preg_match("/name: '(.+?)'\};/", $nameline, $linematch))
			{
				return false;	
			}
			
			$this->itemset = array(
				'setid'			=>	$match[1],
				'name'			=>	$linematch[1],
				'search_name'	=>	$name,
				'lang'			=>	$this->lang
			);
			
			// now time to pull the items
			while (preg_match('#<span class="q([0-6]{1})"><a href="\/item=(.+?)">(.+?)</a></span>#s', $data, $items))
			{
				$this->itemset_items[] = array(
					'setid'		=>	$match[1],
					'itemid'	=>	$items[2],
					'name'		=>	$items[3],
					'quality'	=>	$items[1],
					'icon'		=>	'http://static.wowhead.com/images/wow/icons/small/' . $this->_getItemIcon($items[2])
				);
				$data = str_replace($items[0], '', $data);
			}
			
			if (sizeof($this->itemset) == 0 || sizeof($this->itemset_items) == 0)
			{
				return false;
			}
			else
			{
				return true;
			}
		}
		
		// get the data line
		$line = $this->_summaryLine($data);

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
			
			// decode the json result
			if (!$json = json_decode($line, true))
			{
				return false;
			}

			foreach ($json as $itemset)
			{
				// strip the first character from the name
				$itemset['name'] = substr($itemset['name'], 1);
				
				if (strtolower($itemset['name']) == strtolower($name))
				{
					$this->itemset = array(
						'setid'			=>	$itemset['id'],
						'name'			=>	$itemset['name'],
						'search_name'	=>	$name,
						'lang'			=>	$this->lang
					);
					
					foreach ($itemset['pieces'] as $piece)
					{
						$this->make_searchurl($piece, 'item');
						$xml_data = $this->gethtml($piece, 'item');
			
						libxml_use_internal_errors(true);
						// accounts for SimpleXML not being able to handle 3 parameters if you're using PHP 5.1 or below.
						if (!$this->AllowSimpleXMLOptions())
						{
							// remove CDATA tags
							$xml_data = $this->RemoveCData($xml_data);
							$xml = simplexml_load_string($xml_data, 'SimpleXMLElement');
						}
						else
						{
							$xml = simplexml_load_string($xml_data, 'SimpleXMLElement', LIBXML_NOCDATA);
						}
							
						$errors = libxml_get_errors();
						if (empty($errors))
						{
							 	libxml_clear_errors();
							 	
							 	if(isset($xml->error))
							 	{
							 		return false;
							 	}
							 	
								$this->itemset_items[] = array(
									'setid'		=>	$itemset['id'],
									'itemid'	=>	$piece,
									'name'		=>	(string)$xml->item->name,
									'quality'	=>	(int)$xml->item->quality['id'],
									'icon'		=>	'http://static.wowhead.com/images/wow/icons/small/' . strtolower((string)$xml->item->icon) . '.jpg'
								);
								unset($xml_data, $xml);
						}
						else
						{
							// set error handler off - to free memory
							unset($xml);
							unset($errors); 
							libxml_clear_errors();
							return false;
						}			
						
					}
					
					if (sizeof($this->itemset) == 0 || sizeof($this->itemset_items) == 0)
					{
						return false;
					}
					else
					{
						return true;						
					}
				}
			}
		}
	}
	
	
	/**
	* Generates HTML
	* @access private
	**/
	private function _generateHTML()
	{
		// generate item HTML first
		$item_html = ''; $set_html = $this->patterns->pattern('itemset');

		foreach ($this->itemset_items as $item)
		{
			$patt = $this->patterns->pattern('itemset_item');
			$search = array(
				'{link}'	=>	$this->GenerateLink($item['itemid'], 'item'),
				'{name}'	=>	$item['name'],
				'{qid}'		=>	$item['quality'],
				'{icon}'	=>	$item['icon']
			);
			foreach ($search as $key => $value)
			{
				$patt = str_replace($key, $value, $patt);
			}
			$item_html .= $patt;
		}

		// now generate everything
		$set_html = str_replace('{link}', $this->GenerateLink($this->itemset['setid'], 'itemset'), $set_html);
		$set_html = str_replace('{name}', $this->itemset['name'], $set_html);
		$set_html = str_replace('{items}', $item_html, $set_html);

		return $set_html;
	}

	
}
?>