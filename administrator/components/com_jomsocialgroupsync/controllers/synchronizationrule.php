<?php
/**
 * @version     1.0.0
 * @package     com_jomsocialgroupsync
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by com_combuilder - http://www.notwebdesign.com
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Synchronizationrule controller class.
 */
class JomSocialGroupSyncControllerSynchronizationrule extends JControllerForm
{

    function __construct() {
        $this->view_list = 'synchronizationrules';
        parent::__construct();
    }

}