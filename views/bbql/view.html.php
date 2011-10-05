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

class BbqlViewBbql extends JView
{
    function display($tpl = null)
    {
    	global $userList;
    	
		$model =& $this->getModel();
		$leagues =& $model->getLeagues(JRequest::getVar('filterLetter'));

		$this->assignRef( 'leagues', $leagues['my'] );
		$this->assignRef( 'otherLeagues', $leagues['others'] );
		$this->assignRef( 'userList', $userList );
		
        parent::display($tpl);
    }
}
