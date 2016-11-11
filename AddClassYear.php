<?php
/* $Revision: 1.21 $ */
/* $Id: BankAccounts.php 3845 2010-09-30 14:50:07Z tim_schofield $*/

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Class Management');

include('includes/header.inc');

echo '<p class="page_title_text">' . ' ' . _('Class Management') . '';       

if (isset($_GET['SelectedGradeLevel'])) {
	$SelectedGradeLevel=$_GET['SelectedGradeLevel'];
} elseif (isset($_POST['SelectedGradeLevel'])) {
	$SelectedGradeLevel=$_POST['SelectedGradeLevel'];
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

	$sql="SELECT count(grade_level)
			FROM gradelevels WHERE grade_level='".$_POST['grade_level']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]>0 and !isset($SelectedGradeLevel)) {
		$InputError = 1;
		prnMsg( _('The grade level already exists in the database'),'error');
		$Errors[$i] = 'grade_level';
		$i++;
	}
	
	if (isset($SelectedGradeLevel) AND $InputError !=1) {

		/*Check if there are already transactions against this account - cant allow change currency if there are*/
			$sql = "UPDATE gradelevels
				SET grade_level='" . $_POST['grade_level'] . "',
				next_grade='" . $_POST['next_grade'] . "',
				lower='" . $_POST['lower'] . "'
			WHERE id = '" . $SelectedGradeLevel . "'";
		

		$msg = _('The year details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO gradelevels (grade_level,next_grade,lower
						)
				VALUES ('" . $_POST['grade_level']. "','" . $_POST['next_grade']. "','" . $_POST['lower'] . "'
					)";
		$msg = _('The new grade_level has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The grade level could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the grade level details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg($msg,'success');
		echo '<br>';
		unset($_POST['grade_level']);
		unset($SelectedGradeLevel);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$sql= "SELECT COUNT(yos) FROM registered_students WHERE yos='SelectedGradeLevel'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this class since there are students who have registered for it'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('active registratons for this class');

	}
	$sql= "SELECT COUNT(grade_level_id) FROM classes WHERE grade_level_id='SelectedGradeLevel'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this class since there are students uder it'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('active registratons for this class');

	}
	$sql= "SELECT COUNT(grade_level_id) FROM debtorsmaster WHERE grade_level_id='SelectedGradeLevel'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this class since there are students uder it'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('active registratons for this class');

	}
	
	if (!$CancelDelete) {
		$sql="DELETE FROM classes WHERE id='SelectedGradeLevel'";
		$result = DB_query($sql,$db);
		prnMsg(_('Class deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedGradeLevel);
}

/* Always show the list of accounts */
If (!isset($SelectedGradeLevel)) {
	$sql = "SELECT *
		FROM gradelevels
		ORDER BY id";
	$result = DB_query($sql,$db);
	echo '<table class="enclosed">';	
	echo "<tr><th>" . _('Name') . "</th>
		<th>" . _('Next Grade') . "</th>
		<th>" . _('Level') . "</th>
	</tr>";
	while ($myrow = DB_fetch_array($result)) {
		$sql2 = "SELECT lower
	FROM gradelevels
	WHERE id='".$myrow['id']."'";
	$result2 = DB_query($sql2,$db);
	$myrow2=DB_fetch_array($result2);
	if($myrow2['lower']==1){
	$level=_('lower');
	}
	else{
	$level=_('Upper');
	}

		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href=\"%s&SelectedGradeLevel=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedGradeLevelr=%s&delete=1&grade_level=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow['grade_level'],
			$myrow['next_grade'],
			$level,
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['id'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['id'],
			urlencode($myrow['grade_level']));

	} //END WHILE LIST LOOP
	echo '</table><p>';
}

if (isset($SelectedGradeLevel)) {
	echo '<p>';
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Show all grade levels') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($SelectedGradeLevel) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT *
		FROM gradelevels
		WHERE id='$SelectedGradeLevel'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['grade_level'] = $myrow['grade_level'];
	$_POST['next_grade'] = $myrow['next_grade'];
	$_POST['lower'] = $myrow['lower'];
	
	echo '<input type=hidden name=SelectedGradeLevel VALUE=' . $SelectedGradeLevel . '>';
	echo '<input type=hidden name=grade_level VALUE=' . $_POST['grade_level'] . '>';
	echo '<table class="enclosed"> ';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table class="enclosed"><tr>';

	
}

// Check if details exist, if not set some defaults
if (!isset($_POST['grade_level'])) {
	$_POST['grade_level']='';
}
if (!isset($_POST['next_level'])) {
	$_POST['next_level']='';
}

echo '</br><tr><td>' . _('Grade Level') . ': </td>
			<td><input tabindex="2" ' . (in_array('grade_level',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="grade_level" value="' . $_POST['grade_level'] . '" size=40 maxlength=50></td></tr>
		<tr><td>' . _('Next Grade') . ': </td><td><select tabindex="5" name="next_grade">';


$result = DB_query('SELECT * FROM gradelevels',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['id']==$_POST['grade_level']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['id'] . '>' . $myrow['grade_level'];
} //end while loop
echo '</select></td></tr>';	
echo '<TR><td class="visible">' . _('Lower') . ":</TD><td class=\"visible\"><SELECT name='lower'>";
if ($_POST['lower']==0){
	echo '<OPTION SELECTED VALUE=0>' . _('No');
	echo '<OPTION VALUE=1>' . _('Yes');
} else {
 	echo '<OPTION SELECTED VALUE=1>' . _('Yes');
	echo '<OPTION VALUE=0>' . _('No');
}
'</SELECT></TD></TR>';
echo '</tr></table><br>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Enter Information') .'"></div>';

echo '</form>';
include('includes/footer.inc');
?>
