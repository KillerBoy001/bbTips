<?php
/**
 *
 * @package bbdkp
 * @copyright 2014 bbdkp <https://github.com/bbDKP>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author sajaki <sajaki@gmail.com>
 * @link http://www.bbdkp.com
 * @version 1.0.4
 * Date: 11/02/14
 * Time: 15:25
 *
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
    exit;
}


function hook_bbtips(&$hook, $handle, $include_once = true)
{
    global $_SID, $_EXTRA_URL, $phpbb_hook, $phpbb_root_path, $phpEx;;
    global $user,$template,$forum_id,$topic_id,$post_id,$topic_data;

    //start with the result from the previous hook
    $result = $hook->previous_hook_result('display');
    //in view mode
    if(is_array($template->_tpldata['postrow']) && count($template->_tpldata['postrow'])>0)
    {
        if (!class_exists('bbtips'))
        {
            require($phpbb_root_path . 'includes/bbdkp/bbtips/parse.' . $phpEx);
        }
        $bbtips = new bbtips;

        //parse all messages
        foreach( $template->_tpldata['postrow'] as $key => $val)
        {
            $template->_tpldata['postrow'][$key]['MESSAGE'] = $bbtips->parse( $template->_tpldata['postrow'][$key]['MESSAGE'] );
        }
    }
    else
    {
        //in preview mode
        if( isset($template->_tpldata['.'][0]['PREVIEW_MESSAGE']) )
        {
            if (!class_exists('bbtips'))
            {
                require($phpbb_root_path . 'includes/bbdkp/bbtips/parse.' . $phpEx);
            }
            $bbtips = new bbtips;
            $template->_tpldata['.'][0]['PREVIEW_MESSAGE'] = $bbtips->parse( $template->_tpldata['.'][0]['PREVIEW_MESSAGE']);
        }
    }
}

/**
 * Register all hooks
 */

$phpbb_hook->register(array('template', 'display'), 'hook_bbtips');