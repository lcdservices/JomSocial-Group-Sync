<?php
/**
 * @version		2011-07-23 20:07:15$
 * @author		Marek Handze
 * @package		JomSocial Group Sync
 * @copyright	Copyright (C) 2011. All rights reserved.
 * @license		GNU GPL
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );
require_once( JPATH_ROOT.'/components/com_community/libraries/core.php');

class  plgSystemJomSocialGroupSync extends JPlugin
{
	
	/*
     * Joomla -> JomSocial
     * Update JomSocial groups on Joomla user save 
     * Method is called after user data is stored in the database
     *
     * @param   array       $user       Holds the new user data.
     * @param   boolean     $isnew      True if a new user is stored.
     * @param   boolean     $success    True if user was succesfully stored in the database.
     * @param   string      $msg        Message.
     *
     * @return  void
     * @since   1.6
     * @throws  Exception on error.
     */
    function onUserAfterSave( $user, $isnew, $success, $msg ) {

        //if 'latitude' key exists, event triggered from within JomSocial 
        if ( array_key_exists('latitude', $user) ) {
            return;
        }

        $app = JFactory::getApplication();

        // Instantiate JomSocial
        require_once JPATH_ROOT.'/'.'administrator/components/com_community/defines.php';

        // Get sync mappings
        $mappings = self::getJomSocialGroupSyncMappings();
        if ( empty($mappings) ) {
            return;
        }

        // create JomSocial objects needed to manage group members
        $group =& JTable::getInstance( 'Group' , 'CTable' );
        $model =  CFactory::getModel( 'Groups' );

        // create 
        $data              = new stdClass();
        $data->memberid    = $user['id'];
        $data->approved    = 1;
        $data->permissions = 0;

        // Cycle through mappings and add to/remove from JomSocial groups
        foreach ( $mappings as $mapping ) {

            $data->groupid = $mapping['jsgroup_id'];

            if ( in_array($mapping['jgroup_id'], $user['groups']) ) {

                // Add user to group members table
                if (!$model->isMember($data->memberid, $data->groupid)) {
                    $addResult = $group->addMember( $data );
                }

            } else {
                $model->removeMember( $data );
            }
        }

        return;

    } //end onUserAfterSave

    /*
     * JomSocial <-> Joomla
     * Run rules when mapping is created/edited or enabled
     * Note: we don't need to update users/contacts when a JGroup or CGroup
     * is created, as the group must precede the mapping record.
     * 
     * Note: we don't modify users/contacts if a sync rule is removed or disabled
     * 
     * Method is called right after the content is saved
     *
     * @param   string      The context of the content passed to the plugin (added in 1.6)
     * @param   object      A JTableContent object
     * @param   bool        If the content is just about to be created
     * @since   1.6
     */

     public function onContentAfterSave($context, &$article, $isNew) {

        $ruleID = $article->id;
        $ruleState = $article->state;
        $jgroup_id = $article->jgroup_id;
        $jsgroup_id = $article->jsgroup_id;

        //if the sync rule is disabled, take no action and exit
        if ( !$ruleState ) {
            return true;
        }
        
        //if we are not in the right context, exit
        if ( !in_array( $context, array('com_jomsocialgroupsync.synchronizationrule', 'com_jomsocialgroupsync.synchronizationrules') ) ) {
            return true;
        }

        //include Joomla files
        jimport( 'joomla.user.helper' );
        jimport( 'joomla.access.access' );

        // Instantiate JomSocial
        require_once JPATH_ROOT.'/'.'administrator/components/com_community/defines.php';
        require_once( JPATH_ROOT . DS . 'components' . DS . 'com_community' . DS . 'libraries' . DS . 'core.php' );

        //update Joomla groups
        $model =  CFactory::getModel( 'Groups' );
        $members = $model->getMembers($jsgroup_id);

        foreach ( $members as $member ) {
            //add to Joomla group
            JUserHelper::addUserToGroup( $member->id, $jgroup_id );
        }

        // update JomSocial groups
        $group =& JTable::getInstance( 'Group' , 'CTable' );

        $data   = new stdClass();
        $data->approved     = 1;
        $data->permissions  = 0;
        $data->groupid = $jsgroup_id;
        $jGroupUsers = JAccess::getUsersByGroup($jgroup_id);

        foreach ( $jGroupUsers as $userid ) {
           //add to JomSocial group
           $data->memberid     = $userid;
           if (!$model->isMember($data->memberid, $data->groupid)) {
               $group->addMember( $data );
           }

        }
        
        return true;
        

    } //end onContentAfterSave

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

