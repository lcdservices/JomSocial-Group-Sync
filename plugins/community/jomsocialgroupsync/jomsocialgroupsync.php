<?php
/**
 * @version		2011-07-23 20:07:15$
 * @author		Marek Handze
 * @package		JomSocial Group Sync
 * @copyright	Copyright (C) 2011. All rights reserved.
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

        // Get sync mappings
        $mappings = self::getJomSocialGroupSyncMappings();
        if ( empty($mappings) ) {
            return;
        }

        // Instantiate JomSocial
        require_once JPATH_ROOT.'/'.'administrator/components/com_community/defines.php';
        require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php' );

        jimport('joomla.user.helper1');
        $model =  CFactory::getModel( 'Groups' );

        foreach ( $mappings as $mapping ) {
            if ( $model->isMember($member, $mapping['jsgroup_id']) ) {

                // Add user to jgroup members table
                JUserHelper::addUserToGroup( $userid, $mapping['jgroup_id'] );

            } else {
                JUserHelper::removeUserFromGroup( $userid, $mapping['jgroup_id'] );
            }
        }
        return true;
    } //end jomsocial_post

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

