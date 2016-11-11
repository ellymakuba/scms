<?php
/* $Revision: 1.21 $ */
/* $Id: BankAccounts.php 3845 2010-09-30 14:50:07Z tim_schofield $*/

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Year Maintenance');

include('includes/header.inc');

echo '<p class="page_title_text">' . ' ' . _('Year Maintenance') . '';       

if (isset($_GET['SelectedYear'])) {
	$SelectedYear=$_GET['SelectedYear'];
} elseif (isset($_POST['SelectedYear'])) {
	$SelectedYear=$_POST['SelectedYear'];
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

	$sql="SELECT count(year)
			FROM years WHERE year='".$_POST['year']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]>0 and !isset($SelectedYear)) {
		$InputError = 1;
		prnMsg( _('The year already exists in the database'),'error');
		$Errors[$i] = 'year';
		$i++;
	}
	
	if (isset($SelectedYear) AND $InputError !=1) {

		/*Check if there are already transactions against this account - cant allow change currency if there are*/
			$sql = "UPDATE years
				SET year='" . $_POST['year'] . "',
				approved='" . $_POST['approved'] . "'
			WHERE id = '" . $SelectedYear . "'";
		

		$msg = _('The year details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO years (year
						)
				VALUES ('" . $_POST['year'] . "'
					)";
		$msg = _('The year has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The year could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the course details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg($msg,'success');
		echo '<br>';
		unset($_POST['year']);
		unset($SelectedYear);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$sql= "SELECT COUNT(year) FROM collegeperiods WHERE year='$SelectedYear'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this year since there are periods under it'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('periods under this course');

	}
	if (!$CancelDelete) {
		$sql="DELETE FROM years WHERE id='$SelectedYear'";
		$result = DB_query($sql,$db);
		prnMsg(_('Year deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedYear);
}

/* Always show the list of accounts */
If (!isset($SelectedYear)) {
	$sql = "SELECT *
		FROM years
		ORDER BY id";
	$result = DB_query($sql,$db);
	echo '<table class="enclosed">';	
	echo "<tr><th>" . _('Years') . "</th>
	<th>". _('Principal Approved') . "</th>
	</tr>";
	while ($myrow = DB_fetch_array($result)) {
		$sql2 = "SELECT approved FROM years
		WHERE id='".$myrow['id']."'";
		$result2 = DB_query($sql2,$db);
		$myrow2 = DB_fetch_array($result2);
		$approved=$myrow2['approved'];
		if($approved==1)
		$approved_status=_('Yes');
		else
			$approved_status=_('No');
		printf("<td>%s</td>
			<td>%s</td>
			<td><a href=\"%s&SelectedYear=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedYear=%s&delete=1&year=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow['year'],
			$approved_status,
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['id'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['id'],
			urlencode($myrow['year']));

	} //END WHILE LIST LOOP
	echo '</table><p>';
}

if (isset($SelectedYear)) {
	echo '<p>';
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Show all Years') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($SelectedYear) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT *
		FROM years
		WHERE id='$SelectedYear'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['year'] = $myrow['year'];
	$_POST['approved'] = $myrow['approved'];
	
	echo '<input type=hidden name=SelectedYear VALUE=' . $SelectedYear . '>';
	echo '<input type=hidden name=year VALUE=' . $_POST['year'] . '>';
	echo '<table class="enclosed"> ';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table class="enclosed"><tr>';

	
}

// Check if details exist, if not set some defaults
if (!isset($_POST['year'])) {
	$_POST['year']='';
}
if (!isset($_POST['approved'])) {
	$_POST['approved']='';
}

echo '<td>' . _('Year') . ': </td>
			<td><input tabindex="2" ' . (in_array('year',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="year" value="' . $_POST['year'] . '" size=40 maxlength=50></td></tr>';
			
echo '<TR><td class="visible">' . _('Principal approval') . ":</TD><td class=\"visible\"><SELECT name='approved'>";
if ($_POST['approved']==0){
	echo '<OPTION SELECTED VALUE=0>' . _('No');
	echo '<OPTION VALUE=1>' . _('Yes');
} else {
 	echo '<OPTION SELECTED VALUE=1>' . _('Yes');
	echo '<OPTION VALUE=0>' . _('No');
}		


echo '</tr></table><br>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Enter Information') .'"></div>';

echo '</form>';
include('includes/footer.inc');
?>
