<?php
//Upload Model for BBQL Component
//@license    GNU/GPL

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );
 
//Upload Model

class BbqlModelUpload extends JModel
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
		
		$this->bbqlHandle = $bbqlDb;
		
		// include the utilities class
		include_once($httpPathToComponent.DS.'models'.DS.'utilities.php');
	
		$this->utils = new BbqlModelUtilities;
		
		$this->stringsLocalized = $this->utils->getStringsLocalized();
		
		$this->allowed_filetypes = array('.db','.sqlite'); // These will be the types of file that will pass the validation.
		$this->max_filesize = 524288; // Maximum filesize in BYTES (currently 0.5MB).
		$this->rel_processing_path = $httpPathToComponent.DS.'uploads'.DS.'processing'.DS; // The folder where the processing of the .db will take place
		$this->abs_processing_path = $systemPathToComponent.DS.'uploads'.DS.'processing'.DS; // Absolute path for SQLite 
		
		$this->filename = $_FILES['userfile']['name']; // Get the name of the file (including file extension).
		$this->ext = substr($this->filename, strpos($this->filename,'.'), strlen($this->filename)-1); // Get the extension from the filename.

		// Check if the filetype is allowed, if not DIE and inform the user.
		if(!in_array($this->ext,$this->allowed_filetypes))
		die('The file you attempted to upload is not allowed.');
		
		// Now check the filesize, if it is too large then DIE and inform the user.
		if(filesize($_FILES['userfile']['tmp_name']) > $this->max_filesize)
		die('The file you attempted to upload is too large.');
		
		// Add a random number and timestamp to the file name to avoid collision
		$ran = rand();
		$time = time();
		
		$stripSpaces = str_replace (" ", "", $this->filename);
		$this->filename = 'del_'.$ran.$time.$stripSpaces;
	}
	
	function __destruct() {
		unset($this->dbHandle);

		//delete any previously generated SQL files
		foreach (glob($this->rel_processing_path."*.sql") as $filename) {
			unlink($filename);
		}
		
		//need to cleanup random .db/.sqlite too, but getting locked out because sqlite process is still running
		//the following will remove all previously left over .db files
		foreach (glob($this->rel_processing_path."*.db") as $filename) {
			unlink($filename);
		}
		foreach (glob($this->rel_processing_path."*.sqlite") as $filename) {
			unlink($filename);
		}
	}
	
	/*
	 * TEAM FILE UPLOADS
	 */
	function uploadTeamFile($teamType, $password, $fullControl) {
		//if team is joining for first time, check the password if applicable
		if ($teamType == "joinLeague" && $password != "") {
			if ($password != $_POST['password']) {
				return -1;
			}
		}

		$upload_path = $this->httpPathToComponent.DS.'uploads'.DS; // The final location of the processed file.
		
		// Check if we can upload to the specified path, if not DIE and inform the user.
		if(!is_writable($upload_path) OR !is_writable($this->rel_processing_path))
		die('You cannot upload to the specified directory, please CHMOD it to 777.');
		
		// Upload the file to your specified path.
		if(move_uploaded_file($_FILES['userfile']['tmp_name'],$this->rel_processing_path.$this->filename)) {
			/**** DEPRECATED
			//first we have to make the .db format accessible to php
			$this->convertDBFile($this->filename);
			****/
			
			//now access the DB to determine the teamID
			$db = 'sqlite:'.$this->abs_processing_path.$this->filename;
			
			// create a connection to SQLite3 database file with PDO and return a database handle
			try{
				$dbHandle = new PDO($db);
			}catch( PDOException $exception ){
				die($exception->getMessage());
			}
			
			//first, make sure this is a valid team file, if not return with error
			//Team files do not have the Away_Equipment_Listing table (both MatchReports and Replays do), if found return with error
			$sql = "SELECT * FROM Away_Equipment_Listing LIMIT 1";
			if ($dbHandle->query($sql) == true) {
				return -2;
			}
			
			//if this team is being uploaded to a league that is fully app controlled,
			//only single player generated teams are allowed.  Online teams have a Calendar, SP do not
			if ($fullControl == 1) {
				$sql = "SELECT * FROM Calendar LIMIT 1";
				if ($dbHandle->query($sql) == true) {
					return -3;
				}
			}

			//retrieve players
			$sql = "SELECT PL.* FROM Player_Listing PL INNER JOIN SavedGameInfo S ON PL.idTeam_Listing = S.Championship_idTeam_Listing";
			$players = $dbHandle->query($sql)->fetchAll();
			
			//retrieve casualties
			$sql = "SELECT PC.* FROM Player_Casualties PC INNER JOIN Player_Listing PL ON PC.idPlayer_Listing = PL.ID 
					INNER JOIN SavedGameInfo S ON PL.idTeam_Listing = S.Championship_idTeam_Listing";
			$casualties = $dbHandle->query($sql)->fetchAll();
			
			//retrieve skills
			$sql = "SELECT PS.* FROM Player_Skills PS INNER JOIN Player_Listing PL ON PS.idPlayer_Listing = PL.ID 
					INNER JOIN SavedGameInfo S ON PL.idTeam_Listing = S.Championship_idTeam_Listing";
			$skills = $dbHandle->query($sql)->fetchAll();
			
			//store the teamId
			$teamId = $players[0]['idTeam_Listing'];
			
			//pull Team Info for DB insertion
			$sql = "SELECT * FROM Team_Listing WHERE ID = '" . $teamId . "'";
			$teamInfo = $dbHandle->query($sql)->fetch();
			
			//store teamhash, which will be used as key for all queries
			$teamHash = md5($teamInfo['strName']); 
			$teamName = $teamInfo['strName'];
			
			unset($dbHandle);

			if ($teamType == "joinLeague") {
				//check if team is already part of another league
				$sql = "SELECT * FROM Team_Listing WHERE teamHash = '".$teamHash."' AND leagueId <> ".$this->leagueId." AND leagueId IS NOT NULL";
				$qry = $this->bbqlHandle->query($sql);
				$isInLeague = $qry->fetch();
				
				//if in another league, return with error message;
				if ($isInLeague) {
					return 0;
				}
			} else if ($teamType == "existing") {
				//make sure the team being uploaded is part of this league
				$sql = "SELECT * FROM Team_Listing WHERE teamHash = '".$teamHash."' AND leagueId = ".$this->leagueId;
				
				$qry = $this->bbqlHandle->query($sql);
				$isInLeague = $qry->fetch();
				
				//if not in this league, return with error message;
				if (!$isInLeague) {
					return 0;
				}
			}

			//to overcome stupid bug
			unset($qry,$sql,$isInLeague);

			/*
			 * refresh all team information with current, deleting current info first
			 */
			//grab current list of player IDs for reference
			$sql = "SELECT playerHash FROM Player_Listing WHERE teamHash = '".$teamHash."'";
			$referenceIds = $this->bbqlHandle->query($sql)->fetchAll();
			
			//delete team info 		
			$sql = "DELETE FROM Team_Listing WHERE teamHash = '".$teamHash."'";
			$this->bbqlHandle->query($sql);
			
			$currentPlayerList = "";
			//delete player/casualty/skill info (any retired/dead will be left)
			foreach($players as $row) {
				$sql = "DELETE FROM Player_Listing WHERE playerHash = '".$teamHash."-".$row['ID']."'";
				$this->bbqlHandle->query($sql);

				$sql = "DELETE FROM Player_Skills WHERE playerHash = '".$teamHash."-".$row['ID']."'";
				$this->bbqlHandle->query($sql);
				
				$sql = "DELETE FROM Player_Casualties WHERE playerHash = '".$teamHash."-".$row['ID']."'";
				$this->bbqlHandle->query($sql);
				$currentPlayerList = $currentPlayerList."'".$teamHash."-".$row['ID']."',"; 
			}
			//remove trailing comma
			$currentPlayerList = substr($currentPlayerList, 0, -1);
			
			//set any players that are left after the delete to retired
			$sql = "UPDATE Player_Listing SET bRetired = 1 WHERE teamHash = '".$teamHash."'";
			$this->bbqlHandle->query($sql);
			
			//add any players to the stats table that don't already exist there
			//first grab a list of IDs that DO exist
			$sql = "SELECT playerHash FROM Statistics_Season_Players WHERE playerHash IN ($currentPlayerList)";
			$statPlayers = $this->bbqlHandle->query($sql)->fetchAll();

			$statPlayerList = "";
			foreach($statPlayers as $row) {
				$statPlayerList = $statPlayerList."'".$row['playerHash']."',"; 
			}
			//remove trailing comma
			if (strlen($statPlayerList)) $statPlayerList = substr($statPlayerList, 0, -1);
			
			//compare the list of players in the stats table to those in the player_listing, and insert any new
			foreach($players as $row) {
				if (strpos($statPlayerList, "'".$teamHash."-".$row['ID']."'") === false) {
					$sql = "INSERT INTO Statistics_Season_Players (iSeason,iMatchPlayed,iMVP,Inflicted_iPasses,Inflicted_iCatches,Inflicted_iInterceptions,Inflicted_iTouchdowns,Inflicted_iCasualties,Inflicted_iTackles,Inflicted_iKO,Inflicted_iStuns,Inflicted_iInjuries,Inflicted_iDead,Inflicted_iMetersRunning,Inflicted_iMetersPassing,Sustained_iInterceptions,Sustained_iCasualties,Sustained_iTackles,Sustained_iKO,Sustained_iStuns,Sustained_iInjuries,Sustained_iDead,playerHash)" .
						" VALUES (1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,'".$teamHash."-".$row['ID']."')";
					$this->bbqlHandle->query($sql);
				}
			}
			
			//define team info fields
			$teamFields = array('strName','idRaces','strLogo','iTeamColor','strLeitmotiv','strBackground','iValue','iPopularity',
			'iCash','iCheerleaders','iBalms','bApothecary','iRerolls','bEdited','idTeam_Listing_Filters',
			'idStrings_Formatted_Background','idStrings_Localized_Leitmotiv','iNextPurchase','iAssistantCoaches');
			
			//insert team info
			$sql = "INSERT INTO Team_Listing (";
			foreach($teamFields as $value) {
				$sql = $sql . $value . ",";
			}
			$sql = $sql . "coachId,leagueId,teamHash) VALUES (";
			foreach($teamFields as $value) {
				$sql = $sql . $this->bbqlHandle->quote($teamInfo[$value]) . ",";
			}
			$sql = $sql . $this->bbqlHandle->quote($_POST['coachId']).","
				. $this->bbqlHandle->quote($this->leagueId).","
				. "'".$teamHash ."')";

			$this->bbqlHandle->query($sql);
			
			//define player fields
			$playerFields = $this->utils->getPlayer_ListingFields();
					
			//insert player info
			foreach($players as $row) {
				$sql = "INSERT INTO Player_Listing (";
				foreach($playerFields as $value) {
					$sql = $sql . $value . ",";
				}
				$sql = substr($sql, 0, -1).") VALUES (";
				//set additional values
				$row['playerHash'] = $teamHash."-".$row['ID'];
				$row['teamHash'] = $teamHash;
				$row['bRetired'] = 0;
				$row['playerId'] = $row['ID'];
				
				foreach($playerFields as $value) {
					$sql = $sql . $this->bbqlHandle->quote($row[$value]) . ",";
				}
				$sql = substr($sql, 0, -1).")";
				//die($sql);
				$this->bbqlHandle->query($sql);
			}
			
			//insert casualty info
			foreach($casualties as $row) {
				$sql = "INSERT INTO Player_Casualties (idPlayer_Casualty_Types,playerHash) VALUES (".$row['idPlayer_Casualty_Types'].",'".$teamHash."-".$row['idPlayer_Listing']."')";
				$this->bbqlHandle->query($sql);
			}
			//insert skill info
			foreach($skills as $row) {
				$sql = "INSERT INTO Player_Skills (idSkill_Listing,playerHash) VALUES (".$row['idSkill_Listing'].",'".$teamHash."-".$row['idPlayer_Listing']."')";
				$this->bbqlHandle->query($sql);
			}
			
			unset($this->bbqlHandle);
		
			$myTeamIDfile = $upload_path.$teamName.'.db';
			//now remove an old team ID .db file if it exists
			if (file_exists($myTeamIDfile)) unlink($myTeamIDfile);
			
			//copy randomly generated file to the teamID.db
			copy($this->rel_processing_path.$this->filename, $myTeamIDfile);
			
			echo 'Your file upload was successful!'; // It worked.
			
			//return the teamId for redirect
			return $teamHash;
			
		} else
			echo 'There was an error during the file upload.  Please try again.'; // It failed :(.
	}
	
	function uploadMatchReport($revert=false) {
		$upload_path = $this->httpPathToComponent.DS.'uploads'.DS.$this->leagueId.DS; // The final location of the processed file.
		
		//set operator for sql functions
		if ($revert == false)
			$op = "+";
		else
			$op = "-";
			
		//create folder if it doesn't exist
		if (!file_exists($upload_path))
			mkdir($upload_path);
		
		
		// Check if we can upload to the specified path, if not DIE and inform the user.
		if(!is_writable($upload_path) OR !is_writable($this->rel_processing_path))
			die('You cannot upload to the specified directory, please CHMOD it to 777.');
		
		
		// Upload the file to your specified path.
		if(move_uploaded_file($_FILES['userfile']['tmp_name'],$this->rel_processing_path.$this->filename)) {
			/**** DEPRECATED
			//first we have to make the .db format accessible to php
			$this->convertDBFile($this->filename); 
			****/
					
			//now access the DB to determine the teamIDs
			$db = 'sqlite:'.$this->abs_processing_path.$this->filename;
			
			// create a connection to SQLite3 database file with PDO and return a database handle
			try{
				$matchResultHandle = new PDO($db);
			}catch( PDOException $exception ){
				die($exception->getMessage());
			}
			
			//first, make sure this is a valid match report, if not return with error
			//Replay files have the Replay_NetCommands table, if found return with error
			$sql = "SELECT * FROM Away_Inducements_Listing LIMIT 1";
			if ($matchResultHandle->query($sql) == false) {
				return array("result" => "error", "status" => -2);
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
			
			$sql = "SELECT atl.iPopularity as awayFanFactor, htl.iPopularity as homeFanFactor,
					atl.iValue as awayTeamValue, htl.iValue as homeTeamValue,
					atl.iCash as awayCash, htl.iCash as homeCash
					FROM Away_Team_Listing atl, Home_Team_Listing htl";
			$fanFactor = $matchResultHandle->query($sql)->fetch();
			$awayFanFactor = $fanFactor['awayFanFactor'];
			$awayTeamValue = $fanFactor['awayTeamValue'];
			$awayCash = $fanFactor['awayCash'];
			$homeFanFactor = $fanFactor['homeFanFactor'];
			$homeTeamValue = $fanFactor['homeTeamValue'];
			$homeCash = $fanFactor['homeCash'];

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
			
			//determine if the app is fully controlling this league
			//For cyanide leagues, we never want to update the Player_Listing
			//or Team_Listing tables.  Coaches must upload their current
			//team files to update these tables.
			$sql = "SELECT FullControl FROM League WHERE ID = ".$this->leagueId;
			$fullControlQry = $this->bbqlHandle->query($sql)->fetch();
			$fullControl = $fullControlQry['FullControl'];
			
			$homeJourneyman = 0;
			$awayJourneyman = 0;
			
			/******************************
			 *Find the most recent match played between these two teams
			 *****************************/
			//find the most recently played match between these two teams
			$sql = "SELECT * FROM Calendar WHERE leagueId = ".$this->leagueId .
				" AND ((teamHash_Away = '" . $awayTeamHash . "' AND teamHash_Home = '" . $homeTeamHash . "') OR ".
				" (teamHash_Away = '" . $homeTeamHash . "' AND teamHash_Home = '" . $awayTeamHash . "')) " .
				" AND Away_iScore IS NOT NULL" .
				" ORDER BY ID DESC LIMIT 1";
			$recentMatch = $this->bbqlHandle->query($sql)->fetch();
				
			
			if ($revert == false) {
				//first make sure the currently uploaded match report isn't a duplicate of a previous match
				$fieldsToCheck = array('Away_iScore','Away_iPossessionBall',
				'Away_Occupation_iOwn','Away_Occupation_iTheir','Away_Inflicted_iPasses','Away_Inflicted_iCatches',
				'Away_Inflicted_iInterceptions','Away_Inflicted_iTouchdowns','Away_Inflicted_iCasualties','Away_Inflicted_iTackles',
				'Away_Inflicted_iKO','Away_Inflicted_iInjuries','Away_Inflicted_iDead','Away_Inflicted_iMetersRunning',
				'Away_Inflicted_iMetersPassing',
				'Home_iScore','Home_iPossessionBall',
				'Home_Occupation_iOwn','Home_Occupation_iTheir','Home_iMVP','Home_Inflicted_iPasses','Home_Inflicted_iCatches',
				'Home_Inflicted_iInterceptions','Home_Inflicted_iTouchdowns','Home_Inflicted_iCasualties','Home_Inflicted_iTackles',
				'Home_Inflicted_iKO','Home_Inflicted_iInjuries','Home_Inflicted_iDead','Home_Inflicted_iMetersRunning',
				'Home_Inflicted_iMetersPassing');

				$matchesIdentical = true;
				//check all of the above fields against each other
				foreach($fieldsToCheck as $value) {
					//if any values differ, matches aren't identical
					if ($matchStats[$value] != $recentMatch[$value])
						$matchesIdentical = false;
				}
				//if match is a duplicate of previous, return with error
				if ($matchesIdentical) {
					return array("result" => "error", "status" => -3);
				}

				//find an unplayed game in the schedule who's team IDs match those in the Match Report
				$sql = "SELECT ID,teamHash_Away,teamHash_Home FROM Calendar WHERE leagueId = ".$this->leagueId .
					" AND ((teamHash_Away = '" . $awayTeamHash . "' AND teamHash_Home = '" . $homeTeamHash . "') OR ".
					" (teamHash_Away = '" . $homeTeamHash . "' AND teamHash_Home = '" . $awayTeamHash . "')) " .
					" AND Away_iScore IS NULL";
				$scheduledMatches = $this->bbqlHandle->query($sql)->fetch();
				$leagueTeamHashAway = $scheduledMatches['teamHash_Away'];
				$leagueTeamHashHome = $scheduledMatches['teamHash_Home'];
			} else {
				//have already determined above the most recent match betweem these two teams for reversion
				$scheduledMatches = $recentMatch;
				$leagueTeamHashAway = $scheduledMatches['teamHash_Away'];
				$leagueTeamHashHome = $scheduledMatches['teamHash_Home'];
			}
			
			//if no matches are found, return with error
			if (!$scheduledMatches) {
				return array("result" => "error", "status" => -1);
			}
			
			//determine if league and match report calendar have equivalent home and away teams
			if ($leagueTeamHashAway == $awayTeamHash) {
				//do nothing
			} else {
				//swap the home and away teams on the Calendar
				$sql = "UPDATE Calendar SET teamHash_Away = '".$leagueTeamHashHome."',".
					" teamHash_Home = '".$leagueTeamHashAway."'".
					" WHERE ID = " . $scheduledMatches['ID'];
				$this->bbqlHandle->query($sql);
			}
			
			if ($fullControl && !$revert) {
				//remove any current "miss next game" flags from the two teams so that these players will be available for the next match
				$sql = "UPDATE Player_Listing SET iMatchSuspended = 0 WHERE teamHash IN ('".$awayTeamHash."','".$homeTeamHash."')";
				$this->bbqlHandle->query($sql);
			}
			
			$sql = "SELECT * FROM Player_Listing WHERE bRetired = 0 AND teamHash IN ('".$awayTeamHash."','".$homeTeamHash."')";
			$currentPlayerAttributes = $this->bbqlHandle->query($sql)->fetchAll();
			
			//set up associative array to make player attribute access easy
			$currentPlayerMap = array();
			foreach ($currentPlayerAttributes as $row) {
				$currentPlayerMap[$row['playerHash']] = $row;
				
				if ($row['teamHash'] == $awayTeamHash && $row['journeyman'] == 1) {
					$awayJourneyman = 1;
				}
				if ($row['teamHash'] == $homeTeamHash && $row['journeyman'] == 1) {
					$homeJourneyman = 1;
				}
				
			}
			$SPPmap = array(1=>5,2=>15,3=>30,4=>50,5=>75,6=>175,7=>10000);
			
			//update Calendar with match result
			$matchFields = array('Championship_iGroup','Championship_idRule_Types',
			'Championship_iEliminitationLevel','Playoff_iEliminationLevel','Playoff_bAwayGame','iSpectators','iRating','bPlayed',
			'Away_iScore','Away_iReward','Away_iCashEarned','Away_iPossessionBall',
			'Away_Occupation_iOwn','Away_Occupation_iTheir','Away_iMVP','Away_Inflicted_iPasses','Away_Inflicted_iCatches',
			'Away_Inflicted_iInterceptions','Away_Inflicted_iTouchdowns','Away_Inflicted_iCasualties','Away_Inflicted_iTackles',
			'Away_Inflicted_iKO','Away_Inflicted_iInjuries','Away_Inflicted_iDead','Away_Inflicted_iMetersRunning',
			'Away_Inflicted_iMetersPassing','Away_Sustained_iPasses','Away_Sustained_iCatches','Away_Sustained_iInterceptions',
			'Away_Sustained_iTouchdowns','Away_Sustained_iCasualties','Away_Sustained_iTackles','Away_Sustained_iKO',
			'Away_Sustained_iInjuries','Away_Sustained_iDead','Away_Sustained_iMetersRunning','Away_Sustained_iMetersPassing',
			'Away_iMetersRunning','Away_iMetersPassing','Home_iScore','Home_iReward','Home_iCashEarned','Home_iPossessionBall',
			'Home_Occupation_iOwn','Home_Occupation_iTheir','Home_iMVP','Home_Inflicted_iPasses','Home_Inflicted_iCatches',
			'Home_Inflicted_iInterceptions','Home_Inflicted_iTouchdowns','Home_Inflicted_iCasualties','Home_Inflicted_iTackles',
			'Home_Inflicted_iKO','Home_Inflicted_iInjuries','Home_Inflicted_iDead','Home_Inflicted_iMetersRunning',
			'Home_Inflicted_iMetersPassing','Home_Sustained_iPasses','Home_Sustained_iCatches','Home_Sustained_iInterceptions',
			'Home_Sustained_iTouchdowns','Home_Sustained_iCasualties','Home_Sustained_iTackles','Home_Sustained_iKO',
			'Home_Sustained_iInjuries','Home_Sustained_iDead','Home_Sustained_iMetersRunning','Home_Sustained_iMetersPassing',
			'Home_iMetersRunning','Home_iMetersPassing');
			
			//set non-team values
			$sql = "UPDATE Calendar SET ";
			if (!$revert) {
				foreach($matchFields as $value) {
					$sql = $sql . $value . " = " . $this->bbqlHandle->quote($matchStats[$value]) . ",";
				}
			} else {
				foreach($matchFields as $value) {
					$sql = $sql . $value . " = NULL,";
				}
			}
			//remove trailing comma
			$sql = substr($sql, 0, -1);
			$sql = $sql . " WHERE ID = " . $scheduledMatches['ID'];
			
			$this->bbqlHandle->query($sql);
			
			//grab the number of points for a win
			$sql = "SELECT PointsForWin FROM League WHERE ID = ".$this->leagueId;
			$pfwqry = $this->bbqlHandle->query($sql)->fetchAll();
			$pointsForWin = $pfwqry[0]['PointsForWin'];
			
			//update standings
			//set default stats
			$awayPoints = 0;
			$awayWins = 0;
			$awayDraws = 0;
			$awayLosses = 0;
			$homePoints = 0;
			$homeWins = 0;
			$homeDraws = 0;
			$homeLosses = 0;

			if ($awayTeamScore == $homeTeamScore) {
				$awayPoints = 1;
				$homePoints = 1;
				$awayDraws = 1;
				$homeDraws = 1;
			} else if ($awayTeamScore > $homeTeamScore) {
				$awayPoints = $pointsForWin;
				$awayWins = 1;
				$homeLosses = 1;
			} else {
				$homePoints = $pointsForWin;
				$homeWins = 1;
				$awayLosses = 1;
			}
			
			//update Team Stats
			$statFields = array('iMVP','Inflicted_iPasses','Inflicted_iCatches','Inflicted_iInterceptions',
			'Inflicted_iTouchdowns','Inflicted_iCasualties','Inflicted_iTackles','Inflicted_iKO','Inflicted_iInjuries',
			'Inflicted_iDead','Inflicted_iMetersRunning','Inflicted_iMetersPassing','Sustained_iPasses','Sustained_iCatches',
			'Sustained_iInterceptions','Sustained_iTouchdowns','Sustained_iCasualties','Sustained_iTackles','Sustained_iKO',
			'Sustained_iInjuries','Sustained_iDead','Sustained_iMetersRunning','Sustained_iMetersPassing','iCashEarned',
			'iPossessionBall','Occupation_iOwn','Occupation_iTheir');
			
			//update Away Team Statistics
			$sql = "UPDATE Statistics_Season_Teams SET iMatchPlayed = iMatchPlayed ".$op." 1,";
			foreach($statFields as $value) {
				$sql = $sql.$value." = ".$value.$op.$matchStats['Away_'.$value] . ",";
			}
			$sql = $sql."iPoints = iPoints".$op.$awayPoints.
				",iWins = iWins".$op.$awayWins.
				",iDraws = iDraws".$op.$awayDraws.
				",iLoss = iLoss".$op.$awayLosses.
				",touchdownDif = touchdownDif".$op.$awayTeamScore." - ".$homeTeamScore;
				if (!$fullControl) {
					$sql = $sql.",iSpectators = iSpectators".$op.$matchStats['iSpectators'];
				}
				$sql = $sql.",Average_iMatchRating = Average_iMatchRating".$op.$matchStats['iRating'];
			$sql = $sql . " WHERE teamHash = '" . $awayTeamHash."'";
			$this->bbqlHandle->query($sql);
			
			//update Home Team Statistics
			$sql = "UPDATE Statistics_Season_Teams SET iMatchPlayed = iMatchPlayed ".$op." 1,";
			foreach($statFields as $value) {
				$sql = $sql.$value." = ".$value.$op.$matchStats['Home_'.$value] . ",";
			}
			$sql = $sql."iPoints = iPoints".$op.$homePoints.
				",iWins = iWins".$op.$homeWins.
				",iDraws = iDraws".$op.$homeDraws.
				",iLoss = iLoss".$op.$homeLosses.
				",touchdownDif = touchdownDif".$op.$homeTeamScore." - ".$awayTeamScore;
				if (!$fullControl) {
					$sql = $sql.",iSpectators = iSpectators".$op.$matchStats['iSpectators'];
				}
				$sql = $sql.",Average_iMatchRating = Average_iMatchRating".$op.$matchStats['iRating'];
			$sql = $sql . " WHERE teamHash = '" . $homeTeamHash . "'";
			$this->bbqlHandle->query($sql);
			
			//update Player Stats
			$playerStatFields = array('iMatchPlayed','iMVP','Inflicted_iPasses',
				'Inflicted_iCatches','Inflicted_iInterceptions','Inflicted_iTouchdowns','Inflicted_iCasualties',
				'Inflicted_iTackles','Inflicted_iKO','Inflicted_iStuns','Inflicted_iInjuries','Inflicted_iDead',
				'Inflicted_iMetersRunning','Inflicted_iMetersPassing','Sustained_iInterceptions','Sustained_iCasualties',
				'Sustained_iTackles','Sustained_iKO','Sustained_iStuns','Sustained_iInjuries','Sustained_iDead');
				
			//update Player Statistics
			foreach($playerStats as $statsRow) {
				$playerHash = md5($statsRow['TeamName'])."-".$statsRow['idPlayer_Listing'];
				$SPP = 0;
				$sql = "UPDATE Statistics_Season_Players SET ";
				foreach($playerStatFields as $fieldsRow) {
					$sql = $sql.$fieldsRow." = ".$fieldsRow.$op.$statsRow[$fieldsRow] . ",";
					//calculate SPP
					switch ($fieldsRow) {
						case 'iMVP':
							$SPP = $SPP + $statsRow[$fieldsRow]*5;
							break;
						case 'Inflicted_iTouchdowns':
							$SPP = $SPP + $statsRow[$fieldsRow]*3;
							break;
						case 'Inflicted_iInterceptions':
							$SPP = $SPP + $statsRow[$fieldsRow]*2;
							break;
						case 'Inflicted_iCasualties':
							$SPP = $SPP + $statsRow[$fieldsRow]*2;
							break;
						case 'Inflicted_iPasses':
							$SPP = $SPP + $statsRow[$fieldsRow]*1;
							break;
					}
				}
				//remove trailing comma
				$sql = substr($sql, 0, -1);
				$sql = $sql." WHERE playerHash = '".$playerHash."'";
				$this->bbqlHandle->query($sql);
				
				if ($fullControl && !$revert) {
					//determine if the player has leveled up, and if so, how many levels
					$currentLevel = $currentPlayerMap[$playerHash]['idPlayer_Levels'];
					$currentSPP = $currentPlayerMap[$playerHash]['iExperience'];
					$newSPP = $currentSPP + $SPP;
					$iNbLevelsUp = 0;
					$LevelUp_iRollResult = 0;
					$LevelUp_iRollResult2 = 0;
					$LevelUp_bDouble = 0;
	
					for ($i=$currentLevel; $i<count($SPPmap); $i++) {
						if ($newSPP > $SPPmap[$i]) {
							$iNbLevelsUp++;
							//if there are previous unused rolls, do not update the dice
							if ($currentPlayerMap[$playerHash]['LevelUp_iRollResult'] != 0) {
								$LevelUp_iRollResult = $currentPlayerMap[$playerHash]['LevelUp_iRollResult'];
								$LevelUp_iRollResult2 = $currentPlayerMap[$playerHash]['LevelUp_iRollResult2'];
							} else {
								//roll skill dice
								$LevelUp_iRollResult = rand(1,6);
								$LevelUp_iRollResult2 = rand(1,6);
							}
							if ($LevelUp_iRollResult == $LevelUp_iRollResult2) {
								$LevelUp_bDouble = 1;	
							}
							
						} else {
							break;
						}
					}
					
					//update player attributes
					$sql = "UPDATE Player_Listing SET " .
							" iExperience = iExperience + ".$SPP.",".
							" iNbLevelsUp = ".$iNbLevelsUp .",".
							" LevelUp_iRollResult = ".$LevelUp_iRollResult .",".
							" LevelUp_iRollResult2 = ".$LevelUp_iRollResult2 .",".
							" LevelUp_bDouble = ".$LevelUp_bDouble.
							" WHERE playerHash = '".md5($statsRow['TeamName'])."-".$statsRow['idPlayer_Listing']."'";
					$this->bbqlHandle->query($sql);
					
				//else we're reverting
				} elseif ($fullControl && $revert) {
					//update player attributes
					$sql = "UPDATE Player_Listing SET " .
							" iExperience = iExperience - ".$SPP.
							" WHERE playerHash = '".md5($statsRow['TeamName'])."-".$statsRow['idPlayer_Listing']."'";
					$this->bbqlHandle->query($sql);
				}
				
			} //end foreach
			if ($revert) die();
			/********************************
			 * Everything from here down is only for Fully Controlled Leagues
			 *******************************/
			/**
			 * UPDATE CASUALTIES
			 */
			if ($fullControl) {
				$defaultAttributeMap = $this->utils->getDefaultPlayerAttributes();
				foreach($playerCasualties as $casRow) {
					$insertCasualty = true;
					$bRetired = 0;
					$bDead = 0;
					$iMatchSuspended = 0;
					$playerHash = md5($casRow['TeamName'])."-".$casRow['idPlayer_Listing'];
					$MA = $currentPlayerMap[$playerHash]['Characteristics_fMovementAllowance'];
					$ST = $currentPlayerMap[$playerHash]['Characteristics_fStrength'];
					$AG = $currentPlayerMap[$playerHash]['Characteristics_fAgility'];
					$AV = $currentPlayerMap[$playerHash]['Characteristics_fArmourValue'];
					//only insert injuries greater than "badly hurt"
					if ($casRow['idPlayer_Casualty_Types'] > 1) {
						//handle dead
						if ($casRow['idPlayer_Casualty_Types'] == 18) {
							$bRetired = 1;
							$bDead = 1;
							$insertCasualty = false;
						//handle stat decreases
						} elseif ($casRow['idPlayer_Casualty_Types'] >= 12) {
							$iMatchSuspended = 1;
							switch ($casRow['idPlayer_Casualty_Types']) {
								//MA decrease
								case 12:
								case 13:
									$currentMA = $this->utils->convertMA($MA);
									$defaultMA = $this->utils->convertMA($defaultAttributeMap[$currentPlayerMap[$playerHash]['idPlayer_Types']]['MA']);
									if ($currentMA == 1 || $currentMA - $defaultMA == -2) {
										//stat cannot go any lower, do nothing
										$insertCasualty = false;
									} else {
										$MA = $this->utils->setMApercent($currentMA-1);
									}
									break;
								//AV decrease
								case 14:
								case 15:
									$currentAV = $this->utils->convertAV($AV);
									$defaultAV = $this->utils->convertAV($defaultAttributeMap[$currentPlayerMap[$playerHash]['idPlayer_Types']]['AV']);
									if ($currentAV == 1 || $currentAV - $defaultAV == -2) {
										//stat cannot go any lower, do nothing
										$insertCasualty = false;
									} else {
										$AV = $this->utils->setAVpercent($currentAV-1);
									}
									break;
								//AG decrease
								case 16:
									$currentAG = $this->utils->convertAG($AG);
									$defaultAG = $this->utils->convertAG($defaultAttributeMap[$currentPlayerMap[$playerHash]['idPlayer_Types']]['AG']);
									if ($currentAG == 1 || $currentAG - $defaultAG == -2) {
										//stat cannot go any lower, do nothing
										$insertCasualty = false;
									} else {
										$AG = $this->utils->setAGpercent($currentAG-1);
									}
									break;
								//ST decrease
								case 17:
									$currentST = $this->utils->convertST($ST);
									$defaultST = $this->utils->convertST($defaultAttributeMap[$currentPlayerMap[$playerHash]['idPlayer_Types']]['ST']);
									if ($currentST == 1 || $currentST - $defaultST == -2) {
										//stat cannot go any lower, do nothing
										$insertCasualty = false;
									} else {
										$ST = $this->utils->setSTpercent($currentST-1);
									}
									break;					
							}
						//niggles
						} elseif ($casRow['idPlayer_Casualty_Types'] >= 10) {
							$iMatchSuspended = 1;
						//all other casualties
						} else {
							$iMatchSuspended = 1;
							$insertCasualty = false;
						}
						//update player attributes
						$sql = "UPDATE Player_Listing SET " .
								" Characteristics_fMovementAllowance = ".$MA.",".
								" Characteristics_fStrength = ".$ST.",".
								" Characteristics_fAgility = ".$AG.",".
								" Characteristics_fArmourValue = ".$AV.",".
								" iMatchSuspended = ".$iMatchSuspended.",".
								" bRetired = ".$bRetired.",".
								" bDead = ".$bDead.
								" WHERE playerHash = '".$playerHash."'";
						$this->bbqlHandle->query($sql);
						
						if ($insertCasualty) {
							$sql = "INSERT INTO Player_Casualties (playerHash,idPlayer_Casualty_Types) VALUES ('".$playerHash."',".$casRow['idPlayer_Casualty_Types'].")";
							$this->bbqlHandle->query($sql);
						}
					}
				} //end foreach
				
				/*
				 * POST MATCH SEQUENCE
				 * Only perform the post match sequence if the league is set to be fully controlled by the app
				 */
				$Away_iFAME = 0;
				$Home_iFAME = 0;
				//spectators
				$awaySpecRoll1 = rand(1,6);
				$awaySpecRoll2 = rand(1,6);
				$Away_iSpectators = ($awaySpecRoll1 + $awaySpecRoll2 + $awayFanFactor)*1000;
				$homeSpecRoll1 = rand(1,6);
				$homeSpecRoll2 = rand(1,6);
				$Home_iSpectators = ($homeSpecRoll1 + $homeSpecRoll2 + $homeFanFactor)*1000;
				
				//FAME
				if ($Away_iSpectators > $Home_iSpectators) {
					$Away_iFAME++;
					if ($Away_iSpectators >= $Home_iSpectators*2) {
						$Away_iFAME++;
					}
				}
				if ($Home_iSpectators > $Away_iSpectators) {
					$Home_iFAME++;
					if ($Home_iSpectators >= $Away_iSpectators*2) {
						$Home_iFAME++;
					}
				}
				
				//Winnings
				$Away_iWinningsRoll = rand(1,6);
				$Home_iWinningsRoll = rand(1,6);
				
				$Away_iCashEarned = ($Away_iWinningsRoll + $Away_iFAME) * 10000;
				$Home_iCashEarned = ($Home_iWinningsRoll + $Home_iFAME) * 10000;
				
				if ($awayWins == 1 || $awayDraws == 1) {
					$Away_iCashEarned = $Away_iCashEarned + 10000;
				}
				if ($homeWins == 1 || $homeDraws == 1) {
					$Home_iCashEarned = $Home_iCashEarned + 10000;
				}
				
				//Fan Factor
				$AwayFFroll1 = rand(1,6);
				$AwayFFroll2 = rand(1,6);
				$AwayFFroll3 = rand(1,6);
				
				$HomeFFroll1 = rand(1,6);
				$HomeFFroll2 = rand(1,6);
				$HomeFFroll3 = rand(1,6);
				
				$Away_iFFModifier = 0;
				$Home_iFFModifier = 0;
				
				if ($awayWins == 1) {
					$Away_iFanFactorRoll = $AwayFFroll1.",".$AwayFFroll2.",".$AwayFFroll3;
					$AwayFFTotal = $AwayFFroll1 + $AwayFFroll2 + $AwayFFroll3;
					$Home_iFanFactorRoll = $HomeFFroll1.",".$HomeFFroll2;
					$HomeFFTotal = $HomeFFroll1 + $HomeFFroll2;
					if ($AwayFFTotal > $awayFanFactor) $Away_iFFModifier = 1;
					if ($HomeFFTotal < $homeFanFactor) $Home_iFFModifier = -1;
				} elseif ($homeWins == 1) {
					$Away_iFanFactorRoll = $AwayFFroll1.",".$AwayFFroll2;
					$AwayFFTotal = $AwayFFroll1 + $AwayFFroll2;
					$Home_iFanFactorRoll = $HomeFFroll1.",".$HomeFFroll2.",".$HomeFFroll3;
					$HomeFFTotal = $HomeFFroll1 + $HomeFFroll2 + $HomeFFroll3;
					if ($AwayFFTotal < $awayFanFactor) $Away_iFFModifier = -1;
					if ($HomeFFTotal > $homeFanFactor) $Home_iFFModifier = 1;
				} else {
					$Away_iFanFactorRoll = $AwayFFroll1.",".$AwayFFroll2;
					$AwayFFTotal = $AwayFFroll1 + $AwayFFroll2;
					$Home_iFanFactorRoll = $HomeFFroll1.",".$HomeFFroll2;
					$HomeFFTotal = $HomeFFroll1 + $HomeFFroll2;
					if ($AwayFFTotal > $awayFanFactor) $Away_iFFModifier = 1;
					if ($AwayFFTotal < $awayFanFactor) $Away_iFFModifier = -1;
					if ($HomeFFTotal > $homeFanFactor) $Home_iFFModifier = 1;
					if ($HomeFFTotal < $homeFanFactor) $Home_iFFModifier = -1;
				}
				
				/*
				 * SPIRALLING EXPENSES
				 */
				$awayTeamSpirallingExpenses = $this->utils->calculateSpirallingExpenses($awayTeamValue);
				$homeTeamSpirallingExpenses = $this->utils->calculateSpirallingExpenses($homeTeamValue);

				/*
				 * UPDATE CALENDAR
				 */

				$sql = "UPDATE Calendar SET " .
					"Away_iFAME = ".$Away_iFAME.",".
					"Home_iFAME = ".$Home_iFAME.",".
					"Away_iSpectators = ".$Away_iSpectators.",".
					"Home_iSpectators = ".$Home_iSpectators.",".
					"Away_iSpectatorsRoll = '".$awaySpecRoll1.",".$awaySpecRoll2."',".
					"Home_iSpectatorsRoll = '".$homeSpecRoll1.",".$homeSpecRoll2."',".
					"Away_iFanFactor = ".$awayFanFactor.",".
					"Home_iFanFactor = ".$homeFanFactor.",".
					"Away_iWinningsRoll = ".$Away_iWinningsRoll.",".
					"Home_iWinningsRoll = ".$Home_iWinningsRoll.",".
					"Away_iCashEarned = ".$Away_iCashEarned.",".
					"Home_iCashEarned = ".$Home_iCashEarned.",".
					"Away_iFanFactorRoll = '".$Away_iFanFactorRoll."',".
					"Home_iFanFactorRoll = '".$Home_iFanFactorRoll."',".
					"Away_iFFModifier = ".$Away_iFFModifier.",".
					"Home_iFFModifier = ".$Home_iFFModifier.",".
					"Away_iTeamValue = ".$awayTeamValue.",".
					"Home_iTeamValue = ".$homeTeamValue.",".
					"Away_iCashBeforeGame = ".$awayCash.",".
					"Home_iCashBeforeGame = ".$homeCash.",".
					"Away_iSpirallingExpenses = ".$awayTeamSpirallingExpenses.",".
					"Home_iSpirallingExpenses = ".$homeTeamSpirallingExpenses.
					" WHERE ID = " . $scheduledMatches['ID'];

				$this->bbqlHandle->query($sql);
				
				/*
				 * update teams with post-match info
				 */
				//update Away Team
				$sql = "UPDATE Team_Listing SET ";
				$sql2 = "UPDATE Statistics_Season_Teams SET ";
				//commit money immediately if team drew or lost
				if ($awayWins == 0) {
					$netCash = $awayCash + $Away_iCashEarned - $awayTeamSpirallingExpenses;
					if ($netCash < 0)
						$netCash = 0;
					$sql = $sql."iCash = ".$netCash.",";
					$sql2 = $sql2."iCashEarned = iCashEarned + ".$Away_iCashEarned.",";
				}
				$sql = $sql."iPopularity = iPopularity + ".$Away_iFFModifier.",
					journeymenHireFire = ".$awayJourneyman."
					WHERE teamHash = '".$awayTeamHash."'";
				$sql2 = $sql2."iSpectators = iSpectators + ".$Away_iSpectators."
					WHERE teamHash = '".$awayTeamHash."'";
					
				//die($sql. "<br><br>".$sql2);
				$this->bbqlHandle->query($sql);
				$this->bbqlHandle->query($sql2);
						
				//update Home Team
				$sql = "UPDATE Team_Listing SET ";
				$sql2 = "UPDATE Statistics_Season_Teams SET ";
				//commit money immediately if team drew or lost
				if ($homeWins == 0) {
					$netCash = $homeCash + $Home_iCashEarned - $homeTeamSpirallingExpenses;
					if ($netCash < 0)
						$netCash = 0;
					$sql = $sql."iCash = ".$netCash.",";
					$sql2 = $sql2."iCashEarned = iCashEarned + ".$Home_iCashEarned.",";
				}
				$sql = $sql."iPopularity = iPopularity + ".$Home_iFFModifier.",
					journeymenHireFire = ".$homeJourneyman."
					WHERE teamHash = '".$homeTeamHash."'";
				$sql2 = $sql2."iSpectators = iSpectators + ".$Home_iSpectators."
					WHERE teamHash = '".$homeTeamHash."'";
				$this->bbqlHandle->query($sql);
				$this->bbqlHandle->query($sql2);
				
				//update team values
				$this->utils->updateTeamValue($awayTeamHash);
				$this->utils->updateTeamValue($homeTeamHash);				
			} //end fullControl
			
			$matchIDfile = $upload_path.$scheduledMatches['ID'].'.sqlite';
			//now remove an match ID .sqlite file if it exists
			if (file_exists($matchIDfile)) unlink($matchIDfile);
			
			//copy randomly generated file to the teamID.db
			copy($this->rel_processing_path.$this->filename, $matchIDfile);

			return array("result" => "success", "status" => $scheduledMatches['ID'], "fullControl" => $fullControl);
	
		} else
			echo 'There was an error during the file upload.  Please try again.'; // It failed :(
	}
    
	/**** DEPRECATED
    //Converts BB .db to PHP useable format
	function convertDBFile($fileName) {
		
		//PHP can't read the SQLite generated by BB, so dump uploaded .db file via shell
		$cmd = $this->systemPathToComponent.DS.'resources'.DS.'sqlite3 '.$this->abs_processing_path.$fileName.' .dump > '.$this->abs_processing_path.$fileName.'.sql';
		
		$output = shell_exec($cmd);
		
		//delete the original file so that we can save back to the same name
		$myFile = $this->httpPathToComponent.DS.'uploads'.DS.'processing'.DS.$fileName;
		unlink($myFile);
		
		//create new .db from dump via shell, which PHP will be able to read
		$cmd =  $this->systemPathToComponent.DS.'resources'.DS.'sqlite3 '.$this->abs_processing_path.$fileName.' < '.$this->abs_processing_path.$fileName.'.sql';
		$output = shell_exec($cmd);	
	}
	****/
}