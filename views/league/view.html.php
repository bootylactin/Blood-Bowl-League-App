<?php
/*
 * @license    GNU/GPL
*/
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');
 
/*
 * HTML View class for the BBQL Component
 */

class BbqlViewLeague extends JView
{
    function display($tpl = null)
    {
    	global $userList;
		$leagueModel =& $this->getModel();
		
		$league =& $leagueModel->getLeagueById();
		
		if ($league[0]['StatusId'] == 1) {
			$teams =& $leagueModel->getTeams();
			$this->assignRef( 'teams', $teams );
		} else {
			$standings =& $leagueModel->getStandings(); 
			$schedule = & $leagueModel->getSchedule();
			$this->assignRef( 'standings', $standings );
			$this->assignRef( 'schedule', $schedule );
		}

		$this->assignRef( 'coachesTimeZones', $leagueModel->coachesTimeZones );
		$this->assignRef( 'league', $league );
		$this->assignRef( 'userList', $userList );
		$this->assignRef( 'leagueId', JRequest::getVar('leagueId'));
		
        parent::display($tpl);
    }
}
