<?php
//@license    GNU/GPL


// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport( 'joomla.application.component.view');
 
//HTML View class for the Upload Component

class BbqlViewUpload extends JView
{
    function display($tpl = null)
    {
		$model =& $this->getModel(); 

        parent::display($tpl);
    }
}
