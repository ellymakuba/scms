<?php
/* $Revision: 1.21 $ */
/* $Id: BankAccounts.php 3845 2010-09-30 14:50:07Z tim_schofield $*/

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Marking Period Maintenance');

include('includes/header.inc');

echo '<p class="page_title_text">' . ' ' . _('Marking Period Maintenance') . '';       

if (isset($_GET['SelectedMarkingPeriod'])) {
	$SelectedMarkingPeriod=$_GET['SelectedMarkingPeriod'];
} elseif (isset($_POST['SelectedMarkingPeriod'])) {
	$SelectedMarkingPeriod=$_POST['SelectedMarkingPeriod'];
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

	$sql="SELECT count(title)
			FROM markingperiods WHERE title='".$_POST['title']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]>0 and !isset($SelectedMarkingPeriod)) {
		$InputError = 1;
		prnMsg( _('The marking period already exists in the database'),'error');
		$Errors[$i] = 'marking_period';
		$i++;
	}
	
	if (isset($SelectedMarkingPeriod) AND $InputError !=1) {

		/*Check if there are already transactions against this account - cant allow change currency if there are*/
			$sql = "UPDATE markingperiods
				SET title='" . $_POST['title'] . "',
				exam_type_id='" . $_POST['exam_type_id'] . "',
				priority='" . $_POST['priority'] . "'
			WHERE id = '" . $SelectedMarkingPeriod . "'";
		

		$msg = _('The marking period details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO markingperiods (title,exam_type_id,priority
						)
				VALUES ('" . $_POST['title'] . "','" . $_POST['exam_type_id'] . "','" . $_POST['priority'] . "'
					)";
		$msg = _('The new marking period has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The marking period could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the marking period details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg($msg,'success');
		echo '<br>';
		unset($_POST['title']);
		unset($_POST['exam_type_id']);
		unset($SelectedYear);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$sql= "SELECT COUNT(exam_mode) FROM studentsmarks WHERE exam_mode='$SelectedMarkingPeriod'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this marking period since there are marks under it'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('marsk under this exam mode');

	}
	if (!$CancelDelete) {
		$sql="DELETE FROM markingperiods WHERE id='$SelectedMarkingPeriod'";
		$result = DB_query($sql,$db);
		prnMsg(_('Marking period deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedMarkingPeriod);
}

/* Always show the list of accounts */
If (!isset($SelectedMarkingPeriod)) {
	$sql = "SELECT *
		FROM markingperiods
		ORDER BY priority";
	$result = DB_query($sql,$db);
	echo '<table class="enclosed">';	
	echo "<tr><th>" . _('Title') . "</th>
		<th>" . _('Exam Type') . "</th>
	</tr>";
	while ($myrow = DB_fetch_array($result)) {
	$sql2= "SELECT title
	FROM examtypes
	WHERE id='".$myrow['exam_type_id']."'";
	$result2 = DB_query($sql2,$db);
	$myrow2=DB_fetch_array($result2);
	$title=$myrow2['title'];

		printf("<td>%s</td>
			<td>%s</td>
			<td><a href=\"%s&SelectedMarkingPeriod=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedMarkingPeriod=%s&delete=1&title=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow['title'],
			$title,
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['id'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['id'],
			urlencode($myrow['title']));

	} //END WHILE LIST LOOP
	echo '</table><p>';
}

if (isset($SelectedMarkingPeriod)) {
	echo '<p>';
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Show all marking periods') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($SelectedMarkingPeriod) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT *
		FROM markingperiods
		WHERE id='$SelectedMarkingPeriod'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['title'] = $myrow['title'];
	$_POST['priority'] = $myrow['priority'];
	$_POST['exam_type_id'] = $myrow['exam_type_id'];
	
	echo '<input type=hidden name=SelectedMarkingPeriod VALUE=' . $SelectedMarkingPeriod . '>';
	echo '<input type=hidden name=title VALUE=' . $_POST['title'] . '>';
	echo '<table class="enclosed"> ';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table class="enclosed"><tr>';	
}
if (!isset($_POST['title'])) {
	$_POST['title']='';
}

echo '<td>' . _('title') . ': </td>
			<td><input tabindex="2" ' . (in_array('title',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="title" value="' . $_POST['title'] . '" size=40 maxlength=50></td></tr>
<tr><td>' . _('Priority') . ': </td>
<td><input tabindex="2" ' . (in_array('priority',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="priority" value="' . $_POST['priority'] . '" size=40 maxlength=50></td></tr>			
<tr><td>' . _('Exam Type') . ': </td><td><select tabindex="5" name="exam_type_id">';
$result = DB_query('SELECT * FROM examtypes',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['id']==$_POST['exam_type_id']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['id'] . '>' . $myrow['title'];
} //end while loop

echo '</select></td>';




echo '</select></td>';
		


echo '</tr></table><br>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Enter Information') .'"></div>';

echo '</form>';
include('includes/footer.inc');
?>
