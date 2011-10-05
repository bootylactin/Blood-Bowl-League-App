<?php 
$view = JRequest::getVar('view');
$linkRoot = JRoute::_('index.php?option='.$option);
echo "<br/>";
 if ($view == 'league') {
 	print('<b>Standings / Schedule</b>');
 } else {
	print('<a href="'.$linkRoot.'&view=league&leagueId='.JRequest::getVar('leagueId').'">Standings / Schedule</a>');
 }
 print('&nbsp; &nbsp; | &nbsp; &nbsp;');
 if ($view == 'leagueLeaders') {
 	print('<b>League Leaders</b>');
 } else {
	print('<a href="'.$linkRoot.'&view=leagueLeaders&leagueId='.$this->leagueId.'">League Leaders</a>');
 }
?>