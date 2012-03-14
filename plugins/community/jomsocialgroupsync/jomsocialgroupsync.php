<?php
/**
 * @version		2012-03-14
 * @author		Lighthouse Consulting & Design
 * @package		JomSocial Group Sync
 * @copyright	Copyright (C) 2012. All rights reserved.
 * @license		GNU GPL
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JPATH_ROOT.'/components/com_community/libraries/core.php');

class  plgCommunityJomSocialGroupSync extends CApplications
{

    //NOTE: If a JGroup or JSGroup is deleted, we don't remove from the linked group
    
    /*
     * JomSocial -> Joomla
     * Update Joomla groups on JomSocial jsgroup-contact add 
     *
     * @param   string    $group  jsgroup object
     * @param   int       $memberid     Unique identifier (member)
     *
     * @return  void
     * @since   1.6
     */
    function onGroupJoin( &$group, $memberid ) {
        if (self::groupsSync ($group, $memberid)) {
            return true;
        }

    } //end jomsocial_post

    //NOTE: If a JGroup or JSGroup is deleted, we don't remove from the linked group
    
    /*
     * JomSocial -> Joomla
     * Update Joomla groups on JomSocial jsgroup-contact approved 
     *
     * @param   string    $group  jsgroup object
     * @param   int       $memberid     Unique identifier (member)
     *
     * @return  void
     * @since   1.6
     */
    function onGroupJoinApproved( &$group, $memberid ) {
        if (self::groupsSync ($group, $memberid)) {
            return true;
        }
    } //end jomsocial_post


    /*
     * Helper function to sync jomsocial and joomla groups
     *
     * @return  array
     * @since   1.6
     */
    function groupsSync ( &$group, $memberid ) {

        // Get sync mappings
        $mappings = self::getJomSocialGroupSyncMappings();
        if ( empty($mappings) ) {
            return;
        }

        // Instantiate JomSocial
        require_once JPATH_ROOT.'/administrator/components/com_community/defines.php';
        require_once JPATH_ROOT.'/components/com_community/libraries/core.php';

        jimport('joomla.user.helper');
        $model = CFactory::getModel( 'Groups' );

        foreach ( $mappings as $mapping ) {
            if ( $model->isMember($memberid, $mapping['jsgroup_id']) ) {

                // Add user to jgroup members table
                JUserHelper::addUserToGroup( $memberid, $mapping['jgroup_id'] );

            }
        }
        return true;
    }

    /*
     * Helper function to retrieve sync mappings
     *
     * @return  array
     * @since   1.6
     */
    public function getJomSocialGroupSyncMappings() {
        
        $db = JFactory::getDbo();
        $db->setQuery("SELECT * FROM #__jomsocialgroupsync_rules WHERE state = 1");
        $mappings = $db->loadAssocList($key='id');

        return $mappings;
        
    } //end getJomSocialGroupSyncMappings

}

