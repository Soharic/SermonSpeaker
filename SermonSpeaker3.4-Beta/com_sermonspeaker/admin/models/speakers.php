<?php
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

class SermonspeakerModelSpeakers extends JModel
{
	function __construct()
	{
		parent::__construct();

		global $mainframe, $option;

		$this->db				=& JFactory::getDBO();

		$this->filter_state		= $mainframe->getUserStateFromRequest("$option.speakers.filter_state",'filter_state','','word');
		$this->search			= $mainframe->getUserStateFromRequest("$option.speakers.search",'search','','string');
		$this->search			= JString::strtolower($this->search);

		// Get pagination request variables
		$limit = $mainframe->getUserStateFromRequest('global.list.limit', 'limit', $mainframe->getCfg('list_limit'), 'int');
		$limitstart = JRequest::getInt('limitstart', 0);
 		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
 
		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// Get sorting order from Request and UserState
		$this->_order['order']		= $mainframe->getUserStateFromRequest("$option.speakers.filter_order",'filter_order','id','cmd' );
		$this->_order['order_Dir']	= $mainframe->getUserStateFromRequest("$option.speakers.filter_order_Dir",'filter_order_Dir','DESC','word' );
	}

	function _buildWhere()
	{
		$where = NULL;
		if ($this->filter_state) {
			if ($this->filter_state == 'P') {
				$where[] = 'published = 1';
			}
			else if ($this->filter_state == 'U') {
				$where[] = 'published = 0';
			}
		}
		if ($this->search) {
			$where[] = 'LOWER(name) LIKE '.$this->db->Quote('%'.$this->db->getEscaped($this->search, true).'%', false);
		}
		$where = (count($where) ? ' WHERE '.implode(' AND ', $where) : '');

		return $where;
	}
	
	function getTotal()
	{
		$where	= $this->_buildWhere();
		// Query bilden
		$query = 'SELECT *'
		.' FROM #__sermon_speakers'
		.$where
		;
		
		// Query ausf�hren und Eintr�ge z�hlen (einzeiliges Resultat als Integer)
		$total = $this->_getListCount($query);    

        return $total;
	}

	function getSpeakers()
	{
		$where	= $this->_buildWhere();
		$orderby 	= ' ORDER BY '.$this->_order['order'].' '.$this->_order['order_Dir'];
		// Query bilden
        $query = "SELECT * \n"
				."FROM #__sermon_speakers \n"
				.$where
				.$orderby;
		// Query ausf�hren (mehrzeiliges Resulat als Array)
		$rows = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit')); 

        return $rows;
	}

	function getPagination()
	{
        // Load the content if it doesn't already exist
        if (empty($this->_pagination)) {
            jimport('joomla.html.pagination');
            $this->_pagination = new JPagination($this->getTotal(), $this->getState('limitstart'), $this->getState('limit') );
        }
        return $this->_pagination;
	}
}