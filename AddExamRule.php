<?php
/* $Revision: 1.21 $ */
/* $Id: BankAccounts.php 3845 2010-09-30 14:50:07Z tim_schofield $*/

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Exam Rules Management');

include('includes/header.inc');

echo '<p class="page_title_text">' . ' ' . _('Exam Rules Management') . '';       

if (isset($_GET['SelectedExamRule'])) {
	$SelectedExamRule=$_GET['SelectedExamRule'];
} elseif (isset($_POST['SelectedExamRule'])) {
	$SelectedExamRule=$_POST['SelectedExamRule'];
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	$i=1;

	$sql="SELECT count(rule)
			FROM exam_rules WHERE rule='".$_POST['rule']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]>0 and !isset($SelectedExamRule)) {
		$InputError = 1;
		prnMsg( _('The exam rule already exists in the database'),'error');
		$Errors[$i] = 'rule';
		$i++;
	}
	
	if (isset($SelectedExamRule) AND $InputError !=1) {

		/*Check if there are already transactions against this account - cant allow change currency if there are*/
			$sql = "UPDATE exam_rules
				SET rule='" . $_POST['rule'] . "',
				range_from='" . $_POST['range_from'] . "',
				range_to='" . $_POST['range_to'] . "'
			WHERE id = '" . $SelectedExamRule . "'";
		

		$msg = _('The exam rule details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO exam_rules (range_from,range_to,rule
						)
				VALUES (
				'" . $_POST['range_from'] . "',
				'" . $_POST['range_to'] . "',
				'" . $_POST['rule'] . "'
					)";
		$msg = _('The new exam rule  has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The exam rule could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the report card grades details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg($msg,'success');
		echo '<br>';
		unset($_POST['rule']);
		unset($_POST['range_from']);
		unset($_POST['range_to']);
		unset($SelectedExamRule);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	if (!$CancelDelete) {
		$sql="DELETE FROM exam_rules WHERE id='$SelectedExamRule'";
		$result = DB_query($sql,$db);
		prnMsg(_('Exam rule deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedExamRule);
}

/* Always show the list of accounts */
If (!isset($SelectedExamRule)) {
	$sql = "SELECT *
		FROM exam_rules
		ORDER BY id";
	$result = DB_query($sql,$db);
	echo '<table class="enclosed">';
	
	echo "<tr><th>" . _('Rule') . "</th>
		<th>" . _('Range From %') . "</th>
		<th>" . _('Range To %') . "</th>
	</tr>";
		while ($myrow = DB_fetch_array($result)) {
		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href=\"%s&SelectedExamRule=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedExamRule=%s&delete=1&title=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow['rule'],
			$myrow['range_from'],
			$myrow['range_to'],
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['id'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['id'],
			urlencode($myrow['rule']));

	} //END WHILE LIST LOOP
	echo '</table><p>';
}

if (isset($SelectedExamRule)) {
	echo '<p>';
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Show all Exam Rules') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($SelectedExamRule) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT *
		FROM exam_rules
		WHERE id='$SelectedExamRule'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['rule'] = $myrow['rule'];
	$_POST['range_from'] = $myrow['range_from'];
	$_POST['range_to'] = $myrow['range_to'];
	
	echo '<input type=hidden name=SelectedExamRule VALUE=' . $SelectedExamRule . '>';
	echo '<input type=hidden name=title VALUE=' . $_POST['title'] . '>';
	echo '<table class="enclosed"> ';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table class="enclosed"><tr>';

	
}

// Check if details exist, if not set some defaults
if (!isset($_POST['rule'])) {
	$_POST['rule']='';
}

echo '<td>' . _('Rule') . ': </td>
			<td><input tabindex="2" ' . (in_array('rule',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="rule" value="' . $_POST['rule'] . '" size=40 maxlength=50></td></tr>	
			<tr><td>' . _('Range From') . ': </td>
                        <td><input tabindex="3" ' . (in_array('range_from',$Errors) ?  'class="inputerror"' : '' ) .' type="range_from" name="range_from" value="' . $_POST['range_from'] . '" size=40 maxlength=50></td></tr>
						<tr><td>' . _('Range To') . ': </td>
                        <td><input tabindex="3" ' . (in_array('range_to',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="range_to" value="' . $_POST['range_to'] . '" size=40 maxlength=50></td></tr>';
		


echo '</tr></table><br>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Enter Information') .'"></div>';

echo '</form>';
include('includes/footer.inc');
?>
