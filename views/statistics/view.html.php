<?php
/*
 * @license    GNU/GPL
*/
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');
 
/*
 * HTML View class for the Team Component
 */

class BbqlViewStatistics extends JView {
    function display($tpl = null) {
    	
		$teamModel =& $this->getModel('team');

		//populate data
		$stringsLocalized =& $teamModel->stringsLocalized;
		$playerStats =& $teamModel->getPlayerStats();
		$teamStats =& $teamModel->getTeamStats();
		$teamInfo =& $teamModel->getTeamInfo();
		$info =& $teamModel->getLeagueInfo();
		
		$this->assignRef( 'stringsLocalized', $stringsLocalized );
        $this->assignRef( 'playerStats', $playerStats );
        $this->assignRef( 'teamStats', $teamStats );
        $this->assignRef( 'teamInfo', $teamInfo );
        $this->assignRef( 'teamId', $teamModel->teamId);
        $this->assignRef( 'leagueId', $info['leagueId']);
        $this->assignRef( 'leagueName', $info['leagueName']);

        parent::display($tpl);
    }
}
