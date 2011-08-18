<?php
/**
 * @version     1.0.0
 * @package     com_civigroupsync
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by com_combuilder - http://www.notwebdesign.com
 */

// No direct access
defined('_JEXEC') or die;

/**
 * CiviGroupSync helper.
 */
class CiviGroupSyncHelper
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

		$assetName = 'com_civigroupsync';

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
     * Get CiviCRM Group
     * 
     * $cgroupid int
     * @return   string
     * @since    1.6
     */
    public static function getCGroupName( $cgroupid )
    {
        $db = JFactory::getDbo();
        $db->setQuery("SELECT title FROM civicrm_group WHERE id = $cgroupid");
        $cGroupName = $db->loadResult();
        return $cGroupName;
    }
}
