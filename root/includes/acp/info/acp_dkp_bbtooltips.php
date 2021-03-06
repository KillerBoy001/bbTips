<?php
/**
* This class manages Itemstats 
*
* @package bbDkp.acp
* @author sajaki9@gmail.com
* @version 1.0.4
* @copyright (c) 2009 bbdkp https://github.com/bbDKP/bbTips
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
* 
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/**
* @package module_install
*/
class acp_dkp_bbtooltips_info
{
	function module()
	{
		return array(
			'filename'	=> 'acp_dkp_bbtooltips',
			'title'		=> 'ACP_DKP_DKPTOOLTIPS',
			'version'	=> '1.0.7',
			'modes'		=> array(
    			'bbtooltips'	=> array('title' => 'ACP_DKP_DKPTOOLTIPS'),
			),
		);
	}

	function install()
	{
	}

	function uninstall()
	{
	}
}


?>
