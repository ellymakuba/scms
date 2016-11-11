<?php
/* $Revision: 1.21 $ */
/* $Id: BankAccounts.php 3845 2010-09-30 14:50:07Z tim_schofield $*/

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Exam Type Maintenance');

include('includes/header.inc');

echo '<p class="page_title_text">' . ' ' . _('Exam Type Maintenance') . '';       

if (isset($_GET['SelectedExamType'])) {
	$SelectedExamType=$_GET['SelectedExamType'];
} elseif (isset($_POST['SelectedExamType'])) {
	$SelectedExamType=$_POST['SelectedExamType'];
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
			FROM examtypes WHERE title='".$_POST['title']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]>0 and !isset($SelectedExamType)) {
		$InputError = 1;
		prnMsg( _('The exam type already exists in the database'),'error');
		$Errors[$i] = 'title';
		$i++;
	}
	
	if (isset($SelectedExamType) AND $InputError !=1) {

		/*Check if there are already transactions against this account - cant allow change currency if there are*/
			$sql = "UPDATE examtypes
				SET title='" . $_POST['title'] . "'
			WHERE id = '" . $SelectedExamType . "'";
		

		$msg = _('The exam type details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO examtypes (title
						)
				VALUES ('" . $_POST['title'] . "'
					)";
		$msg = _('The new exam type period has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The exam type could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the exam type details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg($msg,'success');
		echo '<br>';
		unset($_POST['title']);
		unset($SelectedYear);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$sql= "SELECT COUNT(exam_type_id) FROM markingperiods WHERE exam_type_id='$SelectedExamType'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this exam type since there are marking periods under it'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('periods under this marking period');

	}
	if (!$CancelDelete) {
		$sql="DELETE FROM examtypes WHERE id='$SelectedExamType'";
		$result = DB_query($sql,$db);
		prnMsg(_('Exam type deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedExamType);
}

/* Always show the list of accounts */
If (!isset($SelectedExamType)) {
	$sql = "SELECT *
		FROM examtypes
		ORDER BY id";
	$result = DB_query($sql,$db);
	echo '<table class="enclosed">';	
	echo "<tr><th>" . _('Title') . "</th>
	</tr>";
	while ($myrow = DB_fetch_array($result)) {
		printf("<td>%s</td>
			<td><a href=\"%s&SelectedExamType=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedExamType=%s&delete=1&title=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow['title'],
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['id'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['id'],
			urlencode($myrow['title']));

	} //END WHILE LIST LOOP
	echo '</table><p>';
}

if (isset($SelectedExamType)) {
	echo '<p>';
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Show all Exam Types') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($SelectedExamType) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT *
		FROM examtypes
		WHERE id='$SelectedExamType'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['title'] = $myrow['title'];
	
	echo '<input type=hidden name=SelectedExamType VALUE=' . $SelectedExamType . '>';
	echo '<input type=hidden name=title VALUE=' . $_POST['title'] . '>';
	echo '<table class="enclosed"> ';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table class="enclosed"><tr>';

	
}

// Check if details exist, if not set some defaults
if (!isset($_POST['title'])) {
	$_POST['title']='';
}

echo '<td>' . _('title') . ': </td>
			<td><input tabindex="2" ' . (in_array('title',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="title" value="' . $_POST['title'] . '" size=40 maxlength=50></td></tr>';
		


echo '</tr></table><br>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Enter Information') .'"></div>';

echo '</form>';
include('includes/footer.inc');
?>
