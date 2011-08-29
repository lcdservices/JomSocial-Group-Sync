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

class JomSocialGroupSyncController extends JController
{
	/**
	 * Method to display a view.
	 *
	 * @param	boolean			$cachable	If true, the view output will be cached
	 * @param	array			$urlparams	An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}.
	 *
	 * @return	JController		This object to support chaining.
	 * @since	1.5
	 */
	public function display($cachable = false, $urlparams = false)
	{
		require_once JPATH_COMPONENT.'/helpers/jomsocialgroupsync.php';

		$view		= JRequest::getCmd('view', 'synchronizationrules');
        JRequest::setVar('view', $view);

		parent::display();

		return $this;
	}
}
