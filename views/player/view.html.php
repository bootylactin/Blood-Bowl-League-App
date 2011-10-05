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

class BbqlViewPlayer extends JView
{
    function display($tpl = null)
    {
    	global $userList;
    	
		$model =& $this->getModel();
		$info =& $model->getTeamAndLeagueInfo();
		
		//populate data
		$stringsLocalized =& $model->stringsLocalized;
		
		$playerInfo = & $model->getPlayerInfo();
		$playerDetails = $playerInfo['playerDetails'];
		$numbers = & $model->getAvailablePlayerNumbers($info['teamId']);
		$skillCat = $playerInfo['skillCategories'];
		$next = $playerInfo['next'];
		$previous = $playerInfo['previous'];
		
		$this->assignRef( 'playerInfo', $playerDetails );
		$this->assignRef( 'next', $next );
		$this->assignRef( 'previous', $previous );
		$this->assignRef( 'stringsLocalized', $stringsLocalized );
        $this->assignRef( 'userList', $userList );
        $this->assignRef( 'playerHash', JRequest::getVar('playerHash'));
        $this->assignRef( 'skillCat', $skillCat );
        $this->assignRef( 'numbers', $numbers );
        $this->assignRef( 'utils', $model->utils );
        
        
        $this->assignRef( 'leagueId', $info['leagueId']);
        $this->assignRef( 'leagueName', $info['leagueName']);
        $this->assignRef( 'teamId', $info['teamId']);
        $this->assignRef( 'teamName', $info['teamName']);
        $this->assignRef( 'info', $info['FullControl']);

        parent::display($tpl);
    }
}
