<?php
/* $Revision: 1.21 $ */
/* $Id: BankAccounts.php 3845 2010-09-30 14:50:07Z tim_schofield $*/

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Type Management');

include('includes/header.inc');

echo '<p class="page_title_text">' . ' ' . _('Type Management') . '';       

if (isset($_GET['SelectedType'])) {
	$SelectedType=$_GET['SelectedType'];
} elseif (isset($_POST['SelectedType'])) {
	$SelectedType=$_POST['SelectedType'];
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

	$sql="SELECT count(name)
			FROM course_priorities WHERE name='".$_POST['name']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]>0 and !isset($SelectedType)) {
		$InputError = 1;
		prnMsg( _('The priority already exists in the database'),'error');
		$Errors[$i] = 'name';
		$i++;
	}
	
	if (isset($SelectedType) AND $InputError !=1) {

		/*Check if there are already transactions against this account - cant allow change currency if there are*/
			$sql = "UPDATE course_priorities
				SET name='" . $_POST['name'] . "'
			WHERE id = '" . $SelectedType . "'";
		

		$msg = _('The priority details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO course_priorities (name
						)
				VALUES ('" . $_POST['name'] . "'
					)";
		$msg = _('The priority has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The priority could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the priority details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg($msg,'success');
		echo '<br>';
		unset($_POST['name']);
		unset($SelectedType);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$sql= "SELECT COUNT(name) FROM course_priorities WHERE id='$SelectedType'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this priority type since there are course under it'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('courses under this priority');

	}
	if (!$CancelDelete) {
		$sql="DELETE FROM course_priorities WHERE id='$SelectedType'";
		$result = DB_query($sql,$db);
		prnMsg(_('Priority deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedType);
}

/* Always show the list of accounts */
If (!isset($SelectedType)) {
	$sql = "SELECT *
		FROM course_priorities
		ORDER BY id";
	$result = DB_query($sql,$db);

	echo '<table class=enclosed>';
	
	echo "<tr><th>" . _('Name') . "</th>
	</tr>";
	$k=0; //row colour counter

	while ($myrow = DB_fetch_array($result)) {
		if ($k==1){
			echo '<tr class="EvenTableRows">';
			$k=0;
		} else {
			echo '<tr class="OddTableRows">';
			$k=1;
		}


		printf("<td>%s</td>
			<td><a href=\"%s&SelectedType=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedType=%s&delete=1&year=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow['name'],
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['id'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['id'],
			urlencode($myrow['name']));

	} //END WHILE LIST LOOP
	echo '</table><p>';
}

if (isset($SelectedType)) {
	echo '<p>';
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Show all course priorities') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($SelectedType) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT *
		FROM course_priorities
		WHERE id='$SelectedType'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['name'] = $myrow['name'];
	
	echo '<input type=hidden name=SelectedType VALUE=' . $SelectedType . '>';
	echo '<input type=hidden name=name VALUE=' . $_POST['name'] . '>';
	echo '<table class=enclosed> ';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table class=enclosed><tr>';

	
}

// Check if details exist, if not set some defaults
if (!isset($_POST['name'])) {
	$_POST['name']='';
}

echo '<td>' . _('Name') . ': </td>
			<td><input tabindex="2" ' . (in_array('name',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="name" value="' . $_POST['name'] . '" size=40 maxlength=50></td></tr>';


echo '</table><br>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Enter Information') .'"></div>';

echo '</form>';
include('includes/footer.inc');
?>
