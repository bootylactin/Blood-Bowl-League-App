<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

include JRoute::_('components/com_bbql/includes/navigation.php');
$user =& JFactory::getUser();
?>

<p>

<h4>Post-Match Report</h4>

<?php
foreach ($this->schedule AS $row) {
	if ($row['ID'] == $this->matchId) {
		$awaySpecRoll = split(",", $row['Away_iSpectatorsRoll']);
		$homeSpecRoll = split(",", $row['Home_iSpectatorsRoll']);
		$awayFFRoll = split(",", $row['Away_iFanFactorRoll']);
		$homeFFRoll = split(",", $row['Home_iFanFactorRoll']);

	if ($row['Away_iScore'] != $row['Home_iScore'] && $row['Away_iWinningsReRoll'] == null && $row['Home_iWinningsReRoll'] == null
		&& $this->league[0]['FullControl']==1) {
		echo "<p>Note: The winning team will not have their gold transferred to their treasury until they have
		accepted or re-rolled their winnings die below.</p>";
	}
?>

<table  class="leagueTable" cellspacing="0" cellspacing="0">
	<tr>
		<td colspan="2"><b><a href="<?php echo JRoute::_( 'index.php?option=com_bbql&view=team&teamId='.$row['teamHash_Away']); ?>"><?php echo $row['AwayTeam']?></a></b></td>
		<td></td>
		<td colspan="2"><b><a href="<?php echo JRoute::_( 'index.php?option=com_bbql&view=team&teamId='.$row['teamHash_Home']); ?>"><?php echo $row['HomeTeam']?></a></b></td>
	</tr>
	<tr>
		<td></td>
		<td align="right"><?php echo $row['Away_iScore']?></td>
		<td align="center">Score</td>
		<td><?php echo $row['Home_iScore']?></td>
		<td></td>
	</tr>
	<?php if ($this->league[0]['FullControl']==1) { ?>
	<tr>
		<td></td>
		<td align="right"><?php echo $row['Away_iFanFactor']?></td>
		<td align="center">Fan Factor</td>
		<td><?php echo $row['Home_iFanFactor']?></td>
		<td></td>
	</tr>
	<tr>
		<td><?php foreach($awaySpecRoll as $die) {?><img src="components/com_bbql/images/die<?php echo $die ?>.png"><?php } ?></td>
		<td align="right"><?php echo number_format($row['Away_iSpectators'])?></td>
		<td align="center">Gate</td>
		<td><?php echo number_format($row['Home_iSpectators'])?></td>
		<td align="right"><?php foreach($homeSpecRoll as $die) {?><img src="components/com_bbql/images/die<?php echo $die ?>.png"><?php } ?> </td>
	</tr>
	<tr>
		<td></td>
		<td align="right"><?php echo $row['Away_iFAME']?></td>
		<td align="center">FAME</td>
		<td><?php echo $row['Home_iFAME']?></td>
		<td></td>
	</tr>
	<tr>
		<td><?php foreach($awayFFRoll as $die) {?><img src="components/com_bbql/images/die<?php echo $die ?>.png"><?php } ?></td>
		<td align="right"><?php echo $row['Away_iFFModifier']?></td>
		<td align="center" style="padding: 0 10px">Fan Factor Change</td>
		<td><?php echo $row['Home_iFFModifier']?></td>
		<td align="right"><?php foreach($homeFFRoll as $die) {?><img src="components/com_bbql/images/die<?php echo $die ?>.png"><?php } ?></td>
	</tr>
	<tr>
		<td><img src="components/com_bbql/images/die<?php if ($row['Away_iWinningsReRoll'] > 0) echo $row['Away_iWinningsReRoll']; else echo $row['Away_iWinningsRoll'];?>.png"></td>
		<td align="right" style="padding-left:20px"><?php echo number_format($row['Away_iCashEarned'])?></td>
		<td align="center">Winnings</td>
		<td style="padding-right:20px"><?php echo number_format($row['Home_iCashEarned'])?></td>
		<td align="right"><img src="components/com_bbql/images/die<?php if ($row['Home_iWinningsReRoll'] > 0) echo $row['Home_iWinningsReRoll']; else echo $row['Home_iWinningsRoll'];?>.png"></td>
	</tr>
	
	<?php 
	//accept or re-roll winnings die
	if ($row['Away_iScore'] != $row['Home_iScore']) {
		if ($row['Away_iWinningsReRoll'] == null && $row['Home_iWinningsReRoll'] == null) { ?>
			<tr>
				<td><?php if ($row['Away_iScore'] > $row['Home_iScore'] && $row['AwayCoachId'] == $user->id) { ?>
					<a href="<?php echo JRoute::_( 'index.php?option=com_bbql&task=reRollWinnings&leagueId='.$row['leagueId'].'&matchId='.$row['ID'].'&team=away&reroll=false' ); ?>">accept</a> | <a href="<?php echo JRoute::_( 'index.php?option=com_bbql&task=reRollWinnings&leagueId='.$row['leagueId'].'&matchId='.$row['ID'].'&team=away&reroll=true' ); ?>">re-roll</a>
					<?php } ?>
				</td>
				<td></td>
				<td></td>
				<td></td>
				<td align="right"><?php if ($row['Away_iScore'] < $row['Home_iScore'] && $row['HomeCoachId'] == $user->id) { ?>
					<a href="<?php echo JRoute::_( 'index.php?option=com_bbql&task=reRollWinnings&leagueId='.$row['leagueId'].'&matchId='.$row['ID'].'&team=home&reroll=false' ); ?>">accept</a> | <a href="<?php echo JRoute::_( 'index.php?option=com_bbql&task=reRollWinnings&leagueId='.$row['leagueId'].'&matchId='.$row['ID'].'&team=home&reroll=true' ); ?>">re-roll</a>
					<?php } ?>
				</td>
			</tr>
	<?php 
		}
	}
	if ($row['Away_iTeamValue']) {
	?>
		<tr>
			<td></td>
			<td align="right" style="padding-left:20px"><?php echo number_format($row['Away_iCashBeforeGame'])?></td>
			<td align="center">Pre-game Treasury</td>
			<td style="padding-right:20px"><?php echo number_format($row['Home_iCashBeforeGame'])?></td>
			<td</td>
		</tr>
		<tr>
			<td></td>
			<td align="right"><?php echo $row['Away_iTeamValue']?></td>
			<td align="center">Team Value</td>
			<td><?php echo $row['Home_iTeamValue']?></td>
			<td></td>
		</tr>
		<tr>
			<td></td>
			<td align="right">-<?php echo number_format($row['Away_iSpirallingExpenses'])?></td>
			<td align="center">Spiralling Expenses</td>
			<td>-<?php echo number_format($row['Home_iSpirallingExpenses'])?></td>
			<td></td>
		</tr>
	<?php
	}
	?>

<?php
	} //end FullControl
	echo "</table>";
	}
}
?>

<!-- Match Stats will go here -->





