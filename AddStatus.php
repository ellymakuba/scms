<?php
/* $Revision: 1.21 $ */
/* $Id: BankAccounts.php 3845 2010-09-30 14:50:07Z tim_schofield $*/

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Status Management');

include('includes/header.inc');

echo '<p class="page_title_text">' . ' ' . _('Status Management') . '';       

if (isset($_GET['SelectedStatus'])) {
	$SelectedStatus=$_GET['SelectedStatus'];
} elseif (isset($_POST['SelectedStatus'])) {
	$SelectedStatus=$_POST['SelectedStatus'];
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

	$sql="SELECT count(status)
			FROM status WHERE status='".$_POST['title']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]>0 and !isset($SelectedStatus)) {
		$InputError = 1;
		prnMsg( _('The status already exists in the database'),'error');
		$Errors[$i] = 'title';
		$i++;
	}
	
	if (isset($SelectedStatus) AND $InputError !=1) {

		/*Check if there are already transactions against this account - cant allow change currency if there are*/
			$sql = "UPDATE status
				SET status='" . $_POST['title'] . "'
			WHERE id = '" . $SelectedStatus . "'";
		

		$msg = _('The status details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO status (status
						)
				VALUES ('" . $_POST['title'] . "'
					)";
		$msg = _('The new status has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The status could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the status details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg($msg,'success');
		echo '<br>';
		unset($_POST['title']);
		unset($SelectedStatus);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$sql= "SELECT COUNT(title) FROM debtorsmaster WHERE title='$SelectedStatus'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		
		if($myrow[0]=1){
		echo '<br> ' . _('There is one student under this group');
		prnMsg(_('Cannot delete this group since there is a student in it'),'warn');
		}
		else{
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('students in this group');
		prnMsg(_('Cannot delete this group since there are students in it'),'warn');
		}
	}
	if (!$CancelDelete) {
		$sql="DELETE FROM status WHERE id='$SelectedStatus'";
		$result = DB_query($sql,$db);
		prnMsg(_('Group deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedStatus);
}

/* Always show the list of accounts */
If (!isset($SelectedStatus)) {
	$sql = "SELECT *
		FROM status
		ORDER BY id";
	$result = DB_query($sql,$db);

	echo '<table class=enclosed>';
	
	echo "<tr><th>" . _('Group') . "</th>
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

		/*The SecurityHeadings array is defined in config.php */

		printf("<td>%s</td>
			<td><a href=\"%s&SelectedStatus=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedStatus=%s&delete=1&title=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow['status'],
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['id'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['id'],
			urlencode($myrow['status']));

	} //END WHILE LIST LOOP
	echo '</table><p>';
}

if (isset($SelectedStatus)) {
	echo '<p>';
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Show all Groups') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($SelectedStatus) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT *
		FROM status
		WHERE id='$SelectedStatus'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['title'] = $myrow['status'];
	
	echo '<input type=hidden name=SelectedStatus VALUE=' . $SelectedStatus . '>';
	echo '<input type=hidden name=title VALUE=' . $_POST['title'] . '>';
	echo '<table class=enclosed> ';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table class=enclosed><tr>';

	
}

// Check if details exist, if not set some defaults
if (!isset($_POST['title'])) {
	$_POST['title']='';
}

echo '<td>' . _('Group') . ': </td>
			<td><input tabindex="2" ' . (in_array('title',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="title" value="' . $_POST['title'] . '" size=40 maxlength=50></td></tr>';
		


echo '</tr></table><br>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Enter Information') .'"></div>';

echo '</form>';
include('includes/footer.inc');
?>
