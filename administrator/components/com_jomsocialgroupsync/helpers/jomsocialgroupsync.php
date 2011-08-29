<?php
/**
 * @version     1.0.0
 * @package     com_jomsocialgroupsync
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Lighthouse Consulting and Design
 */

// No direct access
defined('_JEXEC') or die;

/**
 * JomSocialGroupSync helper.
 */
class JomSocialGroupSyncHelper
{

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 * @since	1.6
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$assetName = 'com_jomsocialgroupsync';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}
    
    /**
     * Get Joomla ACL Group
     * 
     * $jgroupid int
     * @return   string
     * @since    1.6
     */
    public static function getJGroupName( $jgroupid )
    {
        $db = JFactory::getDbo();
        $db->setQuery("SELECT title FROM #__usergroups WHERE id = $jgroupid");
        $jGroupName = $db->loadResult();
        return $jGroupName;
    }

    /**
     * Get JomSocial Group
     * 
     * $cgroupid int
     * @return   string
     * @since    1.6
     */
    public static function getCGroupName( $cgroupid )
    {
        $db = JFactory::getDbo();
        $db->setQuery("SELECT name FROM #__community_groups WHERE id = $cgroupid");
        $cGroupName = $db->loadResult();
        return $cGroupName;
    }
}
