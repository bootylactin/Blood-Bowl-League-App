<?php 
$view = JRequest::getVar('view');
$linkRoot = JRoute::_('index.php?option='.$option);

if ($view != null && $view != 'bbql') {
	print('<a href="'.$linkRoot.'">League List</a>');
} else {
	print('<b>League List</b>');
}

if ($view != null && $view != 'bbql') {
	 print('&nbsp; &nbsp; > &nbsp; &nbsp;');
	 if ($view == 'league' || $view == 'leagueLeaders') {
	 	print('<b>' . $this->league[0]['Name'] . '</b>');
	 } else {
		print('<a href="'.$linkRoot.'&view=league&leagueId='.$this->leagueId.'">' . $this->leagueName . '</a>');
	 }
}

?>