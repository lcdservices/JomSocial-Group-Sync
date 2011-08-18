<?php
/**
 * @version		2011-07-23 20:07:15$
 * @author		Brian Shaughnessy
 * @package		CiviCRM Group Sync
 * @copyright	Copyright (C) 2011. All rights reserved.
 * @license		GNU GPL
 */

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

class  plgSystemCiviGroupSync extends JPlugin
{
	
	/*
     * Joomla -> CiviCRM
     * Update CiviCRM groups on Joomla user save 
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
    
        $app = JFactory::getApplication();
        
        // Instantiate CiviCRM
        require_once JPATH_ROOT.'/'.'administrator/components/com_civicrm/civicrm.settings.php';
        require_once 'CRM/Core/Config.php';
        require_once 'api/api.php';
        $civiConfig =& CRM_Core_Config::singleton( );
        
        // Get sync mappings
        $mappings = self::getCiviGroupSyncMappings();
        if ( empty($mappings) ) {
            return;
        }
        
        // Retrieve Joomla User ID and CiviCRM Contact ID
        $juserid = $user['id'];
        $cuser   = civicrm_api( "UFMatch",
                                "get", 
                                array ('version' => '3', 
                                       'uf_id'   => $juserid)
                               );
        $cuserid = $cuser['values'][$cuser['id']]['contact_id'];
        
        //TODO: if contact does not exist, we should probably create it here
        
        // Cycle through mappings and add to/remove from CiviCRM groups
        foreach ( $mappings as $mapping ) {
            if ( in_array($mapping['jgroup_id'], $user['groups']) ) {
                //echo 'jgroup_id '.$mapping['jgroup_id'].'</br>';
                //echo 'cgroup_id '.$mapping['cgroup_id'].'</br>';
                civicrm_api( "GroupContact",
                             "create", 
                             array ('version'    => '3', 
                                    'group_id'   => $mapping['cgroup_id'], 
                                    'contact_id' => $cuserid)
                            );
            } else {
                civicrm_api( "GroupContact",
                             "delete", 
                             array ('version'    => '3', 
                                    'group_id'   => $mapping['cgroup_id'], 
                                    'contact_id' => $cuserid)
                            );
            }
        }
        
        return;
        
    } //end onUserAfterSave
    
    //NOTE: If a user is deleted, we don't alter the contact record
    //NOTE: If a JGroup or CGroup is deleted, we don't remove from the linked group
    
    /*
     * CiviCRM -> Joomla
     * Update Joomla groups on CiviCRM group-contact add 
     * Method is called after group contact subscription is stored in the database
     *
     * @param   string    $op           Operation performed
     * @param   string    $objectName   Name of object
     * @param   int       $objectId     Unique identifier (group)
     * @param   object    &$objectRef   Object reference (contact)
     *
     * @return  void
     * @since   1.6
     */
    public function civicrm_post( $op, $objectName, $objectId, &$objectRef ) {
            
        if ( $objectName != 'GroupContact' ) {
            return;
        }
        
        // Get sync mappings
        $mappings = self::getCiviGroupSyncMappings();
        if ( empty($mappings) ) {
            return;
        }
        
        // Instantiate CiviCRM
        require_once 'CRM/Core/Config.php';
        require_once 'api/api.php';
        $civiConfig =& CRM_Core_Config::singleton( );
        
        // Get IDs
        $gid     = $objectId;
        $cid     = $objectRef[0];
        $juser   = civicrm_api( "UFMatch",
                                "get", 
                                array ('version'    => '3', 
                                       'contact_id' => $cid)
                               );
        //if we can't match with a Joomla user, exit
        if ( $juser['count'] == 0 ) {
            return;
        }
        $juserid = $juser['values'][$juser['id']]['uf_id'];
        
        // Cycle through mappings and locate jgroup_id
        $jgroup_ids = array();
        foreach ( $mappings as $mapping ) {
            if ( $mapping['cgroup_id'] == $gid ){
                $jgroup_ids[] = $mapping['jgroup_id'];
            }
        }
        
        // Return if there is no mapped Joomla group
        if ( empty($jgroup_ids) ) {
            return;
        }
        
        jimport('joomla.user.helper');

        switch ( $op ) {
            case 'create':
            case 'edit':
                //add to Joomla group
                foreach ( $jgroup_ids as $jgroup_id ) {
                    JUserHelper::addUserToGroup( $juserid, $jgroup_id );
                }
                break;
            
            case 'delete':
                //remove from Joomla group
                //first check to make sure contact has no other C groups associated with this J group
                foreach ( $jgroup_ids as $jgroup_id ) {
                    if ( self::countCiviJoomlaGroups($mappings, $jgroup_id, $gid, $cid) > 1 ) {
                        break;
                    } else {
                        JUserHelper::removeUserFromGroup( $juserid, $jgroup_id );
                    }
                }
                break;
            
            default:
                break;
        }
        
    } //end civicrm_post
    
    
    /*
     * CiviCRM <-> Joomla
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
        
        // Instantiate CiviCRM
        require_once JPATH_ROOT.'/'.'administrator/components/com_civicrm/civicrm.settings.php';
        require_once 'CRM/Core/Config.php';
        require_once 'CRM/Contact/BAO/Group.php';
        require_once 'api/api.php';
        $civiConfig =& CRM_Core_Config::singleton( );
        
        jimport( 'joomla.user.helper' );
        jimport( 'joomla.access.access' );
        
        $ruleID    = $article->id;
        $ruleState = $article->state;
        $jgroup_id = $article->jgroup_id;
        $cgroup_id = $article->cgroup_id;

        //if the sync rule is disabled, take no action and exit
        if ( !$ruleState ) {
            return;
        }

        //update Joomla groups
        $cGroupContacts = CRM_Contact_BAO_Group::getGroupContacts($cgroup_id);
        foreach ( $cGroupContacts as $cGroupContact ) {
            
            $cid     = $cGroupContact['contact_id'];
            $juser   = civicrm_api( "UFMatch",
                                    "get", 
                                    array ('version'    => '3', 
                                           'contact_id' => $cid)
                                   );
            
            //if we can't match with a Joomla user, move to next record
            if ( $juser['count'] == 0 ) {
                continue;
            }
            
            $juserid = $juser['values'][$juser['id']]['uf_id'];

            //add to Joomla group
            JUserHelper::addUserToGroup( $juserid, $jgroup_id );
        }
        
        //update CiviCRM groups
        $jGroupContacts = JAccess::getUsersByGroup($jgroup_id);
        foreach ( $jGroupContacts as $juserid ) {
            
            $cuser   = civicrm_api( "UFMatch",
                                    "get", 
                                    array ('version' => '3', 
                                           'uf_id'   => $juserid)
                                   );
                                   
            //if we can't match with a CiviCRM user, move to next record
            if ( $cuser['count'] == 0 ) {
                continue;
            }
            
            $cuserid = $cuser['values'][$cuser['id']]['contact_id'];
            
            //add to CiviCRM group
            civicrm_api( "GroupContact",
                         "create", 
                         array ('version'    => '3', 
                                'group_id'   => $cgroup_id, 
                                'contact_id' => $cuserid)
                        );
            
        }
        
    } //end onContentAfterSave
    
    /*
     * Helper function to retrieve sync mappings
     *
     * @return  array
     * @since   1.6
     */
    public function getCiviGroupSyncMappings() {
        
        $db = JFactory::getDbo();
        $db->setQuery("SELECT * FROM #__civigroupsync_rules WHERE state = 1");
        $mappings = $db->loadAssocList($key='id');

        return $mappings;
        
    } //end getCiviGroupSyncMappings
    
    /*
     * Helper function to check if the user has multiple valid mappings to a Joomla group
     *
     * @return  count of contact-group memberships mapped to passed joomla group
     * @since   1.6
     */
    public function countCiviJoomlaGroups($mappings, $jgroup_id, $gid, $cid) {
        
        //start count at 1 for group we are removing
        $countCiviGroups = 1;
        
        //get all cgroup_ids for passed jgroup_id
        $civiGroups = array();
        foreach ( $mappings as $mapping ) {
            if ( $mapping['jgroup_id'] == $jgroup_id ) {
                $civiGroups[] = $mapping['cgroup_id'];
            }
        }
        
        //if civiGroups count is < 2, we can exit immediately
        if ( count($civiGroups) < 2 ) {
            return $countCiviGroups;
        }
        
        //get contacts group memberships
        $contactGroups = civicrm_api( "GroupContact",
                                      "get", 
                                      array ('version'    => '3', 
                                             'contact_id' => $cid)
                                     );
        
        //now cycle through our list of multiple civiGroups and determine if contact is member of others
        foreach ( $civiGroups as $civiGroup ) {
            //skip the Civi group ID we are working with
            if ( $civiGroup == $gid ) {
                continue;
            }
            foreach ( $contactGroups['values'] as $contactGroup ) {
                if ( $contactGroup['group_id'] == $civiGroup ) {
                    $countCiviGroups++;
                }
            }
        }
                
        return $countCiviGroups;
        
    } //end countCiviJoomlaGroups

}
