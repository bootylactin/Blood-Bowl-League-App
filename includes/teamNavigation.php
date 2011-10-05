<?php 
$view = JRequest::getVar('view');
$linkRoot = JRoute::_('index.php?option='.$option);

 if ($view == 'team') {
 	print('<b>Summary / Roster</b>');
 } else {
	print('<a href="'.$linkRoot.'&view=team&teamId='.$this->teamId.'">Summary / Roster</a>');
 }
 print('&nbsp; &nbsp; | &nbsp; &nbsp;');
 if ($view == 'statistics') {
 	print('<b>Statistics</b>');
 } else {
	print('<a href="'.$linkRoot.'&view=statistics&teamId='.$this->teamId.'">Statistics</a>');
 }
?>