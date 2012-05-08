<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

include JRoute::_('components/com_bbql/includes/navigation.php');
?>
<p>
<?php include JRoute::_('components/com_bbql/includes/leagueNavigation.php'); ?>

<h4>TD Leaders</h4>
<table  class="leagueTable" cellspacing="0">
    <tr>
        <th>TDs</th>
        <th>Player</th>
        <th>Team</th>
        <th>Position</th>
    </tr>

<?php 
foreach ($this->mostTDs as $key => $value) {
?>
	<tr>
		<td align="right"><?php echo $value['Inflicted_iTouchdowns']; ?></td>
		<td><?php echo $value['Name']; ?></td>
		<td><?php echo $value['TeamName']; ?></td>
		<td><?php echo $value['Position']; ?></td>
	</tr>
<?php 
} 
?>
</table>

<h4>Casualty Leaders</h4>
<table  class="leagueTable" cellspacing="0">
    <tr>
        <th>CAS</th>
        <th>KIL</th>
        <th>KO</th>
        <th>STN</th>
        <th>Player</th>
        <th>Team</th>
        <th>Position</th>
    </tr>

<?php 
foreach ($this->mostCAS as $key => $value) {
?>
	<tr>
		<td align="right"><?php echo $value['Inflicted_iCasualties']; ?></td>
		<td align="right"><?php echo $value['Inflicted_iDead']; ?></td>
		<td align="right"><?php echo $value['Inflicted_iKO']; ?></td>
		<td align="right"><?php echo $value['Inflicted_iStuns']; ?></td>
		<td><?php echo $value['Name']; ?></td>
		<td><?php echo $value['TeamName']; ?></td>
		<td><?php echo $value['Position']; ?></td>
	</tr>
<?php 
} 
?>
</table>

<h4>Passing Leaders</h4>
<table  class="leagueTable" cellspacing="0">
    <tr>
        <th>COMP</th>
        <th>Yards</th>
        <th>Player</th>
        <th>Team</th>
        <th>Position</th>
    </tr>

<?php 
foreach ($this->mostCOMP as $key => $value) {
?>
	<tr>
		<td align="right"><?php echo $value['Inflicted_iPasses']; ?></td>
		<td align="right"><?php echo $value['Inflicted_iMetersPassing']; ?></td>
		<td><?php echo $value['Name']; ?></td>
		<td><?php echo $value['TeamName']; ?></td>
		<td><?php echo $value['Position']; ?></td>
	</tr>
<?php 
} 
?>
</table>

<h4>Rushing Leaders</h4>
<table  class="leagueTable" cellspacing="0">
    <tr>
        <th>Yards</th>
        <th>Player</th>
        <th>Team</th>
        <th>Position</th>
    </tr>

<?php 
foreach ($this->mostRUSH as $key => $value) {
?>
	<tr>
		<td align="right"><?php echo $value['Inflicted_iMetersRunning']; ?></td>
		<td><?php echo $value['Name']; ?></td>
		<td><?php echo $value['TeamName']; ?></td>
		<td><?php echo $value['Position']; ?></td>
	</tr>
<?php 
} 
?>
</table>

<h4>Interception Leaders</h4>
<table  class="leagueTable" cellspacing="0">
    <tr>
        <th>INT</th>
        <th>Player</th>
        <th>Team</th>
        <th>Position</th>
    </tr>

<?php 
foreach ($this->mostINT as $key => $value) {
?>
	<tr>
		<td align="right"><?php echo $value['Inflicted_iInterceptions']; ?></td>
		<td><?php echo $value['Name']; ?></td>
		<td><?php echo $value['TeamName']; ?></td>
		<td><?php echo $value['Position']; ?></td>
	</tr>
<?php 
} 
?>
</table>