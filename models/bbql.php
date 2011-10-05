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
		
		global $systemPathToComponent, $httpPathToComponent, $bbqlDb;
		$this->systemPathToComponent = $systemPathToComponent;
		
		$this->leagueId = JRequest::getVar('leagueId');
		
		$this->dbHandle = $bbqlDb;
		
		// include the utilities class
		include_once($httpPathToComponent.DS.'models'.DS.'utilities.php');
	
		$this->utils = new BbqlModelUtilities;
		
		$this->stringsLocalized = $this->utils->getStringsLocalized();
	}
	
	function __destruct() {
		unset($this->dbHandle);
	}
	
	function getMyLeagues() {
		$joomlaUser =& JFactory::getUser();
		
//		$sql = "SELECT DISTINCT L.*, status,
//			(SELECT count(teamHash) FROM Team_Listing WHERE leagueId = L.ID) AS numberOfTeams
//			FROM League L INNER JOIN League_Status LS ON L.StatusId = LS.ID
//
//			 WHERE CommissionerId = '" . $joomlaUser->id . "'";


		$sql = "SELECT DISTINCT L.*, status,
			(SELECT count(teamHash) FROM Team_Listing WHERE leagueId = L.ID) AS numberOfTeams
			FROM League L INNER JOIN League_Status LS ON L.StatusId = LS.ID
			WHERE L.id IN (

			SELECT leagueId FROM team_listing WHERE coachId = '" . $joomlaUser->id . "' UNION SELECT id FROM league WHERE CommissionerId = '" . $joomlaUser->id . "'

			)";
		//$sql = "SELECT L.* FROM League L WHERE CommissionerId = '" . $joomlaUser->id . "'";

		$LeagueQry = $this->dbHandle->query($sql);
		$LeagueList = $LeagueQry->fetchAll();

		//$LeagueList = array();
		return $LeagueList; 
	}
	
	function getOtherLeagues($excludeList, $filterLetter) {
		$joomlaUser =& JFactory::getUser();
		
		$sql = "SELECT DISTINCT L.*, status,"
			. "(SELECT count(teamHash) FROM Team_Listing WHERE leagueId = L.ID) AS numberOfTeams "
			. " FROM League L INNER JOIN League_Status LS ON L.StatusId = LS.ID"
			. " WHERE L.ID NOT IN (" . $excludeList . ") AND L.name LIKE '$filterLetter%'";
		
		$LeagueQry = $this->dbHandle->query($sql);
		
		$LeagueList = $LeagueQry->fetchAll();
		
		return $LeagueList;
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

