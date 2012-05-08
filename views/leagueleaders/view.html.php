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

class BbqlViewLeagueLeaders extends JView {
    function display($tpl = null) {
    	
		$leagueModel =& $this->getModel('league');
		$league =& $leagueModel->getLeagueById();

		//populate data
		$leaders = $leagueModel->getLeagueLeaders();
		
		
		$this->assignRef( 'mostTDs', $leaders['mostTDs'] );
		$this->assignRef( 'mostCAS', $leaders['mostCAS'] );
		$this->assignRef( 'mostCOMP', $leaders['mostCOMP'] );
		$this->assignRef( 'mostINT', $leaders['mostINT'] );
		$this->assignRef( 'mostRUSH', $leaders['mostRUSH'] );
		
		$this->assignRef( 'league', $league );

        parent::display($tpl);
    }
}
