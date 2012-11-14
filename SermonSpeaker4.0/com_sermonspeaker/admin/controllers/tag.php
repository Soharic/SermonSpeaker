<?php
// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Tag controller class.
 *
 * @package		SermonSpeaker.Administrator
 */
class SermonspeakerControllerTag extends JControllerForm
{
	/**
	 * Method override to check if you can add a new record.
	 *
	 * @param	array	$data	An array of input data.
	 * @return	boolean
	 */
	protected function allowAdd($data = array())
	{
		$user		= JFactory::getUser();
		$categoryId	= JArrayHelper::getValue($data, 'catid', $this->input->getInt('filter_category_id'), 'int');
		$allow = null;

		if ($categoryId)
		{
			// If the category has been passed in the data or URL check it.
			$allow = $user->authorise('core.create', $this->option.'.category.'.$categoryId);
		}

		if ($allow === null)
		{
			// In the absense of better information, revert to the component permissions.
			return parent::allowAdd($data);
		}
		else
		{
			return $allow;
		}
	}

	/**
	 * Method to check if you can add a new record.
	 *
	 * @param	array	$data	An array of input data.
	 * @param	string	$key	The name of the key for the primary key.
	 *
	 * @return	boolean
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$categoryId = 0;

		if ($recordId)
		{
			// Need to do a lookup from the model.
			$record = $this->getModel()->getItem($recordId);
			$categoryId = (int) $record->catid;
		}

		if ($categoryId)
		{
			$user = JFactory::getUser();
			// The category has been set. Check the category permissions.
			if ($user->authorise('core.edit', $this->option.'.category.'.$categoryId))
			{
				return true;
			}
			// Fallback on edit.own.
			if ($user->authorise('core.edit.own', $this->option.'.category.'.$categoryId))
			{
				return ($record->created_by == $user->get('id'));
			}
		}
		else
		{
			// Since there is no asset tracking, revert to the component permissions.
			return parent::allowEdit($data, $key);
		}
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $model  The model.
	 *
	 * @return  boolean	 True if successful, false otherwise and internal error is set.
	 *
	 * @since   1.7
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Tag', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_sermonspeaker&view=tags'.$this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

	protected function getRedirectToItemAppend($recordId = null, $urlVar = null)
	{
		$append = parent::getRedirectToItemAppend($recordId, $urlVar);
		$modal	= JFactory::getApplication()->input->get('modal', 0, 'int');
		$return	= $this->getReturnPage();

		if ($modal) {
			$append .= '&tmpl=component';
		}

		if ($return) {
			$append .= '&return='.base64_encode($return);
		}

		return $append;
	}

	/**
	 * Get the return URL.
	 *
	 * If a "return" variable has been passed in the request
	 *
	 * @return	string	The return URL.
	 * @since	1.6
	 */
	protected function getReturnPage()
	{
		$return = JFactory::getApplication()->input->get('return', '', 'base64');

		if (empty($return) || !JUri::isInternal(base64_decode($return))) {
			return false;
		}
		else {
			return base64_decode($return);
		}
	}

	/**
	 * Method to save a record.
	 *
	 * @param	string	$key	The name of the primary key of the URL variable.
	 * @param	string	$urlVar	The name of the URL variable if different from the primary key (sometimes required to avoid router collisions).
	 *
	 * @return	Boolean	True if successful, false otherwise.
	 * @since	1.6
	 */
	public function save($key = null, $urlVar = 'id')
	{
		$result = parent::save($key, $urlVar);

		// If ok, redirect to the return page.
		if ($result && ($return = $this->getReturnPage())) {
			$this->setRedirect($return);
		}

		return $result;
	}
}