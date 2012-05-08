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

class BbqlViewPostMatch extends JView
{
    function display($tpl = null)
    {
    	global $userList;
		$leagueModel =& $this->getModel('league');
		$league =& $leagueModel->getLeagueById();
		$schedule = & $leagueModel->getSchedule();
		$matchReport = & $leagueModel->getMatchReport(JRequest::getVar('matchId'));
		
		$this->assignRef( 'schedule', $schedule );
		$this->assignref( 'matchReport', $matchReport );
		$this->assignRef( 'league', $league );
		$this->assignRef( 'userList', $userList );
		$this->assignRef( 'leagueId', $leagueModel->leagueId);
		$this->assignRef( 'matchId', JRequest::getVar('matchId'));
		$this->assignRef( 'leagueName', $league[0]['Name']);
		
        parent::display($tpl);
    }
}
