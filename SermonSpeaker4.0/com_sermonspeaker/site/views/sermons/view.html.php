<?php
defined('_JEXEC') or die('Restricted access');

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the SermonSpeaker Component
 */
class SermonspeakerViewSermons extends JView
{
	function display($tpl = null)
	{
		// Applying CSS file
		JHTML::stylesheet('sermonspeaker.css', 'media/com_sermonspeaker/css/');

		$app		= JFactory::getApplication();
		$params		= $app->getParams();

		$columns = $params->get('col');
		if (!$columns){
			$columns = array();
		}

		// Get some data from the models
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$pagination	= $this->get('Pagination');

		// Get the category name(s)
		if($state->get('sermons_category.id') || $state->get('speakers_category.id') || $state->get('series_category.id')){
			$cat	= $this->get('Cat');
		} else {
			$cat 	= '';
		}
		
		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

        // push data into the template
		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('params',		$params);
		$this->assignRef('columns', 	$columns);
		$this->assignRef('pagination',	$pagination);
		$this->assignRef('cat',			$cat);

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app	= JFactory::getApplication();

		// Add javascript for player if needed
		if (in_array('sermons:player', $this->columns) && count($this->items)){
			JHTML::Script('media/com_sermonspeaker/player/jwplayer/jwplayer.js');
			$this->player = SermonspeakerHelperSermonspeaker::insertPlayer($this->items);
			if($this->params->get('fileswitch')){
				$this->document->addScriptDeclaration('
					function Video() {
						jwplayer().load(['.$this->player['video-pl'].']);
					}
				');
				$this->document->addScriptDeclaration('
					function Audio() {
						jwplayer().load(['.$this->player['audio-pl'].']);
					}
				');
			}
		}

		// Set Page Header from menu if active, otherwise a default value
		$menus	= $app->getMenu();
		$menu 	= $menus->getActive();
		if ($menu) {
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('COM_SERMONSPEAKER_SERMONS_TITLE'));
		}

		// Set Pagetitle
		$title 	= $this->params->get('page_title', '');
		if ($title == $app->getCfg('sitename')) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), JText::_('COM_SERMONSPEAKER_SERMONS_TITLE'));
		} elseif ($app->getCfg('sitename_pagetitles', 0)) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		$this->document->setTitle($title);

		// Set MetaData from menu entry if available
		if ($this->params->get('menu-meta_description')){
			$this->document->setMetaData('description', $this->params->get('menu-meta_description'));
		}
		if ($this->params->get('menu-meta_keywords')){
			$this->document->setMetaData('keywords', $this->params->get('menu-meta_keywords'));
		}
	}
}