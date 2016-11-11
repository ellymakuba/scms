<?php
/* $Revision: 1.21 $ */
/* $Id: BankAccounts.php 3845 2010-09-30 14:50:07Z tim_schofield $*/

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Report Card Grade Maintenance');

include('includes/header.inc');
echo '<p class="page_title_text">' . ' ' . _('Report Card Grade Maintenance') . '';    

echo '<FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<CENTER><TABLE class="enclosed"><TR><TD>' . _('Grading') . '</TD><TD><SELECT Name="grade_session">';
		DB_data_seek($result, 0);
		$sql = 'SELECT DISTINCT(grading) as grading FROM reportcardgrades';
		$result = DB_query($sql, $db);
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['grading'] == $_POST['grade_session']) {  
				echo '<OPTION SELECTED VALUE=';
			} else {
				echo '<OPTION VALUE=';
			}
			echo $myrow['grading'] . '>' . $myrow['grading'];
		} //end while loop
	echo '</SELECT></TD></TR></table>';
	echo "<INPUT TYPE='Submit' NAME='assign' VALUE='" . _('View') . "'>"; 
if(isset($_POST['assign'])){  
$_SESSION['grading']=$_POST['grade_session'];


If (!isset($SelectedReportCardGrade)) {
	$sql = "SELECT *
		FROM reportcardgrades
		WHERE grading='".$_SESSION['grading']."'
		ORDER BY id";
	$result = DB_query($sql,$db);
	echo '<table class="enclosed">';	
	echo "<tr><th>" . _('Title') . "</th>
		<th>" . _('Comment') . "</th>
		<th>" . _('grade') . "</th>
		<th>" . _('Range From') . "</th>
		<th>" . _('Range To') . "</th>
	</tr>";
	while ($myrow = DB_fetch_array($result)) {
		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href=\"%s&SelectedReportCardGrade=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedReportCardGrade=%s&delete=1&title=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow['title'],
			$myrow['comment'],
			$myrow['grade'],
			$myrow['range_from'],
			$myrow['range_to'],
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['id'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['id'],
			urlencode($myrow['title']));

	} //END WHILE LIST LOOP
	echo '</table><p>';
}

if (isset($SelectedReportCardGrade)) {
	echo '<p>';
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Show all Report card grades') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

}
if (isset($_GET['SelectedReportCardGrade'])) {
	$SelectedReportCardGrade=$_GET['SelectedReportCardGrade'];
} elseif (isset($_POST['SelectedReportCardGrade'])) {
	$SelectedReportCardGrade=$_POST['SelectedReportCardGrade'];
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
			FROM reportcardgrades WHERE title='".$_POST['title']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	
	
	if (isset($SelectedReportCardGrade) AND $InputError !=1) {

		/*Check if there are already transactions against this account - cant allow change currency if there are*/
			$sql = "UPDATE reportcardgrades
				SET grading='" . $_POST['grading'] . "',
				title='" . $_POST['title'] . "',
				comment='" . $_POST['comment'] . "',
				grade='" . $_POST['grade'] . "',
				range_from='" . $_POST['range_from'] . "',
				range_to='" . $_POST['range_to'] . "'
			WHERE id = '" . $SelectedReportCardGrade . "'";
		

		$msg = _('The report card grade details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO reportcardgrades (grading,title,comment,grade,range_from,range_to)
				VALUES ('" . $_POST['grading'] . "',
				'" . $_POST['title'] . "',
				'" . $_POST['comment'] . "',
				'" . $_POST['grade'] . "',
				'" . $_POST['range_from'] . "',
				'" . $_POST['range_to'] . "'
					)";
		$msg = _('The new report card grade type period has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The report card grade could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the report card grades details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg($msg,'success');
		echo '<br>';
		unset($_POST['grading']);
		unset($_POST['title']);
		unset($_POST['comment']);
		unset($_POST['grade']);
		unset($_POST['range_from']);
		unset($_POST['range_to']);
		unset($SelectedReportCardGrade);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	if (!$CancelDelete) {
		$sql="DELETE FROM reportcardgrades WHERE id='$SelectedReportCardGrade'";
		$result = DB_query($sql,$db);
		prnMsg(_('Report card grade deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedReportCardGrade);
}
if (isset($SelectedReportCardGrade) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT *
		FROM reportcardgrades
		WHERE id='$SelectedReportCardGrade'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['grading'] = $myrow['grading'];
	$_POST['title'] = $myrow['title'];
	$_POST['comment'] = $myrow['comment'];
	$_POST['grade'] = $myrow['grade'];
	$_POST['range_from'] = $myrow['range_from'];
	$_POST['range_to'] = $myrow['range_to'];
	
	echo '<input type=hidden name=SelectedReportCardGrade VALUE=' . $SelectedReportCardGrade . '>';
	echo '<input type=hidden name=title VALUE=' . $_POST['title'] . '>';
	echo '<table class="enclosed"> ';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table class="enclosed"><tr>';	
}

if (!isset($_POST['title'])) {
	$_POST['title']='';
}

echo '<td>' . _('Grading') . ': </td>
			<td><input tabindex="2" ' . (in_array('grading',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="grading" value="' . $_POST['grading'] . '" size=40 maxlength=50></td></tr>
<tr><td>' . _('title') . ': </td>
      <td><input tabindex="3" ' . (in_array('title',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="title" value="' . $_POST['title'] . '" size=40 maxlength=50></td></tr>
	  <tr><td>' . _('Comment') . ': </td>
      <td><input tabindex="3" ' . (in_array('comment',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="comment" value="' . $_POST['comment'] . '" size=40 maxlength=50></td></tr>	
	  <tr><td>' . _('Grade') . ': </td>
      <td><input tabindex="3" ' . (in_array('grade',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="grade" value="' . $_POST['grade'] . '" size=40 maxlength=50></td></tr>	
			<tr><td>' . _('Range From') . ': </td>
                        <td><input tabindex="3" ' . (in_array('range_from',$Errors) ?  'class="inputerror"' : '' ) .' type="range_from" name="range_from" value="' . $_POST['range_from'] . '" size=40 maxlength=50></td></tr>
						<tr><td>' . _('Range To') . ': </td>
                        <td><input tabindex="3" ' . (in_array('range_to',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="range_to" value="' . $_POST['range_to'] . '" size=40 maxlength=50></td></tr>';
		


echo '</tr></table><br>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Enter Information') .'"></div>';

echo '</form>';
/* Always show the list of accounts */

include('includes/footer.inc');
?>
