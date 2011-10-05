<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

include JRoute::_('components/com_bbql/includes/navigation.php');
$user =& JFactory::getUser();
?>

<h4>My Leagues</h4>
<table  class="leagueTable" cellspacing="0">
<?php if (!empty($this->leagues)) { ?>
    <tr valign="bottom">
        <th>League</th>
        <th>Teams</th>
        <th>Commissioner</th>
        <th>Password?</th>
        <th>Multiple<br/>Teams?</th>
        <th>Application<br/>Control?</th>
        <th>Status</th>
    </tr>

<?php
foreach ($this->leagues as $key => $value) {
?>
	<tr valign="top" <?php if ($key % 2 == 0) echo 'class="underLineRow"'; ?>>
        <td><a href="<?php echo JRoute::_( 'index.php?option=com_bbql&view=league&leagueId='.$value['ID'] ); ?>"><?php echo $value['Name'] ?></a></td>
        <td><?php echo $value['numberOfTeams'] ?></td>
        <td><?php echo $this->userList[$value['CommissionerId']] ?></td>
        <td><?php if ($value['Password'] == '') { echo "No"; } else { echo "Yes"; } ?></td>
        <td><?php if ($value['MultipleTeams'] == '0') { echo "No"; } else { echo "Yes"; } ?></td>
        <td><?php if ($value['FullControl'] == '0') { echo "No"; } else { echo "Yes"; } ?></td>
        <td><?php echo $value['Status'] ?></td>
        <?php
        if ($user->authorize('com_bbql', 'admin')) {
        	echo '<td><a href="/index.php?option=com_bbql&task=deleteLeague&leagueId='.$value['ID'].'"' .
                    ' onClick="return (confirm(\'Are you sure you want to delete this league?\n\n'.addslashes($value['Name']).'\'))">delete</a></td>';
        }
        ?>
    </tr>
<?php
	}
} else {
?>
<!--	<tr><td><em>none returned</em></td></tr> -->
	<tr><td><em>My Leagues functionality is currently broken.
				Please use the "Filter by League Name" dropdown below to find your leagues.</em></td></tr>
<?php } ?>
</table>
<p>

<?php
$user =& JFactory::getUser();

if ($user->authorize('com_bbql', 'createLeague')) {
?>
<h4>Create a New League</h4>
<form name="uploadForm" action="<?php echo JRoute::_( 'index.php?option=com_bbql&task=createLeague' ); ?>" method="post">
	<input type="hidden" id="CommissionerId" name="CommissionerId" value="<?php echo $user->id; ?>"/>
    <div class="tabContent">
        <fieldset>
            <legend> League Settings </legend>
            <table>
            	<tr valign="top">
            		<td nowrap>League Name:</td>
            		<td><input type="text" id="name" name="name" class="required text" /></td>
            	</tr>
            	<tr valign="top">
            		<td nowrap>Optional Password:</td>
            		<td><input type="text" id="password" name="password" class="required text" /></td>
            		<td class="small">The password coaches must enter when submitting a team to join your league.</td>
            	</tr>
            	<tr valign="top">
            		<td nowrap>Multiple Teams Allowed?</td>
            		<td><input type="radio" name="multipleTeams" value="0" checked>No <input type="radio" name="multipleTeams" value="1">Yes</td>
            		<td class="small">Choose Yes if this league will be used to "park" teams or for leagues where one account
            	 	may be used to update all teams, for example LAN or Hotseat Leagues.</td>
            	</tr>
            	<tr valign="top">
            		<td nowrap>Full Application Control?</td>
            		<td><input type="radio" name="fullControl" value="0" checked>No <input type="radio" name="fullControl" value="1">Yes</td>
            		<td class="small">Choose Yes if this league will be played via DirectIP, LAN or Hotseat modes.  If Yes, the application
            			will handle generating spectators, FAME, Fan Factor increases/decreases, and winnings for each match.  Journeymen, Team Value, and
            			skill rolls are also driven completely by the application.</td>
            	</tr>
            </table>
        </fieldset>

        <div class="buttonBar">
            <span id="msg"></span>
            <input type="submit" value="Create League" onClick="this.disabled=1; this.form.submit()" style="float: none" />
        </div>
    </div>
</form>

<?php } ?>

<h4>All Other Leagues</h4>


<form method="get" action="/index.php?option=com_bbql" name="filterForm">
	<p>
Filter by League Name:
<input type="hidden" name="option" value="com_bbql">
<select name="filterLetter" onChange="this.form.submit()">
	<option value=" "> </option>
	<?php foreach(range('A','Z') as $i) {
		$selected = "";
		if (JRequest::getVar('filterLetter') == $i)
			$selected = "SELECTED";
		echo "<option value=\"$i\" $selected>$i</option>";
	} ?>
</select>
	</p>
</form>


<table  class="leagueTable" cellspacing="0">
<?php if (!empty($this->otherLeagues)) { ?>
    <tr valign="bottom">
        <th>League</th>
        <th>Teams</th>
        <th>Commissioner</th>
        <th>Password?</th>
        <th>Multiple<br/>Teams?</th>
        <th>Application<br/>Control?</th>
        <th>Status</th>
    </tr>

<?php
	foreach ($this->otherLeagues as $key => $value) {
?>
	<tr valign="top" <?php if ($key % 2 == 0) echo 'class="underLineRow"'; ?>>
		<td><a href="<?php echo JRoute::_( 'index.php?option=com_bbql&view=league&leagueId='.$value['ID'] ); ?>"><?php echo $value['Name'] ?></a></td>
		<td><?php echo $value['numberOfTeams'] ?></td>
		<td><?php echo $this->userList[$value['CommissionerId']] ?></td>
        <td><?php if ($value['Password'] == '') { echo "No"; } else { echo "Yes"; } ?></td>
        <td><?php if ($value['MultipleTeams'] == '0') { echo "No"; } else { echo "Yes"; } ?></td>
        <td><?php if ($value['FullControl'] == '0') { echo "No"; } else { echo "Yes"; } ?></td>
        <td><?php echo $value['Status'] ?></td>
        <?php
        if ($user->authorize('com_bbql', 'admin')) {
		?>
			<td><a href="/index.php?option=com_bbql&task=deleteLeague&leagueId=<?php echo $value['ID']?>"
				onClick="return (confirm('Are you sure you want to delete this league?\n\n<?php echo addslashes(htmlentities($value['Name']))?>'))"
				>delete</a></td>
		<?php
        }
        ?>
    </tr>
<?php
	}
} else {
?>
	<tr><td><em>none returned</em></td></tr>
<?php } ?>
</table>
