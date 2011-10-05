<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

// create associative array of IDs/Names
$races = $this->stringsLocalized['races']->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);
$skills = $this->stringsLocalized['skills']->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);
$playerLevels = $this->stringsLocalized['playerLevels']->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);
$casualtyEffects = $this->stringsLocalized['casualtyEffects']->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);

$team = $this->teamInfo['team'];

include JRoute::_('components/com_bbql/includes/navigation.php');
$user =& JFactory::getUser();

$canEdit = false;
if ($team['coachId'] == $user->id && $this->FullControl == 1) {
	$canEdit = true;
}
?>


<h4><?php echo $team['strName'] ?></h4>

<?php 
if ($this->FullControl == 1) { 
	if ($team['journeymenHireFire']) { ?>
	<div style="float:right"><em>Journeymen action required for Team Download</em></div>
<?php
	} else if ($team['playersNeeded']) { ?>
	<div style="float:right"><em>11 fit players are required for Team Download</em></div>
<?php 
	} else { 
?>
	<div style="float:right"><a href="<?php echo $linkRoot.'&task=downloadTeam&teamId='.$team['teamHash']; ?>"><b>Download Team File</b></a></div>
<?php
	} 
} 
?>

<?php include JRoute::_('components/com_bbql/includes/teamNavigation.php'); ?>
<p><br/></p>
<table  class="leagueTable" cellspacing="0">
	<tr class="underLineRow">
    	<td class="bTop bold">Coach:</td>
        <td class="bTop bRight"><?php echo $this->userList[$team['coachId']] ?></td>
        <td width="40px" rowspan="5" class="bTop bRight"><br/></td>
        <td class="bTop bold">Fan Factor:</td>
        <td class="bTop bRight"><?php echo $team['iPopularity'] ?></td>
        <?php if ($canEdit) { ?>
        	<td class="bTop bRight"><br/></td>
        <?php } ?>
	</tr>
    <tr>
    	<td class="bold">Race:</td>
        <td class="bRight"><?php echo $races[$team['idRaces']][0] ?></td>
        <td class="bold">Cheerleaders:</td>
        <td class="bRight"><?php echo $team['iCheerleaders'] ?></td>
        <?php if ($canEdit) { ?>
        <form action="<?php echo $linkRoot.'&task=purchaseTeamItems'; ?>" method="post"> 
        	<input type="hidden" name="teamId" value="<?php echo $team['teamHash'] ?>">
	        <td style="padding-left:20px" class="bTop bRight">
	        	<select name="cheerleaders">
	        		<?php if ($team['iCheerleaders'] > 0) {?><option value="-1">Fire 1</option><?php }?>
	        		<option value="" SELECTED></option>
	            	<option value="1">1</option>
	            	<option value="2">2</option>
	            	<option value="3">3</option>
	            	<option value="4">4</option>
	            	<option value="5">5</option>
	            </select> x 10,000
			</td>
		<?php } ?>
	</tr>
    <tr class="underLineRow">
    	<td class="bold">Team Value:</td>
        <td class="bRight"><?php echo $team['iValue'] ?></td>
        <td class="bold">Assistant Coaches:</td>
        <td class="bRight"><?php echo $team['iAssistantCoaches'] ?></td>
        <?php if ($canEdit) { ?>
			<td style="padding-left:20px" class="bTop bRight">
	        	<select name="assistantCoaches">
	        		<?php if ($team['iAssistantCoaches'] > 0) {?><option value="-1">Fire 1</option><?php }?>
	        		<option value="" SELECTED></option>
	            	<option value="1">1</option>
	            	<option value="2">2</option>
	            	<option value="3">3</option>
	            	<option value="4">4</option>
	            	<option value="5">5</option>
	            </select> x 10,000
			</td>
	        
		<?php } ?>
	</tr>
    <tr>
    	<td class="bold">Gold:</td>
        <td class="bRight"><?php echo number_format($team['iCash']) ?></td>

		<?php if ($team['idRaces'] == 10 || $team['idRaces'] == 16 || $team['idRaces'] == 17 || $team['idRaces'] == 18) { ?>
			<td class="bold">Necromancer:</td>
			<td class="bRight">1</td>
			<?php if ($canEdit) echo "<td><br/></td>"; ?>
		<?php } else { ?>
			<td class="bold">Apothecary:</td>
			<td class="bRight"><?php echo $team['bApothecary'] ?></td>
			<?php if ($canEdit) { ?>
				<td style="padding-left:20px" class="bTop bRight">
					<select name="apothecary">
						<?php if ($team['bApothecary'] == 1) {?><option value="-1">Fire</option><?php }?>
						<option value="" SELECTED></option>
						<?php if ($team['bApothecary'] == 0) {?><option value="1">Hire</option><?php }?>
					</select> x 50,000
				</td>
		<?php }
		} ?>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td class="bold">Rerolls:</td>
        <td class="bRight"><?php echo $team['iRerolls'] ?></td>
		<?php if ($canEdit) { ?>
		<td style="padding-left:20px" class="bTop bRight">
	        	<select name="rerolls">
	        		<?php if ($team['iRerolls'] > 0) {?><option value="-1">-1</option><?php }?>
	        		<option value="" SELECTED></option>
	            	<option value="1">1</option>
	            	<option value="2">2</option>
	            	<option value="3">3</option>
	            	<option value="4">4</option>
	            	<option value="5">5</option>
	            </select> x <?php echo number_format($team['iRerollPrice']*2); ?>
			</td>
			<td class="bTop bRight"><input type="submit" value="Confirm" onClick="this.disabled=1; this.form.submit()"></td>
			</form>
		<?php } ?>
	</tr>
</table>

<h4>Roster</h4>
<table  class="leagueTable" cellspacing="0">
    <tr>
    	
        <th>#</th>
        <th>Player</th>
        <th>Position</th>
        <th>MA</th>
        <th>ST</th>
        <th>AG</th>
        <th>AV</th>
        <th>Skills</th>
        <th>Injuries</th>
        <th>Level</th>
        <th>SPPs</th>
        <th style="border-right:0">Value</th>
       <?php if ($canEdit) { ?>
			<th style="border-right:0"><br/></th>
		<?php } ?>
    </tr>

<?php 
$retiredFlag = false;
foreach ($this->teamInfo['roster'] as $key => $value) {
	if ($value['bRetired'] == 1 && !$retiredFlag && $value['journeyman'] == 0) {
		$retiredFlag = true;
		echo '<tr><td colspan="12" style="padding-left:0"><h4>Retired Players</h4></td></tr>';
	}
?>
	
	<tr valign="top" <?php if ($key % 2 == 0) echo 'class="underLineRow"'; ?>>
		
    	<td><?php echo $value['iNumber']; if ($value['journeyman'] == 1) echo "-J"; ?></td>
		<td><a href="<?php echo $linkRoot.'&view=player&playerId='.$value['playerHash']; ?>"><?php echo $value['strName'] ?></a></td>
		<td><?php echo $value['position'] ?></td>
		<td align="center"><span class="<?php echo $value['MAcolor']?>"> <?php echo $value['MA'] ?></span></td>
        <td align="center"><span class="<?php echo $value['STcolor']?>"> <?php echo $value['ST'] ?></span></td>
        <td align="center"><span class="<?php echo $value['AGcolor']?>"> <?php echo $value['AG'] ?></span></td>
        <td align="center"><span class="<?php echo $value['AVcolor']?>"> <?php echo $value['AV'] ?></span></td>
        <td>
        <?php 
		//check for level up
		if ($value['iNbLevelsUp'] > 0) {
			echo ' <a href="'.$linkRoot.'&view=player&playerId='.$value['playerHash'].'"><img src="components/com_bbql/images/levelUp.png" title="Pending Skill Roll"></a> ';
			if ($this->FullControl == 1) {
				echo '<img src="components/com_bbql/images/die'.$value['LevelUp_iRollResult'].'.png">';
				echo '<img src="components/com_bbql/images/die'.$value['LevelUp_iRollResult2'].'.png" style="padding-right:10px;">';
			}
		}
		//build default skills string
		$defaultSkills = "";
		foreach ($value['DefaultSkills'] as $val) { 
        	$defaultSkills = $defaultSkills . str_replace(" ", "&nbsp;", $skills[$val['idSkill_Listing']][0]).", ";		
        } 
		//build acquired skills string
		$acquiredSkills = "";
		foreach ($value['AcquiredSkills'] as $val) { 
        	$acquiredSkills = $acquiredSkills . str_replace(" ", "&nbsp;", $skills[$val['idSkill_Listing']][0]).", ";		
        } 
		//remove trailing comma and space
		if (strlen($acquiredSkills)) {
			$acquiredSkills = substr($acquiredSkills, 0, -2);
			$combinedSkills = $defaultSkills . '<span class="bonus">' . $acquiredSkills . '</span>';
		} else {
			$defaultSkills = substr($defaultSkills, 0, -2);
			$combinedSkills = $defaultSkills;
		}
		
		// output combined strings
		echo $combinedSkills;
		?>
        <br/></td>
		<?php
		$matchSusp = "";
		$casualties = "";
		$dead = "";
		//check for miss next game
		if ($value['iMatchSuspended'] == 1)
			$matchSusp = '<div> <img src="components/com_bbql/images/injured.png" title="Miss Next Game"></div>';
		
		foreach ($value['Injuries'] as $val) { 
        	$casualties = $casualties ."<div>".str_replace(" ", "&nbsp;", $casualtyEffects[$val['idPlayer_Casualty_Types']][0])."</div>";		
        } 
		if (strlen($casualties)) {
			$casualties = '<span class="penalty">'.substr($casualties, 0, -2).'</span>';
        }
		
		if ($value['bDead'] == 1)
			$dead = '<div> <img src="components/com_bbql/images/dead.png" title="Dead!"></div>';
		
		if (strlen($matchSusp) || strlen($casualties) || strlen($dead)) {
			echo '<td>'.$matchSusp.$casualties.$dead.'</td>';
		} else {
			echo '<td></br></td>';
		}
		?>
        
		<td align="center"><span title="<?php echo $playerLevels[$value['idPlayer_Levels']+146][0] ?>"><?php echo $value['idPlayer_Levels'] ?></span></td>
        <td align="right"><?php echo $value['iExperience'] ?></td> 
		<td align="right" class="bRight"><?php echo $value['iValue'] ?></td>
		
		<td nowrap class="bRight bTop">
		<?php if ($canEdit && $value['bRetired'] != 1) { ?>
			<a href="<?php echo $linkRoot.'&task=firePlayer&playerId='.$value['playerHash']; ?>" onClick="return confirm('Are you sure you want to fire <?php echo addSlashes($value['strName']) ?>?')"><img src="components/com_bbql/images/fire.png" title="Fire Player"></a>
		<?php } 
				if ($canEdit && $team['journeymenHireFire'] && $value['journeyman'] == 1 && $team['iCash']/1000 >= $value['iValue']) {?>
			<a href="<?php echo $linkRoot.'&task=hireJourneyman&playerId='.$value['playerHash']; ?>" onClick="return confirm('Are you sure you want to hire <?php echo addSlashes($value['strName']) ?>?')"><img src="components/com_bbql/images/hire.png" title="Hire Journeyman"></a>
		<?php } ?>
		</td>
		
	</tr>
<?php
}
?>
</table>

<?php if ($canEdit) { 
	?>
	<h4>Purchase Players</h4>
	<form action="<?php echo $linkRoot.'&task=purchasePlayers'; ?>" method="post"> 
    	<input type="hidden" name="teamId" value="<?php echo $team['teamHash'] ?>">
	<table class="leagueTable" cellspacing="0">
		<tr>
			<th>Qty</th>
			<th>Position</th>
			<th>Cost</th>
			<th>MA</th>
			<th>ST</th>
			<th>AG</th>
			<th>AV</th>
			<th>Skills</th>
			<th>Normal</th>
			<th style="border-right:0">Doubles</th>
		</tr>
	<?php foreach ($this->PlayerPurchase as $playerPurchase) { 
		//build default skills string
		$defaultSkills = "";
		foreach ($playerPurchase['DefaultSkills'] as $val) { 
        	$defaultSkills = $defaultSkills . str_replace(" ", "&nbsp;", $skills[$val['idSkill_Listing']][0]).", ";		
        }
        $defaultSkills = substr($defaultSkills, 0, -2);
        
        $normalSkills = "";
        foreach($playerPurchase['SkillCategories']['normal'] as $normal) {
        	$normalSkills = $normalSkills . substr($normal['Category'], 0, 1);
        }
        
        $doublesSkills = "";
        foreach($playerPurchase['SkillCategories']['doubles'] as $doubles) {
        	$doublesSkills = $doublesSkills . substr($doubles['Category'], 0, 1);
        }
        if (!array_key_exists($playerPurchase['position'], $this->teamInfo['positionCount'])) {
        //if ($this->teamInfo['positionCount'][$playerPurchase['position']] == null) {
        	$this->teamInfo['positionCount'][$playerPurchase['position']] = 0;
        }
		
	?>
		<tr valign="top">
			<td align="right"><?php echo $this->teamInfo['positionCount'][$playerPurchase['position']]."/".$playerPurchase['iMaxQuantity'];?></td>
			<td><?php echo $playerPurchase['position'] ?></td>
			<td align="right"><?php echo number_format($playerPurchase['iPrice']);?></td>
			<td align="center"><?php echo $playerPurchase['MA'];?></td>
			<td align="center"><?php echo $playerPurchase['ST'];?></td>
			<td align="center"><?php echo $playerPurchase['AG'];?></td>
			<td align="center"><?php echo $playerPurchase['AV'];?></td>
			
			
			<td><?php echo $defaultSkills;?></td>
			<td><?php echo $normalSkills;?></td>
			<td class="bRight"><?php echo $doublesSkills;?></td>
			<td align="right" class="bTop bRight">
				<select name="playerTypeId_<?php echo $playerPurchase['ID'];?>">
	        		<option value="" SELECTED>&nbsp; &nbsp;</option>
	        		<?php for ($i=1; $i<=$playerPurchase['iMaxQuantity']-$this->teamInfo['positionCount'][$playerPurchase['position']]; $i++) {
	        			echo '<option value="'.$i.'">'.$i.'</option>';
	        		}
	        		?>
	            </select>
			</td>
		</tr>
	<?php } ?>
	
	</table>
	<div style="float:right"><input type="submit" value="Purchase" onClick="this.disabled=1; this.form.submit()"></div>
	
	</form>

	<?php if ($team['playersNeeded']) { ?>
		<h4>Acquire Journeymen</h4>
		<form action="<?php echo $linkRoot.'&task=receiveJourneymen'; ?>" method="post"> 
	    	<input type="hidden" name="teamId" value="<?php echo $team['teamHash'] ?>">
	    	<p>If you have less than 11 fit players for the next match, you must bring Journeymen on to your team.</p>
	    <?php foreach ($this->PlayerPurchase as $playerPurchase) { 
	    	if ($playerPurchase['iMaxQuantity'] == 16) {
	    		echo "Journeyman ".$playerPurchase['position'];?>
	    		<select name="playerTypeId_<?php echo $playerPurchase['ID'];?>">
	        		<option value="" SELECTED>&nbsp; &nbsp;</option>
	        		<?php for ($i=1; $i<=(11-$team['fitPlayers']); $i++) {
	        			echo '<option value="'.$i.'">'.$i.'</option>';
	        		}
	        		?>
	            </select>
	    		
	    <?php } ?>
	    		
	    <?php } ?>	
			<input type="submit" value="Acquire" onClick="this.disabled=1; this.form.submit()">
		</form>
	<?php } ?>
	
<?php } ?>

<h4>Match History</h4>

<table  class="leagueTable" cellspacing="0">
<?php if (count($this->teamInfo['matches'])) { ?>
	<tr>
		<th>Round</th>
		<th>Away Team</th>
		<th colspan='3' style='padding:0 30px'>Score</th>
		<th>Home Team</th>
		<th style="border-right:0">Report</th>
		<th>Opponent's Local Time</th>
	</tr>
<?php
	$roundCount = 0;
	foreach ($this->teamInfo['matches'] as $key => $value) {
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
	        <td style="text-align:right"><?php // echo date('g:i a - D', $this->coachesTimeZones[$value['coachId']]); ?></td>
	</tr>
<?php
		}
	} else {
?>
	<tr><td><em>none returned</em></td></tr>
<?php } ?>
</table>
