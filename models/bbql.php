<?php
/**
 * BBQL Model for BBQL Component
 * @license    GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );
 
/**
 * BBQL Model
 */
class BbqlModelBbql extends JModel
{
	
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		
		global $systemPathToComponent, $httpPathToComponent;
		$this->systemPathToComponent = $systemPathToComponent;
		
		$this->leagueId = JRequest::getVar('leagueId');
		
		$this->joomlaDb = JFactory::getDBO();
		
		// include the utilities class
		include_once($httpPathToComponent.DS.'models'.DS.'utilities.php');
	
		$this->utils = new BbqlModelUtilities;
		
		$this->stringsLocalized = $this->utils->getStringsLocalized();
	}
	
	function __destruct() {
		unset($this->joomlaDb);
	}
	
	function getMyLeagues() {
		$joomlaUser =& JFactory::getUser();
		$sql = "
			SELECT leagueId FROM #__bbla_Team_Listing 
			WHERE coachId = '" . $joomlaUser->id . "' 
			UNION 
			SELECT id FROM #__bbla_League 
			WHERE CommissionerId = '" . $joomlaUser->id . "'";
		
		$this->joomlaDb->setQuery($sql);
		$leagueIds = implode(",", $this->joomlaDb->loadResultArray());
		
		$sql = "SELECT DISTINCT L.*, Status,
			(SELECT count(teamHash) FROM #__bbla_Team_Listing WHERE leagueId = L.ID) AS numberOfTeams
			FROM #__bbla_League L INNER JOIN #__bbla_League_Status LS ON L.StatusId = LS.ID
			WHERE L.id IN (".$leagueIds.")";

		$this->joomlaDb->setQuery($sql);
		return $this->joomlaDb->loadAssocList();
	}
	
	function getOtherLeagues($excludeList, $filterLetter) {
		$joomlaUser =& JFactory::getUser();

		$sql = "SELECT DISTINCT L.*, Status,"
			. "(SELECT count(teamHash) FROM #__bbla_Team_Listing WHERE leagueId = L.ID) AS numberOfTeams "
			. " FROM #__bbla_League L INNER JOIN #__bbla_League_Status LS ON L.StatusId = LS.ID"
			. " WHERE L.ID NOT IN (" . $excludeList . ") AND L.name LIKE '$filterLetter%'";
		
		$this->joomlaDb->setQuery($sql);
		return $this->joomlaDb->loadAssocList();
	}
	
	function getLeagues($filterLetter = "") {
		$leagueArray = array();
		$leagueArray['my'] = $this->getMyLeagues();

		if ($filterLetter != "") {
			$excludeList = "";
			foreach ($this->getMyLeagues() as $key => $value) {
				$excludeList = $excludeList . $value['ID'] . ",";
			}
			//remove trailing comma
			$excludeList = substr($excludeList, 0, -1);
			$leagueArray['others'] = $this->getOtherLeagues($excludeList, $filterLetter);
		} else
			$leagueArray['others'] = array();
		
		return $leagueArray;
	}
}

