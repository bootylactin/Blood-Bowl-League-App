<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

include JRoute::_('components/com_bbql/includes/navigation.php');
$user =& JFactory::getUser();
?>

<p>
<?php include JRoute::_('components/com_bbql/includes/leagueNavigation.php'); ?>

<h4><?php echo $this->league[0]['Name'] ?> - <?php echo $this->league[0]['Status'] ?></h4>

<?php 
/* ==============================
 * If league hasn't started, show teams that have joined. 
 * ============================== */

//if league is still accepting teams
if ($this->league[0]['StatusId'] == 1) { ?>
	<table  class="leagueTable" cellspacing="0">
	<?php if (!empty($this->teams)) { ?>
	    <tr>
	    	<th></th>
	        <th>Current Teams</th>
	        <th>Race</th>
	        <th>Coach</th>
	        <th>Team Value</th>
	    </tr>
	<?php
	$isLeagueMember = false;
	foreach ($this->teams as $key => $value) {
		if ($value['coachId'] == $user->id) $isLeagueMember = true;
	?>
		<tr valign="top" <?php if ($key % 2 == 0) echo 'class="underLineRow"'; ?>>
			<td><?php echo $key+1 ?></td>
	    	<td><a href="<?php echo $linkRoot.'&view=team&teamId='.$value['teamHash']; ?>"><?php echo $value['strName'] ?></a></td>
	        <td><?php echo $value['Race'] ?></td>
	        <td><?php echo $this->userList[$value['coachId']] ?></td>
	        <td style="text-align:right"><?php echo $value['iValue'] ?></td>
	    </tr>
	<?php
		}
	} else {
	?>
		<tr><td><em>no teams have joined</em></td></tr>
	
<?php 
	}
	echo "</table>";
} else {
?>
	
	<table  class="leagueTable" cellspacing="0">
		<tr>
			<th></th>
	        <th>Team</th>
	        <th>P</th>
	        <th>W</th>
	        <th>L</th>
	        <th>D</th>
	        <th>F</th>
	        <th>A</th>
	        <th>Pts</th>
	        <th>Race</th>
	        <th>Coach</th>
	        <th>Team Value</th>
	        <th>Local Time</th>
	    </tr>
    <?php
	$isLeagueMember = false;
	$count = 1;
	foreach ($this->standings as $key => $value) {
		if ($value['coachId'] == $user->id) $isLeagueMember = true;
	?>
		<tr valign="top" <?php if ($key % 2 == 0) echo 'class="underLineRow"'; ?>>
			<td style="text-align:right"><?php echo $key+1 ?></td>
	    	<td><a href="<?php echo $linkRoot.'&view=team&teamId='.$value['teamHash']; ?>"><?php echo $value['strName'] ?></a></td>
	    	<td style="text-align:right"><?php echo $value['iMatchPlayed'] ?></td>
	        <td style="text-align:right"><?php echo $value['iWins'] ?></td>
	        <td style="text-align:right"><?php echo $value['iLoss'] ?></td>
	        <td style="text-align:right"><?php echo $value['iDraws'] ?></td>
	        <td style="text-align:right"><?php echo $value['Inflicted_iTouchdowns'] ?></td>
	        <td style="text-align:right"><?php echo $value['Sustained_iTouchdowns'] ?></td>
	        <td style="text-align:right"><?php echo $value['iPoints'] ?></td>
	        <td><?php echo $value['Race'] ?></td>
	        <td><?php echo $this->userList[$value['coachId']] ?></td>
	        <td style="text-align:right; padding-right:20px"><?php echo $value['iValue'] ?></td>
	        <td style="text-align:right"><?php echo date('g:i a - D', $this->coachesTimeZones[$value['coachId']]); ?></td>
	    </tr>
	<?php
	}
	?>
	</table>
	
<?php	if ($this->league[0]['StatusId'] == 2  && ($isLeagueMember  || $user->authorize('com_bbql', 'admin'))) { ?>
<br/>	
	<form name="uploadForm" action="<?php echo JRoute::_( 'index.php?option=com_bbql&task=uploadMatchReport' ); ?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="coachId" value="<?php echo $user->id ?>" /> 
		<input type="hidden" name="leagueId" value="<?php echo $this->leagueId ?>" />
	    <div class="tabContent">
	        <fieldset>
	            <legend> Upload a Match Report</legend>
	            <label for="file">Select a file:</label> <input type="file" name="userfile" id="file">
	        </fieldset>
	        
	        <div class="buttonBar">
	            <span id="msg"></span>
	            <input type="submit" value="Upload Match Report"  onClick="this.disabled=1; this.form.submit();" style="float: none" />
	        </div>
	    </div>
	</form>

<?php } ?>
	
	<p>
	<table  class="leagueTable" cellspacing="0">
		<tr>
			<th>Round</th>
	        <th>Away Team</th>
	        <th colspan="3">Score</th>
	        <th>Home Team</th>
	        <th>Report</th>
	    </tr>
    <?php
    $roundCount = 0;
	foreach ($this->schedule as $key => $value) {
	?>
		<tr valign="top" <?php if ($roundCount == $value['Championship_iDay']) echo 'class="noTop"'; ?>>
			<td align="right"><?php if ($roundCount != $value['Championship_iDay']) echo $value['Championship_iDay'] ?></td>
	    	<td><a href="<?php echo $linkRoot.'&view=team&teamId='.$value['teamHash_Away']; ?>"><?php echo $value['AwayTeam'] ?></a></td>
	        <td><?php echo $value['Away_iScore'] ?></td>
	        <td>-</td>
	        <td><?php echo $value['Home_iScore'] ?></td>
	        <td><a href="<?php echo $linkRoot.'&view=team&teamId='.$value['teamHash_Home']; ?>"><?php echo $value['HomeTeam'] ?></a></td>
	        <td align="right">
	        	<?php if ($value['Away_iScore'] != null) { ?>
	        	<a href="<?php echo $linkRoot.'&view=postMatch&leagueId='.$this->leagueId.'&matchId='.$value['ID'] ?>">view</a>
	        	<?php } ?>
	        </td>
	    </tr>
	<?php
		$roundCount = $value['Championship_iDay'];
	}
	?>
	</table>
<?php
} 
?>

<p>


<?php
/* ==============================
 * Upload Forms
 * ============================== */
if ($user->authorize('com_bbql', 'joinLeague') && $this->league[0]['StatusId'] == 1 && (!$isLeagueMember || $this->league[0]['MultipleTeams'] == 1)) {
?>
	<form name="uploadForm" action="<?php echo JRoute::_( 'index.php?option=com_bbql&task=joinTeamToLeague' ); ?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="coachId" value="<?php echo $user->id ?>" /> 
		<input type="hidden" name="leagueId" value="<?php echo $this->leagueId ?>" />
	    <div class="tabContent">
	        <fieldset>
	            <legend> Upload a Team File to Join This League</legend>
	            <label for="file">Select a file:</label> <input type="file" name="userfile" id="file">
	<?php if ($this->league[0]['Password'] != "") { ?> 
	            <br/><br/><label>Password:</label> <input type="password" id="password" name="password" class="required text" />
	<?php } ?>
	        </fieldset>
	        
	        <div class="buttonBar">
	            <span id="msg"></span>
	            <input type="submit" value="Upload Team" onClick="this.disabled=1; this.form.submit()" style="float: none" />
	        </div>
	    </div>
	</form>
<?php 
}
if (($isLeagueMember && $this->league[0]['StatusId'] != 3 && $this->league[0]['FullControl'] != 1) || $user->authorize('com_bbql', 'admin')) { ?>
<br/>
	<form name="uploadForm" action="<?php echo JRoute::_( 'index.php?option=com_bbql&task=updateExistingTeam' ); ?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="coachId" value="<?php echo $user->id ?>" /> 
		<input type="hidden" name="leagueId" value="<?php echo $this->leagueId ?>" />
	    <div class="tabContent">
	        <fieldset>
	            <legend> Update an Existing Team File</legend>
	            <label for="file">Select a file:</label> <input type="file" name="userfile" id="file">
	        </fieldset>
	        
	        <div class="buttonBar">
	            <span id="msg"></span>
	            <input type="submit" value="Upload Team"  onClick="this.disabled=1; this.form.submit()" style="float: none" />
	        </div>
	    </div>
	</form>
<?php 
}

if ($user->id == $this->league[0]['CommissionerId'] || $user->authorize('com_bbql', 'admin')) {
?>
	<h4>Commissioner Functions</h4>
<?php	
	if ($this->league[0]['StatusId'] == 1 && count($this->teams) > 1) {
?>
	<form name="uploadForm" action="<?php echo JRoute::_( 'index.php?option=com_bbql&task=createSchedule' ); ?>" method="post" enctype="multipart/form-data">
		<input type="hidden" name="numberOfTeams" value="<?php echo count($this->teams) ?>" /> 
		<input type="hidden" name="leagueId" value="<?php echo $this->leagueId ?>" />
	    <div class="tabContent">
	        <fieldset>
	            <legend>Generate Schedule</legend>
	             <label for="playNum">How many times will each team play their opponents?</label> 
	            <select name="playNum">
	            	<option value="1">1</option>
	            	<option value="2">2</option>
	            	<option value="3">3</option>
	            	<option value="4">4</option>
	            	<option value="5">5</option>
	            </select>
	            <br/>
	            <label for="pointsForWin">How many points will be awarded for a win?</label> 
	            <select name="pointsForWin">
	            	<option value="2">2</option>
	            	<option value="3">3</option>
	            </select>
	        </fieldset>
	        
	        <div class="buttonBar">
	            <span id="msg"></span>
	            <input type="submit" value="Generate" onClick="this.disabled=1; this.form.submit()" style="float: none" />
	        </div>
	    </div>
	</form>
<?php
	//in progress commissioner functions
	} else if ($this->league[0]['StatusId'] == 2) {
?>
<!--
		<form name="completeForm" action="<?php echo JRoute::_( 'index.php?option=com_bbql&task=completeLeague' ); ?>" method="post" enctype="multipart/form-data">
			<input type="hidden" name="coachId" value="<?php echo $user->id ?>" /> 
			<input type="hidden" name="leagueId" value="<?php echo $this->leagueId ?>" />
		    <div class="tabContent">
		        <fieldset>
		            <legend>Mark this League Completed</legend>
		            <table>
						<tr valign="top">
							<td class="small">Marks the league as complete, and allows all teams that were part of the league to stay for the next season or join another league.</td>
						</tr>
					</table>
		        </fieldset>
		        <div class="buttonBar">
		            <span id="msg"></span>
		            <input type="submit" value="Complete this League" onClick="this.disabled=1; this.form.submit()" style="float: none" />
		        </div>
		    </div>
		</form>
		<br/>
-->
		
		<form name="replaceCoach" action="<?php echo JRoute::_( 'index.php?option=com_bbql&task=changeCoach' ); ?>" method="post" enctype="multipart/form-data">
			<input type="hidden" name="coachId" value="<?php echo $user->id ?>" /> 
			<input type="hidden" name="leagueId" value="<?php echo $this->leagueId ?>" />
		    <div class="tabContent">
		        <fieldset>
		            <legend>Change a Coach</legend>
		            <table>
						<tr valign="top">
							<td class="small" colspan="2">Replaces the coach for a team in this league with another player.</td>
						</tr>
						<tr valign="top">
							<td>Team:</td>
							<td><select name="teamId">
								<option value="-1"></option>
								<?php foreach ($this->standings as $key => $value) { ?>
									<option value="<?php echo $value['teamHash'];?>"><?php echo $value['strName'];?></option>
								<?php } ?>
							</select>
							</td>
						</tr>
						<tr valign="top">
							<td>Replace Coach with:</td>
							<td><select name="coachId">
								<option value="-1"></option>
								<?php foreach ($this->userList as $key => $value) { ?>
									<option value="<?php echo $key;?>"><?php echo $value;?></option>
								<?php } ?>
							</select>
							</td>
						</tr>
					</table>
		        </fieldset>
		        <div class="buttonBar">
		            <span id="msg"></span>
		            <input type="submit" value="Change Coach" onClick="this.disabled=1; this.form.submit()" style="float: none" />
		        </div>
		    </div>
		</form>
	
<?php 		
	}
?>
		<fieldset>
			<legend>Delete this League</legend>
			<table>
				<tr valign="top">
					<td class="small">Deletes this league entirely.  At the moment, this is used
					to free up all teams in the league to allow them to join another.</td>
				</tr>
				<tr>
					<td>
						<?php
						echo '<p><a href="/index.php?option=com_bbql&task=deleteLeague&leagueId='.$this->leagueId.'"' .
							' onClick="return (confirm(\'Are you sure you want to delete this league?\n\n'.addslashes($this->league[0]['Name']).'\'))"><b>Delete League</b></a></p>';
						?>
					</td>

				</tr>
			</table>
		</fieldset>
<?php
}
if ($user->authorize('com_bbql', 'admin')) {
	echo "<h4>Admin Functions</h4>";
	echo "<a href='/index.php?option=com_bbql&task=resetLeague&leagueId=".$this->leagueId."' onClick='return confirm(\"Are you sure you want to completely reset this league?  All matches played will be erased along with the standings and the generated schedule.\")'>reset</a>";
	if ($this->league[0]['StatusId'] == 2) { ?>
		<br/>	
		<form name="revertForm" action="<?php echo JRoute::_( 'index.php?option=com_bbql&task=revertMatchReport' ); ?>" method="post" enctype="multipart/form-data">
			<input type="hidden" name="coachId" value="<?php echo $user->id ?>" /> 
			<input type="hidden" name="leagueId" value="<?php echo $this->leagueId ?>" />
		    <div class="tabContent">
		        <fieldset>
		            <legend> Revert a Match Report</legend>
		            <table>
		            	<tr valign="top">
		            		<td nowrap>Select a file:</td>
		            		<td><input type="file" name="userfile" id="file"></td>
		            		<td class="small">To revert an uploaded match report and all of it's statistics, re-upload the offending match report here.
		            			The most recent fixture that coincides with this report will be reverted.</td>
		            	</tr>
					</table>
		        </fieldset>
		        <div class="buttonBar">
		            <span id="msg"></span>
		            <input type="submit" value="Revert Match Report" onClick="this.disabled=1; this.form.submit()" style="float: none" />
		        </div>
		    </div>
		</form>
<?php
	}
}
?>

