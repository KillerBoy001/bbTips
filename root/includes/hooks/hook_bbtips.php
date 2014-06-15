<?php
/**
 *
 * @package bbdkp
 * @copyright 2014 bbdkp <https://github.com/bbDKP>
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @author sajaki <sajaki@gmail.com>
 * @link http://www.bbdkp.com
 * @version 1.0.5
 *
 */

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
    exit;
}

//Don't load hook if not installed.
if (empty($config['bbdkp_plugin_bbtips_version']))
{
    return;
}

function bbtipshooks(&$hook, $handle, $include_once = true)
{
    global $_SID, $_EXTRA_URL, $phpbb_hook, $phpbb_root_path, $phpEx;;
    global $user,$template,$forum_id,$topic_id,$post_id,$topic_data;

    //start with the result from the previous hook
    $result = $hook->previous_hook_result('display');
    //in view mode

    if(isset($template->_tpldata['postrow'] ))
    {
        if(is_array($template->_tpldata['postrow']) && count($template->_tpldata['postrow'])>0)
        {
            if (!class_exists('bbtips_parser'))
            {
                require($phpbb_root_path . 'includes/bbdkp/bbtips/bbtips_parser.' . $phpEx);
            }
            $bbtips = new bbtips_parser;

            //parse all messages
            foreach( $template->_tpldata['postrow'] as $key => $val)
            {
                $template->_tpldata['postrow'][$key]['MESSAGE'] = $bbtips->parse( $template->_tpldata['postrow'][$key]['MESSAGE'] );
            }
            unset($bbtips);
        }
    }
    elseif(isset($template->_tpldata['news_row'] ))
    {
        if(is_array($template->_tpldata['news_row']) && count($template->_tpldata['news_row'])>0)
        {
            if (!class_exists('bbtips_parser'))
            {
                require($phpbb_root_path . 'includes/bbdkp/bbtips/bbtips_parser.' . $phpEx);
            }
            $bbtips = new bbtips_parser;

            //parse all messages
            foreach( $template->_tpldata['news_row'] as $key => $val)
            {
                $template->_tpldata['news_row'][$key]['MESSAGE'] = $bbtips->parse( $template->_tpldata['news_row'][$key]['MESSAGE'] );
            }
            unset($bbtips);
        }
    }
    else
    {
        //in preview mode
        if( isset($template->_tpldata['.'][0]['PREVIEW_MESSAGE']) )
        {
            if (!class_exists('bbtips_parser'))
            {
                require($phpbb_root_path . 'includes/bbdkp/bbtips/bbtips_parser.' . $phpEx);
            }
            $bbtips = new bbtips_parser;
            $template->_tpldata['.'][0]['PREVIEW_MESSAGE'] = $bbtips->parse( $template->_tpldata['.'][0]['PREVIEW_MESSAGE']);
            unset($bbtips);
        }
        elseif( isset($template->_tpldata['.'][0]['RAIDPLANNERMESSAGE']) )
        {
            if (!class_exists('bbtips_parser'))
            {
                require($phpbb_root_path . 'includes/bbdkp/bbtips/bbtips_parser.' . $phpEx);
            }
            $bbtips = new bbtips_parser;
            $template->_tpldata['.'][0]['RAIDPLANNERMESSAGE'] = $bbtips->parse( $template->_tpldata['.'][0]['RAIDPLANNERMESSAGE']);
            unset($bbtips);
        }
    }

    $template->assign_vars(array(
        'S_BBTIPS_REMOTE'      =>  (isset($config['bbtips_localjs']) ? $config['bbtips_localjs'] : ' '),
    ));
}

/**
 * Register all hooks if board is active
 */
if (!$config['board_disable'])
{
    $phpbb_hook->register(array('template', 'display'), 'bbtipshooks');
}


