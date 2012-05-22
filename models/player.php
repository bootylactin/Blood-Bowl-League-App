<?php
/**
 * Team Model for BBQL Component
 * @license    GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );

/**
 * BBQL Model
 */
class BbqlModelPlayer extends JModel {
	
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		
		global $httpPathToComponent;
		
		$this->playerId = JRequest::getVar('playerId');
		
		$this->joomlaDb = JFactory::getDBO();
		
		// include the utilities class
		include_once($httpPathToComponent.DS.'models'.DS.'utilities.php');
	
		$this->utils = new BbqlModelUtilities;
		
		$this->stringsLocalized = $this->utils->getStringsLocalized();
		
		$this->user =& JFactory::getUser();
	}
	
	function __destruct() {
		unset($this->joomlaDb);
	}
	
	function getTeamAndLeagueInfo() {
		$sql = "SELECT TL.leagueId, L.name as leagueName, TL.strName as teamName, TL.coachId, 
			TL.teamHash as teamId, L.FullControl 
			FROM #__bbla_Team_Listing TL INNER JOIN #__bbla_League L ON TL.leagueId = L.ID
			INNER JOIN #__bbla_Player_Listing PL ON TL.teamHash = PL.teamHash
			WHERE playerHash = '".$this->playerId."'";

		$this->joomlaDb->setQuery($sql);
		return ($this->joomlaDb->loadAssoc());
	}
	
	function getPlayerInfo() {
		$team = $this->getTeamAndLeagueInfo();
		
		$teamHash = $team['teamId'];
		$sql = "SELECT PL.*, PL.strName, PL.Characteristics_fMovementAllowance MA,PL.Characteristics_fStrength ST, " .
			" PL.Characteristics_fAgility AG, PL.Characteristics_fArmourValue AV, PT.idStrings_Localized AS positionId, PL.teamHash " .
			" FROM #__bbla_Player_Listing PL INNER JOIN #__bbla_Player_Types PT ON PL.idPlayer_Types = PT.ID " .
			" WHERE PL.teamHash = '".$teamHash."' AND bRetired <> 1" .
			" ORDER BY iNumber";
		
		//var_dump($this->joomlaDb);die();
		
		$this->joomlaDb->setQuery($sql);
		$fullRoster = $this->joomlaDb->loadAssocList();

		foreach ($fullRoster as $index => $player) {
			if ($player['playerHash'] == $this->playerId) {
				$playerDetails = $this->processPlayer($player);
				$index > 0 ? $previous = $fullRoster[$index-1]['playerHash'] : $previous = null;
				$index < count($fullRoster)-1 ? $next = $fullRoster[$index+1]['playerHash'] : $next = null;
				$skillCategories = $this->getPlayerSkillCategoriesWithSkills($player['idPlayer_Types']);
			}
		}
		$playerDetails['coachId'] = $team['coachId'];
				
		$returnStruct = array(
			"playerDetails" => $playerDetails, 
			"skillCategories" => $skillCategories,
			"previous" => $previous, 
			"next" => $next);
		return $returnStruct;
	}
	
	function processPlayer($playerArray) {
		$getSkills = $this->getPlayerSkillsInjuries($playerArray['playerHash'], $playerArray['idPlayer_Types']);
		$playerArray['DefaultSkills'] = $getSkills['default'];
		$playerArray['AcquiredSkills'] = $getSkills['acquired'];
		$playerArray['Injuries'] = $getSkills['injuries'];
		
		//convert player attributes 
		$playerArray['MA'] = $this->utils->convertMA($playerArray['MA']);
		$playerArray['ST'] = $this->utils->convertST($playerArray['ST']);
		$playerArray['AG'] = $this->utils->convertAG($playerArray['AG']);
		$playerArray['AV'] = $this->utils->convertAV($playerArray['AV']);
		
		//check player attributes against defaults and set color
		$attributes = $getSkills['attributes'];
		$playerArray['MAcolor'] = "";
		$playerArray['STcolor'] = "";
		$playerArray['AGcolor'] = "";
		$playerArray['AVcolor'] = "";
		
		if ($this->utils->convertMA($attributes[0]['MA']) < $playerArray['MA']) {
			$playerArray['MAcolor'] = "bonus";
		} else if ($this->utils->convertMA($attributes[0]['MA']) > $playerArray['MA']) {
			$playerArray['MAcolor'] = "penalty";
		}
		if ($this->utils->convertST($attributes[0]['ST']) < $playerArray['ST']) {
			$playerArray['STcolor'] = "bonus";
		} else if ($this->utils->convertST($attributes[0]['ST']) > $playerArray['ST']) {
			$playerArray['STcolor'] = "penalty";
		}
		if ($this->utils->convertAG($attributes[0]['AG']) < $playerArray['AG']) {
			$playerArray['AGcolor'] = "bonus";
		} else if ($this->utils->convertAG($attributes[0]['AG']) > $playerArray['AG']) {
			$playerArray['AGcolor'] = "penalty";
		}
		if ($this->utils->convertAV($attributes[0]['AV']) < $playerArray['AV']) {
			$playerArray['AVcolor'] = "bonus";
		} else if ($this->utils->convertAV($attributes[0]['AV']) > $playerArray['AV']) {
			$playerArray['AVcolor'] = "penalty";
		}
		
		return $playerArray;
	}
	

	
	function getPlayerSkillsInjuries($playerHash, $playerType) {
		$skills = array();
		// default skills
		$sql = "SELECT idSkill_Listing as ID, sl.DESCRIPTION as description, loc.English as name
			FROM #__bbla_Player_Type_Skills pts INNER JOIN #__bbla_Skill_Listing sl ON pts.idSkill_Listing = sl.ID
			INNER JOIN #__bbla_Strings_Localized loc ON sl.idStrings_Localized = loc.ID
			WHERE pts.idPlayer_Types = '" . $playerType . "'";
		$this->joomlaDb->setQuery($sql);
		$skills['default'] = $this->joomlaDb->loadAssocList('ID');
		
		// acquired skills
		$sql = "SELECT idSkill_Listing as ID, sl.DESCRIPTION as description, loc.English as name
			FROM #__bbla_Player_Skills ps INNER JOIN #__bbla_Skill_Listing sl ON ps.idSkill_Listing = sl.ID
			INNER JOIN #__bbla_Strings_Localized loc ON sl.idStrings_Localized = loc.ID
			WHERE playerHash = '" . $playerHash . "'";
		$this->joomlaDb->setQuery($sql);
		$skills['acquired'] = $this->joomlaDb->loadAssocList('ID');
		
		// injuries
		$sql = "SELECT idPlayer_Casualty_Types FROM #__bbla_Player_Casualties WHERE playerHash = '" . $playerHash . "'";
		$this->joomlaDb->setQuery($sql);
		$skills['injuries'] = $this->joomlaDb->loadAssocList();
		
		// default attributes
		$sql = "SELECT Characteristics_fMovementAllowance MA, Characteristics_fStrength ST,
				Characteristics_fAgility AG, Characteristics_fArmourValue AV FROM #__bbla_Player_Types WHERE ID = '" . $playerType . "'";
		$this->joomlaDb->setQuery($sql);
		$skills['attributes'] = $this->joomlaDb->loadAssocList();
		
		return $skills;
	}
	
	function getPlayerSkillCategoriesWithSkills($playerType) {
		$struct = array();
		
		$sql = "SELECT CONSTANT as Category,sl.ID as skillId, English as skillName, 
				sl.DESCRIPTION as description
			FROM #__bbla_Player_Types pt INNER JOIN #__bbla_Player_Type_Skill_Categories_Normal ptscn ON pt.ID = ptscn.idPlayer_Types 
			INNER JOIN #__bbla_Skill_Categories sc ON ptscn.idSkill_Categories = sc.ID
			INNER JOIN #__bbla_Skill_Listing sl ON sc.ID = sl.idSkill_Categories
			INNER JOIN #__bbla_Strings_Localized loc ON sl.idStrings_Localized = loc.ID
			WHERE pt.ID = '".$playerType."'".
			"ORDER BY sl.idSkill_Categories, English";

		$this->joomlaDb->setQuery($sql);
		$struct['normal'] = $this->joomlaDb->loadAssocList();
		
		$sql = "SELECT CONSTANT as Category,sl.ID as skillId, English as skillName, 
				sl.DESCRIPTION as description
			FROM #__bbla_Player_Types pt INNER JOIN #__bbla_Player_Type_Skill_Categories_Double ptscn ON pt.ID = ptscn.idPlayer_Types 
			INNER JOIN #__bbla_Skill_Categories sc ON ptscn.idSkill_Categories = sc.ID
			INNER JOIN #__bbla_Skill_Listing sl ON sc.ID = sl.idSkill_Categories
			INNER JOIN #__bbla_Strings_Localized loc ON sl.idStrings_Localized = loc.ID
			WHERE pt.ID = '".$playerType."'".
			"ORDER BY sl.idSkill_Categories, English";

		$this->joomlaDb->setQuery($sql);
		$struct['doubles'] = $this->joomlaDb->loadAssocList();
		
		$sql = "SELECT 'Increase' as Category, sl.ID as skillId, English as skillName
				FROM #__bbla_Skill_Listing sl INNER JOIN #__bbla_Strings_Localized loc ON sl.idStrings_Localized = loc.ID
				WHERE idSkill_Categories = ''";

		$this->joomlaDb->setQuery($sql);
		$struct['attributes'] = $this->joomlaDb->loadAssocList();
		
		return $struct;
	}
	
	function getPlayerSkillCategories($playerType) {
		$struct = array();
		$sql = "SELECT CONSTANT as Category	FROM #__bbla_Player_Type_Skill_Categories_Normal ptscn 
			INNER JOIN #__bbla_Skill_Categories sc ON ptscn.idSkill_Categories = sc.ID
			WHERE ptscn.idPlayer_Types = '".$playerType."'".
			" ORDER BY Category ";
		
		$this->joomlaDb->setQuery($sql);
		$struct['normal'] =	$this->joomlaDb->loadAssocList();
		
		$sql = "SELECT CONSTANT as Category	FROM #__bbla_Player_Type_Skill_Categories_Double ptscd 
			INNER JOIN #__bbla_Skill_Categories sc ON ptscd.idSkill_Categories = sc.ID
			WHERE ptscd.idPlayer_Types = '".$playerType."'".
			"ORDER BY Category";
		$this->joomlaDb->setQuery($sql);
		$struct['doubles'] = $this->joomlaDb->loadAssocList();
		
		return $struct;
	}
	
	function addSkill() {
		$playerInfo = $this->getPlayerInfo();
		$playerDetails = $playerInfo['playerDetails'];
		//get current attribute values
		$ST = $playerDetails['ST'];
		$AG = $playerDetails['AG'];
		$MA = $playerDetails['MA'];
		$AV = $playerDetails['AV'];
		
		//determine what type of skill (normal/double/attribute) based on playerType so that
		//we know how much to add to the player value
		//grab skill category
		$sql = "SELECT idSkill_Categories cat FROM #__bbla_Skill_Listing WHERE ID = ".$_POST['skillId'];

		$this->joomlaDb->setQuery($sql);
		$skillCat = $this->joomlaDb->loadResult();
		
		//if category is blank, we're dealing with an attribute increase
		if ($skillCat == "") {
			switch ($_POST['skillId']) {
				case 2: //ST
					$playerValueIncrease = 50;
					$ST = $ST+1;
					break;
				case 3: //AG
					$playerValueIncrease = 40;
					$AG = $AG+1;
					break;
				case 4: //MA
					$playerValueIncrease = 30;
					$MA = $MA+1;
					break;
				case 5: //AV
					$playerValueIncrease = 30;
					$AV = $AV+1;	
			}

		} else {
			//now check the Normal Table for playerType/Skill
			$sql = "SELECT * FROM #__bbla_Player_Type_Skill_Categories_Normal" .
				" WHERE idPlayer_Types =".$_POST['playerType'].
				" AND idSkill_Categories =".$skillCat;

			$this->joomlaDb->setQuery($sql);
			$normal = $this->joomlaDb->loadAssocList();
			
			$this->utils->do_dump($normal, '$normal');
			
			if ($normal != false) {
				$playerValueIncrease = 20;
			} else {
				//now check the Double Table for playerType/Skill
				$sql = "SELECT * FROM #__bbla_Player_Type_Skill_Categories_Double" .
					" WHERE idPlayer_Types =".$_POST['playerType'].
					" AND idSkill_Categories =".$skillCat;

				$this->joomlaDb->setQuery($sql);
				$double = $this->joomlaDb->loadAssocList();
				
				if ($double != false) {
					$playerValueIncrease = 30;
				} else {
					//nothing found, this is an error
				}
			}			
		}
		
		//convert attributes back to percentage values for insertion later
		$ST = $this->utils->setSTpercent($ST);
		$AG = $this->utils->setAGpercent($AG);
		$MA = $this->utils->setMApercent($MA);
		$AV = $this->utils->setAVpercent($AV);
						
		//add skill
		$sql = "INSERT INTO #__bbla_Player_Skills (idSkill_Listing,playerHash) VALUES(" .
			$_POST['skillId'].",'".$this->playerId."')";
		
		$this->joomlaDb->setQuery($sql);
		$this->joomlaDb->query();
		
		//check if skill dice need to be re-rolled for a multi-level gain
		$reRoll = false;
		$LevelUp_iRollResult = 0;
		$LevelUp_iRollResult2 = 0;
		$LevelUp_bDouble = 0;
		
		if ($playerDetails['iNbLevelsUp'] > 1) {
			$LevelUp_iRollResult = rand(1,6);
			$LevelUp_iRollResult2 = rand(1,6);
			if ($LevelUp_iRollResult == $LevelUp_iRollResult2) {
				$LevelUp_bDouble = 1;
			}
		}
		
		//update player record
		$sql = "UPDATE #__bbla_Player_Listing SET" .
			" Characteristics_fMovementAllowance = ".$MA."," .
			" Characteristics_fStrength = ".$ST."," .
			" Characteristics_fAgility = ".$AG."," .
			" Characteristics_fArmourValue = ".$AV."," .
			" idPlayer_Levels = idPlayer_Levels + 1," .
			" iValue = iValue + ".$playerValueIncrease."," .
			" iNbLevelsUp = iNbLevelsUp - 1," .
			" LevelUp_iRollResult = ".$LevelUp_iRollResult."," .
			" LevelUp_iRollResult2 = ".$LevelUp_iRollResult2."," .
			" LevelUp_bDouble = ".$LevelUp_bDouble.
			" WHERE playerHash = '".$this->playerId."'";
		
		$this->joomlaDb->setQuery($sql);
		$this->joomlaDb->query();

		//update team value
		$this->utils->updateTeamValue($playerDetails['teamHash']);
	}
	
	function changePlayerAttributes() {
		$sql = "UPDATE #__bbla_Player_Listing SET " .
			"strName = ".$this->joomlaDb->quote($_POST['strName']).",".
			"iNumber = '".$_POST['iNumber']."',".
			"iSkinTextureVariant = '".$_POST['iSkinTextureVariant']."'" .
			" WHERE playerHash = '".$_POST['playerId']."'";

		$this->joomlaDb->setQuery($sql);
		$this->joomlaDb->query();
	}
	
	function firePlayer() {
		$teamInfo = $this->getTeamAndLeagueInfo();
		$teamId = $teamInfo['teamId'];
		$coachId = $teamInfo['coachId'];
		
		//if userID and CoachId don't match
		if ($this->user->id != $coachId) {
			return array("result" => "error");
		} else {
			//retire player
			$sql = "UPDATE #__bbla_Player_Listing SET bRetired = 1 WHERE playerHash = '".$this->playerId."'";
			$this->joomlaDb->setQuery($sql);
			$this->joomlaDb->query();
			
			//run Journeyman routine to see if journeymenHireFire flag needs to be reset
			$this->processJourneymen($teamId);
			
			//finally, update the team value based on the firing
			$this->utils->updateTeamValue($teamId);
			
			return array("result" => "success", "teamId" => $teamId);
		}
	}

	function hireJourneyman() {
		$teamInfo = $this->getTeamAndLeagueInfo();
		$teamId = $teamInfo['teamId'];
		$coachId = $teamInfo['coachId'];
		$hireMsg = array();
		
		//if userID and CoachId don't match
		if ($this->user->id != $coachId) {
			return array("result" => "error");
		} else {
			//retrieve player name and value
			$sql = "SELECT strName, iValue FROM #__bbla_Player_Listing 
				WHERE playerHash = '".$this->playerId."'";
			$this->joomlaDb->setQuery($sql);
			$qry = $this->joomlaDb->loadAssoc();
			$name = $qry['strName'];
			$cost = $qry['iValue']*1000;
			
			//remove Journeyman from Player Name
			if (strpos($name, 'Journeyman') !== false) {
				$name = trim(str_replace("Journeyman", "", $name));
			}
			
			//update name and remove Journeyman flag
			$sql = "UPDATE #__bbla_Player_Listing SET strName = ".$this->joomlaDb->quote($name).", journeyman = 0 
				WHERE playerHash = '".$this->playerId."'";
			$this->joomlaDb->setQuery($sql);
			$this->joomlaDb->query();
			
			//remove Loner skill
			$sql = "DELETE FROM #__bbla_Player_Skills 
				WHERE playerHash = '".$this->playerId."' AND idSkill_Listing = 44";
			$this->joomlaDb->setQuery($sql);
			$this->joomlaDb->query();
			
			//run Journeyman routine to see if journeymenHireFire flag needs to be reset
			$this->processJourneymen($teamId);
			
			//deduct gold from treasury
			$sql="UPDATE #__bbla_Team_Listing SET iCash = iCash - ".$cost." 
				WHERE teamHash = '".$teamId."'";
			$this->joomlaDb->setQuery($sql);
			$this->joomlaDb->query();
			
			$hireMsg[] = "Journeyman was hired.";
			$hireMsg[] = "<br/>";
			$hireMsg[] = number_format($cost)." gold was deducted from your treasury.";
			
			//finally, update the team value based on the firing
			$this->utils->updateTeamValue($teamId);
			
			return array("result" => "success", "teamId" => $teamId, "msg" => $hireMsg);
		}
	}
	
	function processJourneymen($teamId) {
		//grab a count of the journeymen on the team
		$sql = "SELECT count(*) as journeyman FROM #__bbla_Player_Listing WHERE teamHash = '".$teamId."' AND journeyman = 1 AND bRetired = 0";
		$this->joomlaDb->setQuery($sql);
		$journeyman = $this->joomlaDb->loadResult();

		//if no Journeyman are found, reset the postMatch flag
		if ($journeyman == 0) {
			$sql = "UPDATE Team_Listing set journeymenHireFire = 0 WHERE teamHash = '".$teamId."'";
			$this->joomlaDb->setQuery($sql);
			$this->joomlaDb->query();
		}
	}
	
	function getPlayerTypeDetails($playerType) {
		$positionCosts = array();
		$sql = "SELECT PT.*, SL.English AS position FROM #__bbla_Player_Types PT 
			INNER JOIN #__bbla_Strings_Localized SL ON PT.idStrings_Localized = SL.ID
			WHERE PT.ID = ".$playerType;
		$this->joomlaDb->setQuery($sql);
		$qry = $this->joomlaDb->loadAssoc();
		
		$sql = "SELECT ID, idEquipment_Types FROM #__bbla_Equipment_Listing
			WHERE idPlayer_Levels = 1 AND idPlayer_Types = ".$playerType.
			" ORDER BY idEquipment_Types";
		$this->joomlaDb->setQuery($sql);
		$equipment = $this->joomlaDb->loadAssocList();
		
		$positionCosts['position'] = $qry['position'];
		$positionCosts['cost'] = $qry['iPrice'];
		$positionCosts['playerAttributes'] = $qry;
		$positionCosts['playerAttributes']['equipment'] = $equipment;
		
		return $positionCosts;
	}
	
	function getAvailablePlayerNumbers($teamId) {	
		//retrieve player numbers
		$sql = "SELECT iNumber FROM #__bbla_Player_Listing WHERE teamHash = '".$teamId."' AND bRetired <> 1 ORDER BY iNumber";
		$this->joomlaDb->setQuery($sql);
		$numbers = $this->joomlaDb->loadAssocList();
		
		$availableNumbers = array();
		$numberCheck = 1;  //player numbers start at 1
		
		while ($numberCheck <= 32) {
			$availableNumbers[] = $numberCheck;
			$numberCheck++;
		}
		//var_dump($availableNumbers);
		for ($i=count($numbers)-1; $i >= 0 ; $i--) {
			//var_dump($numbers[$i]['iNumber']);
			unset($availableNumbers[$numbers[$i]['iNumber']-1]); 
			//var_dump($availableNumbers);
		}
		/*
		//loop through player numbers up to 32
		while ($numberCheck <= 32) {
			//find an available player number
			//while ($numberCheck == $numbers[$iterator]['iNumber']) {
			for ($i=0; $i < count($numbers); $i++) {
				echo $numberCheck.'=='.$numbers[$i]['iNumber'].'<br/>';
				if ($numberCheck == $numbers[$i]['iNumber']) {
					$numberCheck++;	
				} else {
					$availableNumbers[] = $numberCheck;
					$numberCheck++;
					$i++;
					$iterator = $i;
					//break;
				}
				
				//var_dump($iterator);
				//var_dump(count($numbers));
				//if ($iterator >= count($numbers)) break;
			}
			$iterator++;
			//echo $numberCheck."<br/>";
			var_dump($availableNumbers);
		}
		*/
		
		return $availableNumbers;
	}
}

