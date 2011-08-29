<?php
/**
 * @version     1.0.0
 * @package     com_jomsocialgroupsync
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Lighthouse Consulting and Design
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (task == 'synchronizationrule.cancel' || document.formvalidator.isValid(document.id('synchronizationrule-form'))) {
			Joomla.submitform(task, document.getElementById('synchronizationrule-form'));
		}
		else {
			alert('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_jomsocialgroupsync&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="synchronizationrule-form" class="form-validate">
	<div class="width-60 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_JOMSOCIALGROUPSYNC_LEGEND_SYNCHRONIZATIONRULE'); ?></legend>
			<ul class="adminformlist">

            
			<li><?php echo $this->form->getLabel('id'); ?>
			<?php echo $this->form->getInput('id'); ?></li>

            
			<li><?php echo $this->form->getLabel('jgroup_id'); ?>
			<?php echo $this->form->getInput('jgroup_id'); ?></li>

            
			<li><?php echo $this->form->getLabel('cgroup_id'); ?>
			<?php echo $this->form->getInput('cgroup_id'); ?></li>

            

            <li><?php echo $this->form->getLabel('state'); ?>
                    <?php echo $this->form->getInput('state'); ?></li><li><?php echo $this->form->getLabel('checked_out'); ?>
                    <?php echo $this->form->getInput('checked_out'); ?></li><li><?php echo $this->form->getLabel('checked_out_time'); ?>
                    <?php echo $this->form->getInput('checked_out_time'); ?></li>

            </ul>
		</fieldset>
	</div>


	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
	<div class="clr"></div>
</form>