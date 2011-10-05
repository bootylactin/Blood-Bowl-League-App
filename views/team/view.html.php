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

class BbqlViewTeam extends JView
{
    function display($tpl = null)
    {
    	global $userList, $mainframe;
    	
		$model =& $this->getModel();
		$bbqlModel =& $this->getModel('bbql');
		
		//populate data
		$teamInfo = & $model->getTeamInfo();
		$stringsLocalized =& $model->stringsLocalized;
		$info =& $model->getLeagueInfo();

		$this->assignRef( 'stringsLocalized', $stringsLocalized );
        $this->assignRef( 'teamInfo', $teamInfo );
        $this->assignRef( 'userList', $userList );
        $this->assignRef( 'teamId', $model->teamId);
        $this->assignRef( 'leagueId', $info['leagueId']);
        $this->assignRef( 'leagueName', $info['leagueName']);
        $this->assignRef( 'FullControl', $info['FullControl']);
        $this->assignRef( 'PlayerPurchase', $model->getPlayersForPurchase() );
        //$this->assignRef( 'coachesTimeZones', $leagueModel->coachesTimeZones );
		
		if ($info['FullControl']) {
			if ($teamInfo['team']['journeymenHireFire']) {
				$mainframe->enqueueMessage("You must hire/fire any journeymen who played in the previous match.","notice");
			} else if ($teamInfo['team']['playersNeeded'] == 1) {
				$mainframe->enqueueMessage("Eleven (11) fit players are required for your next match.<br/>" .
					"Please purchase players or acquire journeymen.","notice");
			} else if ($teamInfo['team']['playersNeeded'] == 2) {
				$mainframe->enqueueMessage("Journeymen may only be used to bring your roster up to 11 fit players.<br/>" .
					"You must fire one or more to reduce your roster.","notice");
			}
		}
		
		parent::display($tpl);
    }
}
