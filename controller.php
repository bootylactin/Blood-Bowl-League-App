<?php
/**
 * @license    GNU/GPL
 */
 
// No direct access
 
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport('joomla.application.component.controller');

header('Content-Type: text/html; charset=utf-8');
 
/**
 * BBQL Component Controller
 *
 */
class BbqlController extends JController
{
    /**
     * Method to display the view
     *
     * @access    public
     */
    function display()
    {
    	$viewName = JRequest::getVar('view', 'bbql');
    	
    	$view = &$this->getView($viewName, 'html');
    	
    	//if loading the statistics view...
    	switch ($viewName) {
    		case 'leagueLeaders':
		    	//grab the team model for use
		    	$model = &$this->getModel( 'league' );
				$view->setModel($model);
				break;	
    		case 'statistics':
		    	//grab the team model for use
		    	$teamModel = &$this->getModel( 'team' );
				$view->setModel($teamModel);
				break;	
			case 'team':
				$model = &$this->getModel('bbql');
				$view->setModel($model);
				break;
			case 'postMatch':
				$leagueModel = &$this->getModel('league');
				$view->setModel($leagueModel);
				break;
			case 'player':
				$teamModel = &$this->getModel( 'team' );
				$view->setModel($teamModel);
				break;
    	}
    	
        parent::display();
	}
	
	function joinTeamToLeague() {
		$leagueModel = &$this->getmodel('league');
		$league = $leagueModel->getLeagueById($_POST['leagueId']);
		$model = &$this->getModel('upload');
		
		$teamId = $model->uploadTeamFile("joinLeague", $league[0]['Password'], $league[0]['FullControl']);

		if (strLen($teamId)>10) {
			$return = "index.php?option=com_bbql&view=team&teamId=".$teamId."&leagueId=".$_POST['leagueId'];
			$this->setRedirect( $return, "Your team was uploaded successfully!  You have joined this league." );
		} else {
			$return = "index.php?option=com_bbql&view=league&leagueId=".$_POST['leagueId'];
			if ($teamId == 0) {
				$this->setRedirect($return, "This team is part of another league, OR your team has an identical name to another in the system.  Teams can only be part of one league at any given time.", "error");
			} else if ($teamId == -1) {
				$this->setRedirect($return, "The password you entered is incorrect.", "error");
			} else if ($teamId == -2) {
				$this->setRedirect($return, "You have attempted to upload a non-team file.", "error");
			} else if ($teamId == -3) {
				$this->setRedirect($return, "You have attempted to upload an online team.  Only single-player generated teams are allowed in leagues fully controlled by this application.", "error");
			} else if ($teamId == -4) {
				$this->setRedirect($return, "The league application is down for maintenance.  Team uploads are not permitted at the moment.", "error");
			}	
		}
	}
	
	
	function updateExistingTeam() {
		$model = &$this->getModel('upload');
		$teamId = $model->uploadTeamFile("existing", "", 0);
		
		if (strLen($teamId)>10) {
			$return = "index.php?option=com_bbql&view=team&teamId=".$teamId."&leagueId=".$_POST['leagueId'];
			$this->setRedirect( $return, "Your team was updated successfully!" );
		} else {
			$return = "index.php?option=com_bbql&view=league&leagueId=".$_POST['leagueId'];
			if ($teamId == 0){
				$this->setRedirect($return, "The team you attempted to update is not part of this league.", "error");
			} else if ($teamId == -2) {
				$this->setRedirect($return, "You have attempted to upload a non-team file.", "error");
		 	} else if ($teamId == -3) {
				$this->setRedirect($return, "You have attempted to upload an online team.  Only single-player generated teams are allowed in leagues fully controlled by the applications.", "error");
			}	
		}
	}
	
	function uploadMatchReport() {		
		$model = &$this->getModel('upload');
		$return = $model->uploadMatchReport(false);

		if ($return['result'] == "success") {
			if ($return['fullControl'] == "1") {
				$redirect = "index.php?option=com_bbql&view=postMatch&leagueId=".$_POST['leagueId']."&matchId=".$return['status'];
			} else {
				$redirect = "index.php?option=com_bbql&view=league&leagueId=".$_POST['leagueId'];
			}
			$this->setRedirect( $redirect, "Your match result has been submitted successfully!" );
		} else {
			$redirect = "index.php?option=com_bbql&view=league&leagueId=".$_POST['leagueId'];
			switch ($return['status']) {
				case -1:
					$this->setRedirect( $redirect, "This match report file does not correlate to any unplayed game in the schedule.", "error");
					break;
				case -2:
					$this->setRedirect( $redirect, "You attempted to upload the wrong type of file.  You may only upload MatchReport.sqlite files.", "error");
					break;
				case -3:
					$this->setRedirect( $redirect, "This match appears to be a duplicate of a previously uploaded match.  Submission aborted.", "error");
					break;
				case -4:
					$this->setRedirect($redirect, "The league application is down for maintenance.  Match Report uploads are not permitted at the moment.", "error");
					break;
			}
		}
	}
	function updateTeamValues() {
		$model = &$this->getModel('utilities');
		$model->updateTeamValues();
	}
	
	function revertMatchReport() {		
		$model = &$this->getModel('upload');
		$return = $model->uploadMatchReport(true);

		if ($return['result'] == "success") {
			if ($return['fullControl'] == "1") {
				$redirect = "index.php?option=com_bbql&view=postMatch&leagueId=".$_POST['leagueId']."&matchId=".$return['status'];
			} else {
				$redirect = "index.php?option=com_bbql&view=league&leagueId=".$_POST['leagueId'];
			}
			$this->setRedirect( $redirect, "Your match reversion has been submitted successfully!" );
		} else {
			$redirect = "index.php?option=com_bbql&view=league&leagueId=".$_POST['leagueId'];
			if ($return['status'] == -1) {
				$this->setRedirect( $redirect, "This match report file does not correlate to any previously played game in the schedule.", "error");
			} else {
				$this->setRedirect( $redirect, "You attempted to upload the wrong type of file.  You may only upload MatchReport.sqlite files.", "error");
			}
		}
	}
	
	function bbLog() {
		global $systemPathToComponent, $httpPathToComponent;
		$log = JRequest::getVar('log');
		if($log == "") $log = 266;
		// include the utilities class
		include_once($httpPathToComponent.DS.'models'.DS.'utilities.php');
		$utils = new BbqlModelUtilities;

		$txt = file_get_contents($systemPathToComponent.'\uploads\19\\'.$log.'.log');
		
		//find start of match
		$matchStart = strpos($txt, 'Enter CStateMatchTossChooseGob');
		$str = array();
		
		if ($matchStart === false) {
			//return with error, match start not found
		} else {
			//get team names and match to current league
			$str['matchStart'] = $matchStart;
			$str[1] = substr($txt, $matchStart, strpos($txt, chr(13).chr(10), $matchStart)-$matchStart);
			$str[2] = strpos($txt, chr(13).chr(10), $matchStart);
			$str[3] = strpos($txt, ":", $matchStart);
			$str[4] = strpos($txt, ") vs ", $str[3]);
			$str['awayTeam'] = trim(substr($txt, $str[3]+1, $str[4]+1-$str[3]));
			$str['awayTeamAbr'] = substr($str['awayTeam'], -5);
			$str[6] = strpos($txt, chr(13).chr(10), $str[4]);
			$str['homeTeam'] = trim(substr($txt, $str[4]+5, $str[6]-($str[4]+5)));
			$str['homeTeamAbr'] = substr($str['homeTeam'], -5);
			$str['matchEnd'] = strpos($txt, "Enter CStateMatchEnd", $str[6]);
			$str['casCount'] = substr_count($txt, '(Casualty)', $str['matchStart'], $str['matchEnd']-$str['matchStart']);
			$this->getLine($txt, $str[6]);
			
		}
		$utils->dump($str);
		die();
	}
	
	function getLine($str, $pos) {
		//find end of line
		$endOfLine = strpos($str, chr(13).chr(10), $pos);
		//seed the substring
		$i = 1;
		$sub = substr($str, $endOfLine-$i, $i);
		//loop backward through $str until previous line break is found
		while(strpos($sub, chr(13).chr(10)) === false) {
			$i++;
			$sub = substr($str, $endOfLine-$i, $i);
		}
		die($sub);
	}
	
	function reRollWinnings() {
		$model = &$this->getModel('league');
		$match = $model->reRollWinnings();
		
		$return = "index.php?option=com_bbql&view=postMatch&leagueId=".JRequest::getVar('leagueId')."&matchId=".JRequest::getVar('matchId');
		$this->setRedirect( $return, "Winnings finalized." );
	}
	
	function addSkill() {
		$model = &$this->getModel('player');
		$model->addSkill();
		
		$return = "index.php?option=com_bbql&view=player&playerId=".$_POST['playerId'];
		$this->setRedirect( $return, "Player skill/attribute was added successfully!" );
	}
	
	function changePlayerAttributes() {
		$model = &$this->getModel('player');
		$model->changePlayerAttributes();
		
		$return = "index.php?option=com_bbql&view=player&playerId=".$_POST['playerId'];
		$this->setRedirect( $return, "Player attributes were modified successfully!" );
	}
	
	function firePlayer() {
		$model = &$this->getModel('player');
		$return = $model->firePlayer();

		if ($return['result'] == "success") {
			$redirect = "index.php?option=com_bbql&view=team&teamId=".$return['teamId'];
			$this->setRedirect( $redirect, "Player was fired." );
		} else {
			$redirect = "index.php?option=com_bbql";
			$this->setRedirect( $redirect, "You are not the coach of this player, nice try!", "error");
		}
	}
	
	function hireJourneyman() {
		$model = &$this->getModel('player');
		$return = $model->hireJourneyman();
		$msg = "";
		foreach ($return['msg'] as $messages) {
			$msg = $msg."<li>".$messages."</li>";
		}
		if ($return['result'] == "success") {
			$redirect = "index.php?option=com_bbql&view=team&teamId=".$return['teamId'];
			$this->setRedirect( $redirect, $msg );
		} else {
			$redirect = "index.php?option=com_bbql";
			$this->setRedirect( $redirect, "You are not the coach of this player, nice try!", "error");
		}
	}
	
	function purchaseTeamItems() {
		$model = &$this->getModel('team');
		$return = $model->purchaseTeamItems();
		
		$msg = "";
		foreach ($return['msg'] as $messages) {
			$msg = $msg."<li>".$messages."</li>";
		}
		
		if ($return['result'] == "success") {
			$redirect = "index.php?option=com_bbql&view=team&teamId=".$return['teamId'];
			$this->setRedirect( $redirect, $msg);
		} else {
			$redirect = "index.php?option=com_bbql&view=team&teamId=".$return['teamId'];
			$this->setRedirect( $redirect, $msg, "error");
		}
	}
	
	function purchasePlayers() {
		$model = &$this->getModel('team');
		$return = $model->purchasePlayers();
		
		$msg = "";
		foreach ($return['msg'] as $messages) {
			$msg = $msg."<li>".$messages."</li>";
		}
		
		if ($return['result'] == "success") {
			$redirect = "index.php?option=com_bbql&view=team&teamId=".$return['teamId'];
			$this->setRedirect( $redirect, $msg);
		} else {
			$redirect = "index.php?option=com_bbql&view=team&teamId=".$return['teamId'];
			$this->setRedirect( $redirect, $msg, "error");
		}
	}
	
	function receiveJourneymen() {
		$model = &$this->getModel('team');
		$return = $model->receiveJourneymen();
		
		$msg = "";
		foreach ($return['msg'] as $messages) {
			$msg = $msg."<li>".$messages."</li>";
		}
		
		if ($return['result'] == "success") {
			$redirect = "index.php?option=com_bbql&view=team&teamId=".$return['teamId'];
			$this->setRedirect( $redirect, $msg);
		} else {
			$redirect = "index.php?option=com_bbql&view=team&teamId=".$return['teamId'];
			$this->setRedirect( $redirect, $msg, "error");
		}
	}
	
	function downloadTeam() {
		$model = &$this->getModel('team');
		$filePath = $model->downloadTeam();
		
		if (file_exists($filePath) && filesize($filePath) > 0) {
			header('Content-Description: File Transfer');
		    header('Content-Type: application/octet-stream');
		    header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
		    header('Content-Transfer-Encoding: binary');
		    header('Expires: 0');
		    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		    header('Pragma: public');
		    header('Content-Length: ' . filesize($filePath));
		    ob_clean();
		    flush();
		    readfile($filePath);
		    exit;
		} else {
			$redirect = "index.php?option=com_bbql&view=team&teamId=".JRequest::getVar('teamId');
			$this->setRedirect( $redirect, "Error downloading team.", "error");
		}
	}
        
        function recalculateTeamValue() {
		$model = &$this->getModel('team');
		$return = $model->recalculateTeamValue();
		
		$msg = "";
		foreach ($return['msg'] as $messages) {
			$msg = $msg."<li>".$messages."</li>";
		}
		
		if ($return['result'] == "success") {
			$redirect = "index.php?option=com_bbql&view=team&teamId=".$return['teamId'];
			$this->setRedirect( $redirect, $msg);
		} else {
			$redirect = "index.php?option=com_bbql&view=team&teamId=".$return['teamId'];
			$this->setRedirect( $redirect, $msg, "error");
		}
	}
	
	function createLeague() {
		$model = &$this->getModel('league');
		$teamId = $model->createLeague();
		
		$return = "index.php?option=com_bbql";
		$this->setRedirect( $return, "Your league was created successfully!" );
	}
	
	function createSchedule() {
		$model = &$this->getModel('scheduler');
		$model->createLeagueSchedule();
		
		$return = "index.php?option=com_bbql&view=league&leagueId=".$_POST['leagueId'];
		$this->setRedirect( $return, "Your league schedule was created successfully!" );
	}
	
	function resetLeague() {
		$model = &$this->getModel('league');
		$model->resetLeague();
		
		$return = "index.php?option=com_bbql&view=league&leagueId=".JRequest::getVar('leagueId');
		$this->setRedirect( $return, "Your league was reset." );
	}
	
	function deleteLeague() {
		$model = &$this->getModel('league');
		$model->deleteLeague();
		
		$return = "index.php?option=com_bbql";
		$this->setRedirect( $return, "League was deleted." );
	}
	
	function changeCoach() {
		$model = &$this->getModel('league');
		$return = $model->changeCoach();
		
		$redirect = "index.php?option=com_bbql&view=league&leagueId=".JRequest::getVar('leagueId');
		
		if ($return['result'] == "success") {
			$this->setRedirect( $redirect, "Coaching change was successful.");
		} else {
			$this->setRedirect( $redirect, "There was an error.  Please make sure both a team and a replacement coach are selected.", "error");
		}
	}
}
