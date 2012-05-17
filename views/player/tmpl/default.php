<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

// create associative array of IDs/Names
$playerPositions = $this->stringsLocalized['playerPositions'];
$playerLevels = $this->stringsLocalized['playerLevels'];
$casualtyEffects = $this->stringsLocalized['casualtyEffects'];

include JRoute::_('components/com_bbql/includes/navigation.php');
$user =& JFactory::getUser();
$value = $this->playerInfo;

$canEdit = false;
if ($value['coachId'] == $user->id && $this->info['FullControl'] == 1) {
	$canEdit = true;
}
?>



<h4><?php echo $this->teamName ?></h4>

<?php include JRoute::_('components/com_bbql/includes/teamNavigation.php'); 

echo "<p>";

if ($this->next) {
	echo '<a href="'.$linkRoot.'&view=player&playerId='.$this->next.'"><img src="components/com_bbql/images/next.png" title="Next Player" align="right"></a>&nbsp;';
}
if ($this->previous) {
	echo '<a href="'.$linkRoot.'&view=player&playerId='.$this->previous.'"><img src="components/com_bbql/images/previous.png" title="Previous Player"></a>';
}
echo '<img src="components/com_bbql/images/transparent.png" width="1" height="30">';

$retiredFlag = false;

?>
<table>
	<?php if ($canEdit) { ?>
	<form action="<?php echo JRoute::_( 'index.php?option=com_bbql&task=changePlayerAttributes' ); ?>" method="post"> 
    	<input type="hidden" name="playerId" value="<?php echo $value['playerHash'] ?>">
	<tr>
		<td><b>Name:</b></td>
		<td><input type="text" name="strName" value="<?php echo $value['strName'] ?>"></td>
	</tr>
	<tr>
		<td><b>Player Number:</b></td>
		<td>
			<select name="iNumber">
        		<?php 
        		$assignedNumberUsed = false;
        		/*
        		for ($i=0; $i<count($this->numbers); $i++) {
        			if ($this->numbers[$i] > $value['iNumber'] && !$assignedNumberUsed) {
        				echo '<option value="'.$value['iNumber'].'" SELECTED>'.$value['iNumber'].'</option>';
        				$assignedNumberUsed = true;
        			}
        			echo '<option value="'.$this->numbers[$i].'">'.$this->numbers[$i].'</option>';
        		}*/
        		foreach ($this->numbers as $val) {
        			if ($val > $value['iNumber'] && !$assignedNumberUsed) {
        				echo '<option value="'.$value['iNumber'].'" SELECTED>'.$value['iNumber'].'</option>';
        				$assignedNumberUsed = true;
        			}
        			echo '<option value="'.$val.'">'.$val.'</option>';
        		}
        		?>
            </select>
        </td>
	</tr>
	<tr>
		<td><b>Skin:</b></td>
		<td><input type="text" size="2" name="iSkinTextureVariant" value="<?php echo $value['iSkinTextureVariant'] ?>"> <span class="small">(0 for random)</span></td>
		<td style="padding-left:20px"><input type="submit" value="Save"></td>
	</tr>
	</tr>
	</form>
	<?php } ?>
</table>

<table  class="leagueTable" cellspacing="0">
    <tr>
    <?php if (!$canEdit) { ?>
		<th>#</th>
		<th>Player</th>
	<?php } ?>
        <th>Position</th>
        <th>MA</th>
        <th>ST</th>
        <th>AG</th>
        <th>AV</th>
        <th>Skills</th>
        <th>Injuries</th>
        <th>Level</th>
        <th>SPPs</th>
        <th>Value</th>
    </tr>
	<tr valign="top" class="underLineRow">
	<?php if (!$canEdit) {
		echo '<td>'.$value['iNumber'].'</td>';
		echo '<td>'.$value['strName'].'</td>';
	} ?>
		<td><?php echo $playerPositions[$value['positionId']]['position'] ?></td>
		<td align="center"><span class="<?php echo $value['MAcolor']?>"> <?php echo $value['MA'] ?></span></td>
        <td align="center"><span class="<?php echo $value['STcolor']?>"> <?php echo $value['ST'] ?></span></td>
        <td align="center"><span class="<?php echo $value['AGcolor']?>"> <?php echo $value['AG'] ?></span></td>
        <td align="center"><span class="<?php echo $value['AVcolor']?>"> <?php echo $value['AV'] ?></span></td>
        <td>
        <?php 
		//check for level up
		if ($value['iNbLevelsUp'] > 0) {
			echo ' <a href="'.$linkRoot.'&view=player&playerId='.$value['playerHash'].'" title="Pending Skill Roll" class="tipTip"><img src="components/com_bbql/images/levelUp.png"> ';
			if ($this->info['FullControl'] == 1) {
				echo '<img src="components/com_bbql/images/die'.$value['LevelUp_iRollResult'].'.png">';
				echo '<img src="components/com_bbql/images/die'.$value['LevelUp_iRollResult2'].'.png" style="padding-right:10px;">';
			}
			echo ' </a> ';
		}
		
		//build default skills string
		$defaultSkills = "";
		foreach ($value['DefaultSkills'] as $val) { 
        	$defaultSkills = $defaultSkills . '<span class="tipTip" title="'. $val['description'] .'">' . str_replace(" ", "&nbsp;", $val['name']).", </span>";		
        } 
		//build acquired skills string
		$acquiredSkills = "";
		foreach ($value['AcquiredSkills'] as $val) { 
        	$acquiredSkills = $acquiredSkills . '<span class="bonus tipTip" title="'. $val['description'] .'">' . str_replace(" ", "&nbsp;", $val['name']).", </span>";		
        } 
		//remove trailing comma and space
		if (strlen($acquiredSkills)) {
			$acquiredSkills = substr($acquiredSkills, 0, -9);
			$combinedSkills = $defaultSkills . $acquiredSkills . '</span>';
		} else {
			$defaultSkills = substr($defaultSkills, 0, -9);
			$combinedSkills = $defaultSkills . '</span>';
		}
		
		// output combined strings
		echo $combinedSkills;
		?>
        <br/></td>
        <td>
		<?php
		//check for miss next game
		if ($value['iMatchSuspended'] == 1)
			echo ' <img src="components/com_bbql/images/injured.png" title="Miss Next Game" class="tipTip"><br/>';
		$casualties = "";
		
		foreach ($value['Injuries'] as $val) { 
        	$casualties = $casualties . str_replace(" ", "&nbsp;", $casualtyEffects[$val['idPlayer_Casualty_Types']]['casualty'])."<br/>";		
        } 
		if (strlen($casualties)) {
			$casualties = substr($casualties, 0, -2);
			echo '<span class="penalty">' . $casualties . '</span>';
        }
		
		if ($value['bDead'] == 1)
			echo ' <img src="components/com_bbql/images/dead.png" title="Dead!" class="tipTip"> ';
		?>
        <br/></td>
		<td align="center"><span title="<?php echo $playerLevels[$value['idPlayer_Levels']+146]['playerLevel'] ?>" class="tipTip"><?php echo $value['idPlayer_Levels'] ?></span></td>
        <td align="right"><?php echo $value['iExperience'] ?></td> 
		<td align="right"><?php echo $value['iValue'] ?></td>
	</tr>

</table>

<?php if ($value['iNbLevelsUp'] > 0 && $canEdit) { ?>
	<form action="<?php echo JRoute::_( 'index.php?option=com_bbql&task=addSkill' ); ?>" method="post" name="addSkill">
		<input type="hidden" name="playerId" value="<?php echo $value['playerHash'] ?>">
		<input type="hidden" name="playerType" value="<?php echo $value['idPlayer_Types'] ?>">
		
	<div style="float:left">
	<h4>Normal</h4>
	<table  class="leagueTable" cellspacing="0" style="margin-right:10px">
		<tr>
			<td>
	<?php
	$catHeading = "";
	
	foreach ($this->skillCat['normal'] as $row) {
		if ($catHeading != $row['Category']) {
			echo '</td><td valign="top"><b>'.$row['Category'].'</b><br/>';
			$catHeading = $row['Category'];
		}
		
		$skillStr = '<input type="radio" name="skillId" id="s'.$row['skillId'].'" value="'.$row['skillId'].'"';
		$skillName = '<span class="tipTip" title="'. $row['description'] .'">' . str_replace(" ", "&nbsp;", $row['skillName']) . '</span>';
		$className = "";
		if (array_key_exists($row['skillId'], $value['DefaultSkills'])
		|| array_key_exists($row['skillId'], $value['AcquiredSkills'])) {
			$skillStr = $skillStr.' DISABLED';
			$className = "disabled";
		}
		$skillStr = $skillStr.'><label class="'.$className.'" for="s'.$row['skillId'].'">'.$skillName.'</label>';
		
		echo $skillStr."<br/>";
	}
	?>
			</td>
		</tr>
	</table>
	</div>
	
	<?php if ($value['LevelUp_bDouble'] == 1) { ?>
	<div style="float:left">
	<h4>Doubles</h4>
	<table  class="leagueTable" cellspacing="0" style="margin-right:10px">
		<tr>
			<td>
	<?php
	$catHeading = "";
	foreach ($this->skillCat['doubles'] as $row) {
		if ($catHeading != $row['Category']) {
			echo '</td><td valign="top"><b>'.$row['Category'].'</b><br/>';
			$catHeading = $row['Category'];
		}
		$skillStr = '<input type="radio" name="skillId" id="s'.$row['skillId'].'" value="'.$row['skillId'].'"';
		$skillName = '<span class="tipTip" title="'. $row['description'] .'">' . str_replace(" ", "&nbsp;", $row['skillName']) . '</span>';
		$className = "";
		if (array_key_exists($row['skillId'], $value['DefaultSkills'])
		|| array_key_exists($row['skillId'], $value['AcquiredSkills'])) {
			$skillStr = $skillStr.' DISABLED';
			$className = "disabled";
		}
		$skillStr = $skillStr.'><label class="'.$className.'" for="s'.$row['skillId'].'">'.$skillName.'</label>';
		
		echo $skillStr."<br/>";
	}
	?>
		</tr>
	</table>
	</div>
	<?php } 
	
	if ($value['LevelUp_iRollResult'] + $value['LevelUp_iRollResult2'] >= 10) {
	?>
	
	<div style="float:left">
	<h4>Attribute</h4>
	<table  class="leagueTable" cellspacing="0" style="margin-right:10px">
		<tr>
			<td>
			<b>Increase</b><br/>
			<?php switch ($value['LevelUp_iRollResult'] + $value['LevelUp_iRollResult2']) {
				case 10:
					echo '<input type="radio" name="skillId" id="s4" value="4"><label for="s4">+ 1 Movement Allowance</label><br/>';
					echo '<input type="radio" name="skillId" id="s5" value="5"><label for="s5">+ 1 in Armour</label><br/>';		
					break;
				case 11:
					echo '<input type="radio" name="skillId" id="s3" value="3"><label for="s3">+ 1 in Agility</label><br/>';
					break;
				case 12:
					echo '<input type="radio" name="skillId" id="s2" value="2"><label for="s2">+ 1 in Strength</label><br/>';
					break;
			}
			?>
			</td>
		</tr>
	</table>
	</div>
	
	<?php } ?>
		<div style="clear:both; padding-top:20px"><input type="submit" value="Confirm Skill Selection" onClick="this.disabled=1; this.form.submit()"></div>
		</form>
<?php
	}
?>

<script type="text/javascript">
	jQuery(function(){
		jQuery(".tipTip").tipTip({delay: 0, maxWidth: "400px"});
	});
</script>








