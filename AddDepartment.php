<?php
/* $Revision: 1.21 $ */
/* $Id: BankAccounts.php 3845 2010-09-30 14:50:07Z tim_schofield $*/

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Department Maintenance');

include('includes/header.inc');

echo '<p class="page_title_text">' . ' ' . _('Department Maintenance') . '';       

if (isset($_GET['SelectedDepartment'])) {
	$SelectedDepartment=$_GET['SelectedDepartment'];
} elseif (isset($_POST['SelectedDepartment'])) {
	$SelectedDepartment=$_POST['SelectedDepartment'];
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

	$sql="SELECT count(department_name)
			FROM departments WHERE department_name='".$_POST['department_name']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]>0 and !isset($SelectedDepartment)) {
		$InputError = 1;
		prnMsg( _('The department already exists in the database'),'error');
		$Errors[$i] = 'department_name';
		$i++;
	}
	
	if (isset($SelectedDepartment) AND $InputError !=1) {

		/*Check if there are already transactions against this account - cant allow change currency if there are*/
			$sql = "UPDATE departments
				SET department_name='" . $_POST['department_name'] . "'
			WHERE id = '" . $SelectedDepartment . "'";
		

		$msg = _('The departments details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO departments (department_name
						)
				VALUES ('" . $_POST['department_name'] . "'
					)";
		$msg = _('The new department has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The department could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the department details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg($msg,'success');
		echo '<br>';
		unset($_POST['department_name']);
		unset($SelectedDepartment);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$sql= "SELECT COUNT(department_id) FROM subjects WHERE department_id='$SelectedDepartment'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this department since there are courses under it'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('courses under this department');

	}
	if (!$CancelDelete) {
		$sql="DELETE FROM departments WHERE id='$SelectedDepartment'";
		$result = DB_query($sql,$db);
		prnMsg(_('Department deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedDepartment);
}

/* Always show the list of accounts */
If (!isset($SelectedDepartment)) {
	$sql = "SELECT *
		FROM departments
		ORDER BY id";
	$result = DB_query($sql,$db);
	echo '<table class="enclosed">';	
	echo "<tr><th>" . _('Department') . "</th>
	</tr>";
	while ($myrow = DB_fetch_array($result)) {
		printf("<td>%s</td>
			<td><a href=\"%s&SelectedDepartment=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedDepartment=%s&delete=1&department_name=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow['department_name'],
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['id'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['id'],
			urlencode($myrow['department_name']));

	} //END WHILE LIST LOOP
	echo '</table><p>';
}

if (isset($SelectedDepartment)) {
	echo '<p>';
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Show all departments') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($SelectedDepartment) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT *
		FROM departments
		WHERE id='$SelectedDepartment'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['department_name'] = $myrow['department_name'];
	
	echo '<input type=hidden name=SelectedDepartment VALUE=' . $SelectedDepartment . '>';
	echo '<input type=hidden name=department_name VALUE=' . $_POST['department_name'] . '>';
	echo '<table class="enclosed"> ';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table class="enclosed"><tr>';	
}
if (!isset($_POST['department_name'])) {
	$_POST['department_name']='';
}

echo '<td>' . _('Department') . ': </td>
			<td><input tabindex="2" ' . (in_array('department_name',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="department_name" value="' . $_POST['department_name'] . '" size=40 maxlength=50></td></tr>';
		


echo '</tr></table><br>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Enter Information') .'"></div>';

echo '</form>';
include('includes/footer.inc');
?>
