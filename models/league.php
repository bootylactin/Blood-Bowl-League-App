<?php
//Upload Model for BBQL Component
//@license    GNU/GPL

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );
 
//Upload Model

class BbqlModelLeague extends JModel
{
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		
		global $systemPathToComponent, $httpPathToComponent, $bbqlDb;
		$this->systemPathToComponent = $systemPathToComponent;
		$this->httpPathToComponent = $httpPathToComponent;
		
		$this->leagueId = JRequest::getVar('leagueId');
		
		//$this->dbHandle = $bbqlDb;
		
		$this->joomlaDb = JFactory::getDBO();
		
		// include the utilities class
		include_once($httpPathToComponent.DS.'models'.DS.'utilities.php');
		include_once($httpPathToComponent.DS.'models'.DS.'timezones.php');
	
		$this->utils = new BbqlModelUtilities;
		$this->timezones = new BbqlModelTimezones;
		
		$this->stringsLocalized = $this->utils->getStringsLocalized();
		
		//retrieve coach IDs
		$sql = "SELECT coachId from #__bbla_Team_Listing WHERE leagueId = ".$this->leagueId;
		$this->joomlaDb->setQuery($sql);
		
		$coaches = $this->joomlaDb->loadResultArray();
		
		if ($coaches) {
			//set times for coaches based on selected timezone and DST settings
			$this->coachesTimeZones = array();
			foreach ($coaches as $id) {
				$user=& JUser::getInstance($id);
				$timezone = $user->getParam('timezone');

				$dst = $user->getParam('dst');
				//are we currently in DST?  If so, and the user observes DST, add one hour
				if (date('I') == 1 && $user->getParam('dst') == 1) {
					$dst = 1; //add an hour
				} else {
					$dst = 0;
				}
				
				$this->coachesTimeZones[$id] = time() + ($timezone + $dst)*3600 - date('Z');
			}
		}
	}
	
	function __destruct() {
		unset($this->joomlaDb);
		unset($this->dbHandle);
	}
	
	//handle the receiving of the submitted file in this method
	function createLeague() {
		//insert team info
		$sql = "INSERT INTO #__bbla_League (Name,Password,CommissionerId,StatusId,MultipleTeams,FullControl) " .
				"VALUES(".
					$this->joomlaDb->quote($_POST['name']).",".
					$this->joomlaDb->quote($_POST['password']).",".
					$this->joomlaDb->quote($_POST['CommissionerId']).",".
					"1,".
					$this->joomlaDb->quote($_POST['multipleTeams']).",".
					$this->joomlaDb->quote($_POST['fullControl']).
				")";

		
		$this->joomlaDb->setQuery(stripSlashes($sql));
		$test = $this->joomlaDb->query();

		echo 'Your file upload was successful!'; // It worked.
	}
	
	function getLeagueById() {
		$sql = "SELECT L.*, Status 
			FROM #__bbla_League L INNER JOIN #__bbla_League_Status LS ON L.StatusId = LS.ID
			WHERE L.ID = '" . $this->leagueId . "'";
		
		$this->joomlaDb->setQuery($sql);
		return $this->joomlaDb->loadAssocList();
	}
	
	function getTeams() {
		// get team information from database for a particular league
		$sql = "SELECT tl.*, sl.English as Race
			FROM #__bbla_Team_Listing tl INNER JOIN #__bbla_Races r ON tl.idRaces = r.ID
			INNER JOIN #__bbla_Strings_Localized sl ON r.idStrings_Localized = sl.ID
			WHERE leagueId = '" . $this->leagueId . "'";

		$this->joomlaDb->setQuery($sql);
		return $this->joomlaDb->loadAssocList();
	}
    
	function getStandings() {
		// get team information from database for a particular league
		$sql = "SELECT sst.*, tl.*, sl.English as Race
			FROM #__bbla_Statistics_Season_Teams sst INNER JOIN #__bbla_Team_Listing tl ON sst.teamHash = tl.teamHash
			INNER JOIN #__bbla_Races r ON tl.idRaces = r.ID
			INNER JOIN #__bbla_Strings_Localized sl ON r.idStrings_Localized = sl.ID
			WHERE sst.leagueId = '" . $this->leagueId . "'
			ORDER BY iPoints DESC, iMatchPlayed, iWins DESC, touchdownDif DESC, Inflicted_iTouchdowns DESC";
		
		$this->joomlaDb->setQuery($sql);
		return $this->joomlaDb->loadAssocList();
	}
	
	function getSchedule() {
		// get team information from database for a particular league
		$sql = "SELECT C.*, (SELECT strName FROM #__bbla_Team_Listing TL WHERE TL.teamHash = C.teamHash_Away) AS AwayTeam,
			(SELECT strName FROM #__bbla_Team_Listing TL WHERE TL.teamHash = C.teamHash_Home) AS HomeTeam,
			(SELECT coachId FROM #__bbla_Team_Listing TL WHERE TL.teamHash = C.teamHash_Away) AS AwayCoachId,
			(SELECT coachId FROM #__bbla_Team_Listing TL WHERE TL.teamHash = C.teamHash_Home) AS HomeCoachId
			FROM #__bbla_Calendar C WHERE leagueId = '" . $this->leagueId . "'
			ORDER BY Championship_iDay, bPlayed DESC, ID";
		
		$this->joomlaDb->setQuery($sql);
		//$this->utils->do_dump($this->joomlaDb->loadAssocList());
		return $this->joomlaDb->loadAssocList();
	}
	
	//TODO: convert to joomlaDb
	function getLeagueLeaders() {
		$returnStruct = array();
		
		$sqlBase = "SELECT tl.strName as TeamName, pl.strName as Name, pl.iNumber, stl.English as Position, ssp.* FROM Statistics_Season_Players ssp INNER JOIN Player_Listing pl ON ssp.playerHash = pl.playerHash " .
			"INNER JOIN Team_Listing TL ON pl.teamHash = tl.teamHash INNER JOIN Player_Types pt ON pl.idPlayer_Types = pt.ID INNER JOIN Strings_Localized stl ON pt.idStrings_Localized = stl.ID " .
			"WHERE tl.leagueId = '" . $this->leagueId . "'";
			
		$sqlTD = $sqlBase." AND Inflicted_iTouchdowns > 0 ORDER BY Inflicted_iTouchdowns DESC Limit 10";
		$returnStruct['mostTDs'] = $this->dbHandle->query($sqlTD)->fetchAll();
		
		$sqlCAS = $sqlBase." AND (Inflicted_iCasualties > 0 OR Inflicted_iDead > 0) " .
			"ORDER BY Inflicted_iCasualties DESC, Inflicted_iDead DESC, Inflicted_iKO DESC, Inflicted_iStuns DESC Limit 10";
		$returnStruct['mostCAS'] = $this->dbHandle->query($sqlCAS)->fetchAll();
		
		$sqlCOMP = $sqlBase." AND Inflicted_iPasses > 0 ORDER BY Inflicted_iPasses DESC, Inflicted_iMetersPassing DESC Limit 10";
		$returnStruct['mostCOMP'] = $this->dbHandle->query($sqlCOMP)->fetchAll();
		
		$sqlINT = $sqlBase." AND Inflicted_iInterceptions > 0 ORDER BY Inflicted_iInterceptions DESC Limit 10";
		$returnStruct['mostINT'] = $this->dbHandle->query($sqlINT)->fetchAll();
		
		$sqlRUSH = $sqlBase." AND Inflicted_iMetersRunning > 0 ORDER BY Inflicted_iMetersRunning DESC Limit 10";
		$returnStruct['mostRUSH'] = $this->dbHandle->query($sqlRUSH)->fetchAll();
		
		
		return $returnStruct;
	}
	
	//TODO: show match statistics on match report view
	function getMatchReport($matchId) {
		/*
		//connect to the match report file
		$matchPath = $this->httpPathToComponent.DS.'uploads'.DS.$this->leagueId.DS.$matchId.'.sqlite';
		
		//now access the DB to determine the teamIDs
		$db = 'sqlite:'.$matchPath;
		
		// create a connection to SQLite3 database file with PDO and return a database handle
		try{
			$matchResultHandle = new PDO($db);
		}catch( PDOException $exception ){
			die($exception->getMessage());
		}
			
		//retrieve team statistics from match report
		$sql = "SELECT * FROM Calendar";
		$matchStats = $matchResultHandle->query($sql)->fetch();
		$awayTeamScore = $matchStats['Away_iScore'];
		$homeTeamScore = $matchStats['Home_iScore'];
		
		$sql = "SELECT atl.strName as awayName, htl.strName as homeName FROM Away_Team_Listing atl, Home_Team_Listing htl";
		$teamNames = $matchResultHandle->query($sql)->fetch();
		$awayTeamHash = md5($teamNames['awayName']);
		$homeTeamHash = md5($teamNames['homeName']);
		
		$sql = "SELECT atl.iPopularity as awayFanFactor, htl.iPopularity as homeFanFactor FROM Away_Team_Listing atl, Home_Team_Listing htl";
		$fanFactor = $matchResultHandle->query($sql)->fetch();
		$awayFanFactor = $fanFactor['awayFanFactor'];
		$homeFanFactor = $fanFactor['homeFanFactor'];

		//retrieve player statistics from match report
		$sql = "SELECT asp.*, atl.strName as TeamName " .
				"FROM Away_Statistics_Players asp, Away_Team_Listing atl " .
				"UNION SELECT hsp.*, htl.strName as TeamName " .
				"FROM Home_Statistics_Players hsp, Home_Team_Listing htl";
		$playerStats = $matchResultHandle->query($sql)->fetchAll();
		
		//retrieve player casualties from match report
		$sql = "SELECT apc.*, atl.strName as TeamName " .
				"FROM Away_Player_Casualties apc, Away_Team_Listing atl " .
				"UNION SELECT hpc.*, htl.strName as TeamName " .
				"FROM Home_Player_Casualties hpc, Home_Team_Listing htl";
		$playerCasualties = $matchResultHandle->query($sql)->fetchAll();

		unset($matchResultHandle);
	
		
		
		//extract player stats
		
		//extract inducements
		 */
	}
	
	//TODO: convert to joomlaDb
	function reRollWinnings() {
		$team = JRequest::getVar('team');
		$reroll =  JRequest::getVar('reroll');
		$matchId = JRequest::getVar('matchId');
		
		$die = rand(1,6);
		
		if ($team == "away") {
			$sql = "SELECT teamHash_Away as teamId, Away_iFame as fame, Away_iCashEarned as cashEarned,
				Away_iCashBeforeGame as cashBeforeGame, Away_iSpirallingExpenses as spirallingExpenses
				FROM Calendar WHERE ID = ".$matchId;
		} else {
			$sql = "SELECT teamHash_Home as teamId, Home_iFame as fame, Home_iCashEarned as cashEarned,
				Home_iCashBeforeGame as cashBeforeGame, Home_iSpirallingExpenses as spirallingExpenses
				FROM Calendar WHERE ID = ".$matchId;
		}
		$details = $this->dbHandle->query($sql)->fetch();
		$reRolledCash = ($details['fame'] + 1 + $die) * 10000;
		
		if ($reroll == "true") {
			$sql = "UPDATE Calendar SET" .
				" Away_iWinningsReRoll = ".$die."," .
				" Away_iCashEarned = ".$reRolledCash.",".
				" Home_iWinningsReRoll = 0" .
				" WHERE ID = ".$matchId;

			$cash = $details['cashBeforeGame'] + $reRolledCash - $details['spirallingExpenses'];
			if ($cash < 0)
				$cash = 0;

			$sql2 = "UPDATE Team_Listing SET iCash = ".$cash.
				" WHERE teamHash = '".$details['teamId']."'";
			$sql3 = "UPDATE Statistics_Season_Teams SET
				iCashEarned = iCashEarned + ".$reRolledCash."
				WHERE teamHash = '".$details['teamId']."'";
			
			//swap Home and Away in sql strings
			if ($team == "home") {
				$sql = str_replace("Away_", "Temp_", $sql);
				$sql = str_replace("Home_", "Away_", $sql);
				$sql = str_replace("Temp_", "Home_", $sql);
			}

		} else {
			$sql = "UPDATE Calendar SET " .
				"Away_iWinningsReRoll = 0, Home_iWinningsReRoll = 0 WHERE ID = ".$matchId;

			$cash = $details['cashBeforeGame'] + $details['cashEarned'] - $details['spirallingExpenses'];
			if ($cash < 0)
				$cash = 0;

			$sql2 = "UPDATE Team_Listing SET iCash = ".$cash.
					" WHERE teamHash = '".$details['teamId']."'";
			$sql3 = "UPDATE Statistics_Season_Teams SET
				iCashEarned = iCashEarned + ".$details['cashEarned']."
				WHERE teamHash = '".$details['teamId']."'";
		}

		$this->dbHandle->query($sql);
		$this->dbHandle->query($sql2);
		$this->dbHandle->query($sql3);
	}
	
	//TODO: convert to joomlaDb
	function resetLeague() {
		//remove schedule
		$sql = "DELETE FROM Calendar WHERE leagueId = ". $this->leagueId;
		$this->dbHandle->query($sql);
		//remove team stats
		$sql = "DELETE FROM Statistics_Season_Teams WHERE leagueId = ". $this->leagueId;
		$this->dbHandle->query($sql);
		//update league
		$sql = "UPDATE League SET StatusId = 1, PointsForWin = NULL WHERE ID = ". $this->leagueId;
		$this->dbHandle->query($sql);
		
		$sql = "SELECT playerHash FROM Player_Listing pl INNER JOIN Team_Listing tl ON pl.teamHash = tl.teamHash WHERE tl.leagueId = ". $this->leagueId;
		$playerList = $this->dbHandle->query($sql)->fetchAll();
		foreach ($playerList as $player) {
			$sql = "UPDATE Statistics_Season_Players SET
				iMatchPlayed=0,iMVP=0,Inflicted_iPasses=0,Inflicted_iCatches=0,Inflicted_iInterceptions=0,Inflicted_iTouchdowns=0,Inflicted_iCasualties=0,Inflicted_iTackles=0,Inflicted_iKO=0,Inflicted_iStuns=0,Inflicted_iInjuries=0,Inflicted_iDead=0,Inflicted_iMetersRunning=0,Inflicted_iMetersPassing=0,Sustained_iInterceptions=0,Sustained_iCasualties=0,Sustained_iTackles=0,Sustained_iKO=0,Sustained_iStuns=0,Sustained_iInjuries=0,Sustained_iDead=0
				WHERE playerHash = '".$player['playerHash']."'";
			$this->dbHandle->query($sql);
			$sql = "DELETE FROM Player_Casualties WHERE playerHash = '".$player['playerHash']."'";
			$this->dbHandle->query($sql);
		}
	}
	
	//TODO: convert to joomlaDb
	function deleteLeague() {
		$this->resetLeague();
		
		$sql = "SELECT playerHash FROM Player_Listing pl INNER JOIN Team_Listing tl ON pl.teamHash = tl.teamHash WHERE tl.leagueId = ". $this->leagueId;
		$playerList = $this->dbHandle->query($sql)->fetchAll();
		foreach ($playerList as $player) {
			$sql = "DELETE FROM Statistics_Season_Players WHERE playerHash = '".$player['playerHash']."'";
			$this->dbHandle->query($sql);
			$sql = "DELETE FROM Player_Casualties WHERE playerHash = '".$player['playerHash']."'";
			$this->dbHandle->query($sql);
			$sql = "DELETE FROM Player_Skills WHERE playerHash = '".$player['playerHash']."'";
			$this->dbHandle->query($sql);
			$sql = "DELETE FROM Player_Listing WHERE playerHash = '".$player['playerHash']."'";
			$this->dbHandle->query($sql);
		}
		$sql = "DELETE FROM Team_Listing WHERE leagueId = ". $this->leagueId;
		$this->dbHandle->query($sql);
		
		$sql = "DELETE FROM League WHERE ID = ". $this->leagueId;
		$this->dbHandle->query($sql);
	}
	
	//TODO: convert to joomlaDb
	function changeCoach() {
		$returnStruct = array();
		
		if (JRequest::getVar('teamId') == -1 || JRequest::getVar('coachId') == -1) {
			$returnStruct['result'] = "error";
			return $returnStruct;
		} else {
			$sql = "UPDATE Team_Listing SET coachId = ".JRequest::getVar('coachId')." WHERE teamHash = '".JRequest::getVar('teamId')."' AND leagueId = ".JRequest::getVar('leagueId');
			$this->dbHandle->query($sql);

			$returnStruct['result'] = "success";
			return $returnStruct;
		}
		$this->utils->dump(JRequest::getVar('leagueId'));
		$this->utils->dump(JRequest::getVar('teamId'));
		$this->utils->dump(JRequest::getVar('coachId'));
		//die();
		
		
	}
}

