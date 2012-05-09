<?php
/**
 * Scheduler Model for BBQL Component
 * @license    GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.model' );

/**
 * BBQL Model
 */
class BbqlModelScheduler extends JModel {
	
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		
		global $httpPathToComponent, $bbqlDb;
		
		//$this->dbHandle = $bbqlDb;

		//include the bbql class
		include_once($httpPathToComponent.DS.'models'.DS.'league.php');
		$this->league = new BbqlModelLeague;
		$this->leagueId = $this->league->leagueId;
		$this->teams = $this->league->getTeams();
		//randomize teams
		shuffle($this->teams);

		$this->numberOfTeams = count($this->teams);
		
		$this->playNum = JRequest::getVar('playNum');
	}
	
	function __destruct() {
		unset($this->dbHandle);
	}
	
	function createLeagueSchedule() {
		$round = 0;
		//grab the schedule matrix
		$matrix = $this->makeScheduleMatrix($this->numberOfTeams);
		//randomize the schedule order
		shuffle($matrix);

		//set up loop for number of times teams will play each other
		for ($i=1; $i<=$this->playNum; $i++) {
			//loop through matrix array
			for ($j=0; $j<count($matrix); $j++) {
				//reset used indexes at the start of a row
				$usedIndexes = array();
				$round = $round + 1;
				//loop through matrix row
				for ($k=0; $k<count($matrix[$j]); $k++) {
					//check for bye
					if ($matrix[$j][$k] == "bye") {
						//do nothing
					//check usedIndexes array, skip if already used
					} else if ($usedIndexes[$k] != "used") { 
						//set used indexes
						$usedIndexes[$k] = "used";
						$usedIndexes[$matrix[$j][$k]] = "used";
						
						//grab team Ids
						$homeTeamHash = $this->teams[$k]['teamHash'];
						$awayTeamHash = $this->teams[$matrix[$j][$k]]['teamHash'];
						
						echo "Home Team ID=".$homeTeamHash;
						echo " - Away Team ID=".$awayTeamHash."<br/>";
						//add row to schedule
						$sql = "INSERT INTO Calendar (teamHash_Away,teamHash_Home,Championship_iSeason,Championship_iDay,leagueId) " . 
								"VALUES ('".$awayTeamHash."','".$homeTeamHash."','1','".$round."','".$this->leagueId."')";
						
						$this->dbHandle->query($sql);
					}
				}
			}
			echo "<br/>";
		}
		// set league to "In Progress"
		$sql = "UPDATE League SET statusId = 2, pointsForWin = " . $_POST['pointsForWin'] . " WHERE ID = " . $this->leagueId;
		$this->dbHandle->query($sql);
		
		// populate initial team statistics table with teams
		foreach($this->teams as $value) {
			$sql = "INSERT INTO Statistics_Season_Teams (teamHash,leagueId,iSeason,iMatchPlayed,iMVP,Inflicted_iPasses," .
					"Inflicted_iCatches,Inflicted_iInterceptions,Inflicted_iTouchdowns,Inflicted_iCasualties,Inflicted_iTackles," .
					"Inflicted_iKO,Inflicted_iInjuries,Inflicted_iDead,Inflicted_iMetersRunning,Inflicted_iMetersPassing," .
					"Sustained_iPasses,Sustained_iCatches,Sustained_iInterceptions,Sustained_iTouchdowns,Sustained_iCasualties," .
					"Sustained_iTackles,Sustained_iKO,Sustained_iInjuries,Sustained_iDead,Sustained_iMetersRunning," .
					"Sustained_iMetersPassing,iPoints,iWins,iDraws,iLoss,iBestMatchRating,Average_iMatchRating,Average_iSpectators," .
					"Average_iCashEarned,iSpectators,iCashEarned,iPossessionBall,Occupation_iOwn,Occupation_iTheir,touchdownDif)" .
					" VALUES ('".$value['teamHash']."',".$this->leagueId.",1,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0," .
							"0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0,0)";
			$this->dbHandle->query($sql);
		}

		return $matrix;
	}
	

	function makeScheduleMatrix($numberOfTeams) { // where n = number of teams
		
		if ($numberOfTeams%2 != 0) {
			//ODD NUMBER OF TEAMS ALGORITHM
			for ($r=0; $r<$numberOfTeams; $r++) {
				for ($i=0; $i<$numberOfTeams; $i++) {
					if ((($numberOfTeams + $r - $i - 1)%$numberOfTeams) == $i) {
						//tempArray[i] = ($numberOfTeams + r - i - 1 + shift)%$numberOfTeams
						$tempArray[$i] = 'bye';
					} else {
						$tempArray[$i] = ($numberOfTeams + $r - $i - 1)%$numberOfTeams;
					}
				}
				$oddMatrix[$r] = $tempArray;
			}
			return $oddMatrix;
		} else {
			//EVEN NUMBER OF TEAMS ALGORITHM	    
			for ($r=0; $r<$numberOfTeams-1; $r++) {
				for ($i=0; $i<$numberOfTeams; $i++) {
					if (($r == (2*$i + 1) % ($numberOfTeams - 1)) && ($i < ($numberOfTeams-1))) {
						$tempArray[$i] = ($numberOfTeams-1);
					} else if ($i == $numberOfTeams-1) {
						for ($p=0; $p<$numberOfTeams; $p++) {
							if (($r == (2*$p + 1) % ($numberOfTeams-1)) && ($p != $numberOfTeams-1)) {
								$tempArray[$i] = $p;
							}
						}          
					} else {
						$tempArray[$i] = (($numberOfTeams-1) + $r - $i - 1) % ($numberOfTeams-1);
					}
				}
				$evenMatrix[$r] = $tempArray;
			}
			return $evenMatrix;
		}
	}
}

