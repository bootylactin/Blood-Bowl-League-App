<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

$team = $this->teamInfo['team'];
include JRoute::_('components/com_bbql/includes/navigation.php');
?>

<h4><?php echo $team['strName'] ?></h4>

<?php include JRoute::_('components/com_bbql/includes/teamNavigation.php'); ?>

<h4>Player Statistics</h4>
<table class="statTable">
	<tr>
		<th colspan="3"></th>
		<th colspan="5" class="SPPs">SPP</th>
		<th colspan="3" class="Misc">Misc</th>
		<th colspan="6" class="Inflicted">Inflicted</th>
		<th colspan="6" class="Sustained">Sustained</th>
	</tr>
    <tr>
        <th>#</th>
        <th>Player</th>
        <th title="Matches Played">MP</th>
        <th title="MVPs">MVP</th>
        <th title="Touch Downs">TD</th>
        <th title="Casualties">CAS</th> 
        <th title="Interceptions">INT</th>
        <th title="Completions">CMP</th>
        <th title="Catches">CAT</th>
        <th title="Yards Rushing">RSH</th>
        <th title="Yards Passing">PAS</th>
        <th title="Tackles">TAC</th>
        <th title="Injuries">INJ</th>
		<th title="Stuns">STN</th>     
        <th title="Knock-Outs">KO</th>
        <th title="Casualties">CAS</th>
        <th title="Kills">KIL</th>
        <th title="Interceptions">INT</th>
        <th title="Tackles">TAC</th>
        <th title="Injuries">INJ</th>
        <th title="Stuns">STN</th>
        <th title="Knock-Outs">KO</th>
        <th title="Casualties">CAS</th>
    </tr>

<?php
foreach ($this->playerStats as $key => $value) {
?>
	<tr valign="top" align="right" <?php if ($key % 2 == 0) echo 'class="oddRow"'; ?>>
    	<td><?php echo $value['iNumber'] ?></td>
		<td align="left"<a href="<?php echo $linkRoot.'&view=player&playerId='.$value['playerHash']; ?>"><?php echo $value['strName'] ?></a></td>
		<td><?php echo $value['iMatchPlayed'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="SPPs"' ?>><?php echo $value['iMVP'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="SPPs"' ?>><?php echo $value['Inflicted_iTouchdowns'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="SPPs"' ?>><?php echo $value['Inflicted_iCasualties'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="SPPs"' ?>><?php echo $value['Inflicted_iInterceptions'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="SPPs"' ?>><?php echo $value['Inflicted_iPasses'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Misc"' ?>><?php echo $value['Inflicted_iCatches'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Misc"' ?>><?php echo $value['Inflicted_iMetersRunning'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Misc"' ?>><?php echo $value['Inflicted_iMetersPassing'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Inflicted"' ?>><?php echo $value['Inflicted_iTackles'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Inflicted"' ?>><?php echo $value['Inflicted_iInjuries'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Inflicted"' ?>><?php echo $value['Inflicted_iStuns'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Inflicted"' ?>><?php echo $value['Inflicted_iKO'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Inflicted"' ?>><?php echo $value['Inflicted_iCasualties'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Inflicted"' ?>><?php echo $value['Inflicted_iDead'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iInterceptions'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iTackles'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iInjuries'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iStuns'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iKO'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iCasualties'] ?></td>
	</tr>
<?php
}
?>
</table>

<h4>Team Statistics</h4>
<table class="statTable">
	<tr>
		<th></th>
		<th colspan="5" class="SPPs">SPP</th>
		<th colspan="3" class="Misc">Misc</th>
		<th colspan="6" class="Inflicted">Inflicted</th>
		<th colspan="12" class="Sustained">Sustained</th>
	</tr>
	<tr>
        <th title="Matches Played">MP</th>
        <th title="MVPs">MVP</th>
        <th title="Touch Downs">TD</th>
        <th title="Casualties">CAS</th> 
        <th title="Interceptions">INT</th>
        <th title="Completions">CMP</th>
        <th title="Catches">CAT</th>
        <th title="Yards Rushing">RSH</th>
        <th title="Yards Passing">PAS</th>
        <th title="Tackles">TAC</th>
        <th title="Injuries">INJ</th>
		<th title="Stuns">STN</th>     
        <th title="Knock-Outs">KO</th>
        <th title="Casualties">CAS</th>
        <th title="Kills">KIL</th>
        <th title="Touch Downs">TD</th>
        <th title="Completions">CMP</th>
        <th title="Catches">CAT</th>
        <th title="Yards Rushing">RSH</th>
        <th title="Yards Passing">PAS</th>
        <th title="Interceptions">INT</th>
        <th title="Tackles">TAC</th>
        <th title="Injuries">INJ</th>
        <th title="Stuns">STN</th>
        <th title="Knock-Outs">KO</th>
        <th title="Casualties">CAS</th>
        <th title="Kills">KIL</th>
    </tr>

<?php
foreach ($this->teamStats as $key => $value) {
?>
	<tr valign="top" align="right" <?php if ($key % 2 == 0) echo 'class="oddRow"'; ?>>
		<td><?php echo $value['iMatchPlayed'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="SPPs"' ?>><?php echo $value['iMVP'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="SPPs"' ?>><?php echo $value['Inflicted_iTouchdowns'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="SPPs"' ?>><?php echo $value['Inflicted_iCasualties'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="SPPs"' ?>><?php echo $value['Inflicted_iInterceptions'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="SPPs"' ?>><?php echo $value['Inflicted_iPasses'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Misc"' ?>><?php echo $value['Inflicted_iCatches'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Misc"' ?>><?php echo $value['Inflicted_iMetersRunning'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Misc"' ?>><?php echo $value['Inflicted_iMetersPassing'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Inflicted"' ?>><?php echo $value['Inflicted_iTackles'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Inflicted"' ?>><?php echo $value['Inflicted_iInjuries'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Inflicted"' ?>><?php echo $value['Inflicted_iInjuries']-$value['Inflicted_iKO']-$value['Inflicted_iCasualties'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Inflicted"' ?>><?php echo $value['Inflicted_iKO'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Inflicted"' ?>><?php echo $value['Inflicted_iCasualties'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Inflicted"' ?>><?php echo $value['Inflicted_iDead'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iTouchdowns'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iPasses'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iCatches'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iMetersRunning'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iMetersPassing'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iInterceptions'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iTackles'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iInjuries'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iInjuries']-$value['Sustained_iKO']-$value['Sustained_iCasualties'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iKO'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iCasualties'] ?></td>
		<td <?php if ($key % 2 == 0) echo 'class="Sustained"' ?>><?php echo $value['Sustained_iDead'] ?></td>
	</tr>
</table>
<table class="statTable">
	<tr>
    	<td><br/></td>
    </tr>
    <tr valign="bottom">
        <th title="Wins">&nbsp; W &nbsp; </th>
        <th title="Draws">&nbsp; D &nbsp; </th>
        <th title="Losses">&nbsp; L &nbsp; </th>
        <th title="Average Spectators per Match">Avg Spec</th>
        <th title="Average Cash Earned per Match">Avg Cash</th>
        <th title="Ball Possession % per Match">Ball<br/>Poss</th>
        <th title="Possession % in Own Half">Own<br/>Half</th>
        <th title="Possession % in Opponent's Half">Opp<br/>Half</th>
        <th title="Average Match Rating">Avg<br/>Rating</th>
	</tr>
	<tr class="oddRow" align="right">
        <td><?php echo $value['iWins'] ?></td>
        <td><?php echo $value['iDraws'] ?></td>
        <td><?php echo $value['iLoss'] ?></td>
        <td><?php if ($value['iMatchPlayed'] != 0) { echo number_format($value['iSpectators']/$value['iMatchPlayed']); } ?></td>
        <td><?php if ($value['iMatchPlayed'] != 0) { echo number_format($value['iCashEarned']/$value['iMatchPlayed']); } ?></td>
        <td><?php if ($value['iPossessionBall'] != 0) { echo number_format($value['iPossessionBall']/$value['iMatchPlayed']); } ?>%</td>
        <td><?php if ($value['Occupation_iOwn'] != 0) { echo number_format($value['Occupation_iOwn']/$value['iMatchPlayed']); } ?>%</td>
        <td><?php if ($value['Occupation_iTheir'] != 0) { echo number_format($value['Occupation_iTheir']/$value['iMatchPlayed']); } ?>%</td>
		<td><?php if ($value['Average_iMatchRating'] != 0) { echo number_format($value['Average_iMatchRating']/$value['iMatchPlayed']); } ?></td>
	</tr>
<?php
}
?>
</table>
