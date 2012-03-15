<?php
/**
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * @package		SermonSpeaker
 */
// Based on com_contact
class SermonspeakerModelArchive extends JModelList
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'sermon_number', 'sermons.sermon_number',
				'sermon_title', 'sermons.sermon_title',
				'scripture', 'sermons.scripture',
				'sermon_date', 'sermons.sermon_date',
				'sermon_time', 'sermons.sermon_time',
				'addfileDesc', 'sermons.addfileDesc',
				'hits', 'sermons.hits',
				'ordering', 'sermons.ordering',
				'name', 'speakers.name',
				'series_title', 'series.series_title',
			);
		}

		parent::__construct($config);
	}

	protected function getListQuery()
	{
		$user	= JFactory::getUser();
		$groups	= implode(',', $user->authorisedLevels());

		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'sermons.id, sermons.sermon_title, sermons.catid, sermons.audiofile, sermons.videofile, ' .
				'CASE WHEN CHAR_LENGTH(sermons.alias) THEN CONCAT_WS(\':\', sermons.id, sermons.alias) ELSE sermons.id END as slug,' .
				'sermons.picture, sermons.hits, sermons.notes,' .
				'sermons.sermon_date, sermons.alias, sermons.sermon_time,' .
				'sermons.state, sermons.ordering, sermons.podcast,' .
				'sermons.sermon_number, sermons.addfile, sermons.addfileDesc,' .
				'sermons.created, sermons.created_by'
			)
		);
		$query->from('`#__sermon_sermons` AS sermons');

		// Join over the scriptures.
		$query->select('GROUP_CONCAT(script.book,"|",script.cap1,"|",script.vers1,"|",script.cap2,"|",script.vers2,"|",script.text ORDER BY script.ordering ASC SEPARATOR "!") AS scripture');
		$query->join('LEFT', '#__sermon_scriptures AS script ON script.sermon_id = sermons.id');
		$query->group('sermons.id');

		// Join over Speaker
		$query->select(
			'speakers.name AS name, speakers.pic AS pic, speakers.state as speaker_state, ' .
			'CASE WHEN CHAR_LENGTH(speakers.alias) THEN CONCAT_WS(\':\', speakers.id, speakers.alias) ELSE speakers.id END as speaker_slug'
		);
		$query->join('LEFT', '#__sermon_speakers AS speakers ON speakers.id = sermons.speaker_id');

		// Join over Series
		$query->select(
			'series.series_title AS series_title, series.state as series_state, ' .
			'CASE WHEN CHAR_LENGTH(series.alias) THEN CONCAT_WS(\':\', series.id, series.alias) ELSE series.id END as series_slug'
		);
		$query->join('LEFT', '#__sermon_series AS series ON series.id = sermons.series_id');

		// Filter by search in title or scripture (with ref:)
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'ref:') === 0) {
				$search = $db->Quote('%'.$db->getEscaped(substr($search, 4), true).'%');
				$query->where('(scripture LIKE '.$search.')');
			} else {
				$search = $db->Quote('%'.$db->getEscaped($search, true).'%');
				$query->where('(sermons.sermon_title LIKE '.$search.')');
			}
		}

		// Join over Sermons Category.
		$query->join('LEFT', '#__categories AS c_sermons ON c_sermons.id = sermons.catid');
		if ($categoryId = $this->getState('sermons_category.id')) {
			$query->where('sermons.catid = '.(int) $categoryId);
		}
		$query->where('(sermons.catid = 0 OR (c_sermons.access IN ('.$groups.') AND c_sermons.published = 1))');

		// Join over Speakers Category.
		$query->join('LEFT', '#__categories AS c_speaker ON c_speaker.id = speakers.catid');
		if ($categoryId = $this->getState('speakers_category.id')) {
			$query->where('speakers.catid = '.(int) $categoryId);
		}
		$query->where('(sermons.speaker_id = 0 OR speakers.catid = 0 OR (c_speaker.access IN ('.$groups.') AND c_speaker.published = 1))');

		// Join over Series Category.
		$query->join('LEFT', '#__categories AS c_series ON c_series.id = series.catid');
		if ($categoryId = $this->getState('series_category.id')) {
			$query->where('series.catid = '.(int) $categoryId);
		}
		$query->where('(sermons.series_id = 0 OR series.catid = 0 OR (c_series.access IN ('.$groups.') AND c_series.published = 1))');

		// Filter by date
		$year = $this->getState('date.year');
		$query->where('YEAR(sermons.sermon_date) = '.(int) $year);
		$month = $this->getState('date.month');
		if ($month){
			$query->where('MONTH(sermons.sermon_date) = '.(int) $month);
		}

		// Join over users for the author names.
		$query->select("user.name AS author");
		$query->join('LEFT', '#__users AS user ON user.id = sermons.created_by');

		// Filter by state
		$state = $this->getState('filter.state');
		if (is_numeric($state)) {
			$query->where('sermons.state = '.(int) $state);
		}

		// Add the list ordering clause.
		$query->order($db->getEscaped($this->getState('list.ordering', 'ordering')).' '.$db->getEscaped($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState()
	{
		$app	= JFactory::getApplication();
		$params	= $app->getParams();
		$this->setState('params', $params);

		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter-search', '', 'STRING');
		$this->setState('filter.search', $search);

		$id = (int)$params->get('sermon_cat', 0);
		if (!$id){ $id = JRequest::getInt('sermon_cat', 0); }
		$this->setState('sermons_category.id', $id);

		$id = (int)$params->get('speaker_cat', 0);
		if (!$id){ $id = JRequest::getInt('speaker_cat', 0); }
		$this->setState('speakers_category.id', $id);

		$id = (int)$params->get('series_cat', 0);
		if (!$id){ $id = JRequest::getInt('series_cat', 0); }
		$this->setState('series_category.id', $id);

		$date	= getDate();
		$year	= $app->getUserStateFromRequest($this->context.'.date.year', 'year', 0, 'INT');
		$month	= $app->getUserStateFromRequest($this->context.'.date.month', 'month', 0, 'INT');
		if ($year || $month){
			if (!$year){
				$year	= $date['year'];
				$app->setUserState($this->context.'.date.year', $year);
			}
		} else {
			$year	= $params->get('year', $date['year']);
			$month	= $params->get('month', $date['mon']);
			$app->setUserState($this->context.'.date.year', $year);
			$app->setUserState($this->context.'.date.month', $month);
		}
		$this->setState('date.year', $year);
		$this->setState('date.month', $month);

		$this->setState('filter.state',	1);

		$order	= $params->get('default_order', 'ordering');
		$dir	= $params->get('default_order_dir', 'ASC');
		parent::populateState($order, $dir);
	}

	/**
	 * Method to get the name of the category.
	 *
	 * @since	1.6
	 */
	public function getCat()
	{
		$cat_arr = array();
		if($categoryId = $this->getState('sermons_category.id')){
			$cat_arr[] = $categoryId;
		}
		if($categoryId = $this->getState('series_category.id')){
			$cat_arr[] = $categoryId;
		}
		if($categoryId = $this->getState('speakers_category.id')){
			$cat_arr[] = $categoryId;
		}
		$cat_arr 	= array_unique($cat_arr);
		$db		= $this->getDbo();
		$title = array();
		foreach ($cat_arr as $cat){
			$query = "SELECT title FROM #__categories WHERE id = ".$cat;
			$db->setQuery( $query );
			$title[] = $db->LoadResult();
		}
		$title = implode(' &amp; ', $title);
		return $title;
	}

	/**
	 * Method to get the available Months.
	 *
	 * @since	1.6
	 */
	public function getMonths()
	{
		$months	= array(
			1 => 'JANUARY',
			2 => 'FEBRUARY',
			3 => 'MARCH',
			4 => 'APRIL',
			5 => 'MAY',
			6 => 'JUNE',
			7 => 'JULY',
			8 => 'AUGUST',
			9 => 'SEPTEMBER',
			10 => 'OCTOBER',
			11 => 'NOVEMBER',
			12 => 'DECEMBER',
		);

		$db	= $this->getDbo();
		$query = "SELECT DISTINCT MONTH(sermon_date) AS `value` \n"
				."FROM #__sermon_sermons \n"
				."ORDER BY `value` ASC";
		$db->setQuery($query);
		$options = $db->loadAssocList();

		foreach($options as &$option){
			$option['text']	= $months[$option['value']];
		}

		return $options;
	}

	/**
	 * Method to get the available Years.
	 *
	 * @since	1.6
	 */
	public function getYears()
	{
		$db	= $this->getDbo();
		$query = "SELECT DISTINCT YEAR(sermon_date) AS year \n"
				."FROM #__sermon_sermons \n"
				."ORDER BY `year` ASC";
		$db->setQuery($query);
		$options = $db->loadAssocList();

		return $options;
	}
}