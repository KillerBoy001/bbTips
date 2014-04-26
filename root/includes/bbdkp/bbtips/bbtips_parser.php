<?php
/**
* bbTips Parser 
*
* @version 1.0.4
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* @copyright (c) 2010 bbdkp <https://github.com/bbdkp>
* @author : sajaki@bbdkp.com
* 
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

/**
 * Handles Wowhead tooltips
 *
 */
class bbtips_parser
{

	public function parse($message)
	{
	    global $phpbb_root_path, $phpEx, $config;
	    $match = array();
	    $parses = 0;

	    //max 600 items will be parsed no matter what the setting of maxparse is set too
		//600 will parse approximetly 8 different wowchar character profiles...
		if (isset($config['bbtips_maxparse']))
		{
		    $maxparse = min(600,(int) $config['bbtips_maxparse']);

		}
		else
		{
			//bbTips is not installed
			return $message;
		}

		$bbcodelist = "item|quest|achievement|craft|itemset|spell|itemico|itemdkp|npc|wowchar|ptritem|ptrquest|ptrachievement|ptrcraft|ptritemset|ptrspell|ptritemico|ptritemdkp|ptrnpc";

	    while (
	    	    ($parses < $maxparse) &&
			  	preg_match('#\[('. $bbcodelist.')\](.+?)\[/('. $bbcodelist.')\]#s', $message, $match) or
			  	preg_match('#\[('. $bbcodelist.') (.+?)\](.+?)\[/('. $bbcodelist.')\]#s', $message, $match)
		  	  )
		  {
				$args = array();

				if ( !class_exists('bbtips'))
		        {
		        	require($phpbb_root_path . 'includes/bbdkp/bbtips/bbtips.' . $phpEx);
		        }

				if (  (count($match)>= 5) && (
						strpos($match[2], 'lang=') !== false || strpos($match[2],'mats') !== false || strpos($match[2], 'enchant=') !== false ||
						strpos($match[2], 'size=') !== false || strpos($match[2],'rank=')  !== false || strpos($match[2], 'gems=') !== false ||
						strpos($match[2], 'loc=') !== false || strpos($match[2],'realm=')  !== false || strpos($match[2],'region=')  !== false  )
					)
				{
					// we have arguments
					$args = $this->arguments($match[2]);
				}

                if (isset($match))
                {
	                switch ($match[1])
					{
						case 'item':
						case 'itemico':
						case 'itemdkp':
						case 'ptritem':
						case 'ptritemico':
						case 'ptritemdkp':
			        		if ( !class_exists('bbtips_item'))
			                {
			                    require($phpbb_root_path . 'includes/bbdkp/bbtips/bbtips_item.' . $phpEx);
			                }
			                $object = new bbtips_item($args, $match[1]);
							break;
						case 'craft':
						case 'ptrcraft':
						    if ( !class_exists('bbtips_craft'))
			                {
			                    require($phpbb_root_path . 'includes/bbdkp/bbtips/bbtips_craft.' . $phpEx);
			                }
			                $object = new bbtips_craft($args);
							break;
						case 'itemset':
						case 'ptritemset':
			        		if ( !class_exists('bbtips_itemset'))
			                {
			                    require($phpbb_root_path . 'includes/bbdkp/bbtips/bbtips_itemset.' . $phpEx);
			                }
			                $object = new bbtips_itemset($args);
							break;
						case 'quest':
						case 'ptrquest':
						    if ( !class_exists('bbtips_quest'))
			                {
			                    require($phpbb_root_path . 'includes/bbdkp/bbtips/bbtips_quest.' . $phpEx);
			                }
			                $object = new bbtips_quest($args);
							break;
						case 'spell':
						case 'ptrspell':
			        		if ( !class_exists('bbtips_spell'))
			                {
			                    require($phpbb_root_path . 'includes/bbdkp/bbtips/bbtips_spell.' . $phpEx);
			                }
			                $object = new bbtips_spell($args);
							break;
						case 'achievement':
						case 'ptrachievement':
			                if ( !class_exists('bbtips_achievement'))
			                {
			                    require($phpbb_root_path . 'includes/bbdkp/bbtips/bbtips_achievement.' . $phpEx);
			                }
			                $object = new bbtips_achievement($args);
							break;
						case 'npc':
						case 'ptrnpc':
			                if ( !class_exists('bbtips_npc'))
			                {
			                    require($phpbb_root_path . 'includes/bbdkp/bbtips/bbtips_npc.' . $phpEx);
			                }
			                $object = new bbtips_npc($args);
							break;
						case 'wowchar':
							// uses the arguments realm and region
				            if ( !class_exists('wowcharacter'))
			                {
			                    require($phpbb_root_path . 'includes/bbdkp/bbtips/bbtips_character.' . $phpEx);
			                }
			                $object = new wowcharacter($args);
							break;
						default:
							break;
					}

					if (isset($object))
					{
						$object->ptr = (substr($match[1],0,3) == 'ptr') ? true: false;
					}

					$namein = (sizeof($args) > 0) ? html_entity_decode($match[3], ENT_QUOTES) : html_entity_decode($match[2], ENT_QUOTES);
			   		$namein= trim($namein);
				   	// prevent any unwanted script execution or html formatting
					$nameout = $this->html2txt($namein);
					if ($nameout != $namein)
					{
					    $message = str_replace($match[0], "<span class=\"notfound\">Illegal HTML/JavaScript found.</span>", $message);
					}
					else
					{
						// ok tag content allowed, go to parser
					    $message =  isset($object) ? str_replace($match[0], $object->parse(trim($nameout)), $message) : $message;
					}


                }


		   		$parses++;
		}

		unset($object);
		return $message;
	}

	/**
	 * strips illegal html/javascript
	 */
	private function html2txt($document)
	{
	  $search = array('@]*?>.*?@si',          // Strip out javascript
	                 '@]*?>.*?@siU',          // Strip style tags properly
	                 '@<[\/\!]*?[^<>]*?>@si', // Strip out HTML tags
	                 '@@',                    // Strip multi-line comments including CDATA
	  				 '@http@si' , 			  // strip out http
	  				 '@HTTP@si' , 			  // strip out HTTP
	  				 '@https@si' , 			  // strip out https
	  );
	  $text = preg_replace($search, '', $document);
	  return trim($text);
	}


	/**
	 * turn arguments into array
	 *
	 * @param argumentstring $in
	 * @return array
	 */
	private function arguments($in)
	{
		if (strlen($in) == 0)
		{
			return false;
		}

		// has unencodes quotes ?
		if (strpos($in, '"') !== false)
		{
			$in = str_replace('"', '', $in);
		}

		// has encoded quotes ?
		if (strpos($in, '&quot;') !== false)
		{
			$in = str_replace('&quot;', '', $in);
		}

		// is there no space in the middle ?
		if (strpos($in, ' ') === false)
		{
			$args = array();
			// only one argument
			if (trim($in) == 'mats')
			{
				return array(
					'mats'	=>	true
				);
			}
			elseif (trim($in) == 'realm')
			{
				// used with wowchar
				return array('realm' => true);
			}
			elseif (trim($in) == 'region')
			{
				// used with wowchar
				return array('region' => true);
			}
			else
			{
				$pre = substr($in, 0, strpos($in, '='));
				$post = substr($in, strpos($in, '=') + 1);
				$args[$pre]=$post;

				return $args;
			}
		}
		else
		{
			$args = array();
			// multiple arguments
			$in_array = explode(' ', $in);

			foreach ($in_array as $value)
			{
				if ($value == 'mats')
				{
					$args['mats'] = true;
				}

				elseif ($value == 'realm')
				{
					// used with wowchar
					$args['realm'] = true;
				}
				elseif ($value == 'region')
				{
					// used with wowchar
					$args['region'] = true;
				}
				else
				{
					$pre = substr($value, 0, strpos($value, '='));
					$post = substr($value, strpos($value, '=') + 1);
					$args[$pre] = $post;
				}

			}
			return $args;
		}
	}
}
?>