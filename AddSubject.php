<?php
/* $Revision: 1.21 $ */
/* $Id: BankAccounts.php 3845 2010-09-30 14:50:07Z tim_schofield $*/

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Subject Maintenance');

include('includes/header.inc');

echo '<p class="page_title_text">' . ' ' . _('Subject Maintenance') . '';       

if (isset($_GET['SelectedSubject'])) {
	$SelectedSubject=$_GET['SelectedSubject'];
} elseif (isset($_POST['SelectedSubject'])) {
	$SelectedSubject=$_POST['SelectedSubject'];
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

	$sql="SELECT count(subject_code)
			FROM subjects WHERE subject_code='".$_POST['subject_code']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]>0 and !isset($SelectedSubject)) {
		$InputError = 1;
		prnMsg( _('Subject code already exists in the database'),'error');
		$Errors[$i] = 'course_code';
		$i++;
	}
	
	if (isset($SelectedSubject) AND $InputError !=1) {
			$subject=$_POST['subject_name'];
			$subject= strtoupper($subject);
	
			$sql = "UPDATE subjects
				SET subject_name='$subject',
				subject_code='" . $_POST['subject_code'] . "',
				priority='" . $_POST['priority'] . "',
				department_id='" . $_POST['department_id'] . "',
				priority='" . $_POST['priority'] . "',
				grading='" . $_POST['grading'] . "',
				display='" . $_POST['display'] . "',
				lower_display='" . $_POST['lower_display'] . "'
			WHERE subjects.id = '" . $SelectedSubject . "'";
		
		$msg = _('The subject details have been updated');
	} elseif ($InputError !=1) {

	$subject=$_POST['subject_name'];
			$subject= strtoupper($subject);

		$sql = "INSERT INTO subjects (
						subject_name,
						subject_code,
						department_id,
						priority,
						grading,
						display,
						lower_display
						)
				VALUES ('$subject',
					'" . $_POST['subject_code'] . "',
					'" . $_POST['department_id'] . "',
					'" . $_POST['priority'] . "',
					'" . $_POST['grading'] . "',
					'" . $_POST['display'] . "',
					'" . $_POST['lower_display'] . "'
					)";
		$msg = _('The new subject has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The subject could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the subject details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg($msg,'success');
		echo '<br>';
		unset($_POST['subject_code']);
		unset($_POST['grading']);
		unset($_POST['subject_name']);
		unset($_POST['department_id']);
		unset($_POST['priority']);
		unset($_POST['display']);
		unset($SelectedSubject);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$sql= "SELECT COUNT(subject_id) FROM registered_students WHERE subject_id='$SelectedSubject'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this Subject since there are students who have registered for it'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('students taking this subject');

	}
	
	if (!$CancelDelete) {
		$sql="DELETE FROM subjects WHERE id='$SelectedSubject'";
		$result = DB_query($sql,$db);
		prnMsg(_('Subject deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedSubject);
}

/* Always show the list of accounts */
If (!isset($SelectedSubject)) {
	$sql = "SELECT *
		FROM subjects
		ORDER BY priority";
	$result = DB_query($sql,$db);

	echo '<table class="enclosed">';
	
	echo "<tr><th>" . _('Subject Code') . "</th>
		<th>" . _('Subject Name') . "</th>
		<th>" . _('Priority') . "</th>
		<th>" . _('Grading') . "</th>
		<th>" . _('Department') . "</th>
		<th>" . _('Upper Display') . "</th>
		<th>" . _('Lower Display') . "</th>
	</tr>";
	while ($myrow = DB_fetch_array($result)) {
	$sql2 = "SELECT department_name
	FROM departments
	WHERE id='".$myrow['department_id']."'";
	$result2 = DB_query($sql2,$db);
	$myrow2=DB_fetch_array($result2);
	$department=$myrow2['department_name'];

	if($myrow['display']==0){
	$display=_('No');
	}
	else{
	$display=_('Yes');
	}
	
	if($myrow['lower_display']==0){
	$lower_display=_('No');
	}
	else{
	$lower_display=_('Yes');
	}
		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href=\"%s&SelectedSubject=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedSubject=%s&delete=1&subject_code=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow['subject_code'],
			$myrow['subject_name'],
			$myrow['priority'],
			$myrow['grading'],
			$department,
			$display,
			$lower_display,
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['id'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['id'],
			urlencode($myrow['subject_code']));

	} //END WHILE LIST LOOP
	echo '</table><p>';
}

if (isset($SelectedSubject)) {
	echo '<p>';
	echo "<br /><div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Show all Subjects') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($SelectedSubject) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT *
		FROM subjects
		WHERE id='$SelectedSubject'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['subject_name'] = $myrow['subject_name'];
	$_POST['grading'] = $myrow['grading'];
	$_POST['subject_code']  = $myrow['subject_code'];
	$_POST['department_id']  = $myrow['department_id'];
	$_POST['priority']  = $myrow['priority'];
	$_POST['display']  = $myrow['display'];
	$_POST['lower_display']  = $myrow['lower_display'];
	
	echo '<input type=hidden name=SelectedSubject VALUE=' . $SelectedSubject . '>';
	echo '<input type=hidden name=subject_code VALUE=' . $_POST['subject_code'] . '>';
	echo '<table class="enclosed"> ';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table class="enclosed"><tr>';

	
}

// Check if details exist, if not set some defaults
if (!isset($_POST['subject_name'])) {
	$_POST['subject_name']='';
}
if (!isset($_POST['subject_code'])) {
	$_POST['subject_code']='';
}
if (!isset($_POST['department_id'])) {
        $_POST['department_id']='';
}


echo '</br><tr><td class="visible">' . _('Subject Name') . ': </td>
			<td class="visible"><input tabindex="2" ' . (in_array('subject_name',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="subject_name" value="' . $_POST['subject_name'] . '" size=40 maxlength=50 ></td></tr>
		<tr><td class="visible">' . _('Subject Code') . ': </td>
     <td class="visible"><input tabindex="3" ' . (in_array('subject_code',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="subject_code" value="' . $_POST['subject_code'] . '" size=40 maxlength=50 ></td></tr>
	 <tr><td class="visible">' . _('Priority') . ': </td>
     <td class="visible"><input tabindex="3" ' . (in_array('priority',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="priority" value="' . $_POST['priority'] . '" size=40 maxlength=50></td></tr>
		<tr><td class="visible">' . _('Department') . ': </td><td class="visible"><select tabindex="5" name="department_id">';
$result = DB_query('SELECT * FROM departments',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['id']==$_POST['department_id']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['id'] . '>' . $myrow['department_name'];
} //end while loop

echo '</select></td>';

echo '</tr>

<tr><td class="visible">' . _('Grading') . ': </td><td class="visible"><select tabindex="5" name="grading">';
$result = DB_query('SELECT DISTINCT(grading) as grading FROM reportcardgrades',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['grading']==$_POST['grading']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['grading'] . '>' . $myrow['grading'];
} //end while loop

echo '</select></td>';

echo '</tr>';
echo '<TR><td class="visible">' . _('Display') . ":</TD><td class=\"visible\"><SELECT name='display'>";
if ($_POST['display']==0){
	echo '<OPTION SELECTED VALUE=0>' . _('No');
	echo '<OPTION VALUE=1>' . _('Yes');
} else {
 	echo '<OPTION SELECTED VALUE=1>' . _('Yes');
	echo '<OPTION VALUE=0>' . _('No');
}
'</SELECT></TD></TR>';
echo '<TR><td class="visible">' . _('Lower Display') . ":</TD><td class=\"visible\"><SELECT name='lower_display'>";
if ($_POST['lower_display']==0){
	echo '<OPTION SELECTED VALUE=0>' . _('No');
	echo '<OPTION VALUE=1>' . _('Yes');
} else {
 	echo '<OPTION SELECTED VALUE=1>' . _('Yes');
	echo '<OPTION VALUE=0>' . _('No');
}
'</SELECT></TD></TR>';

echo '</table><br>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Enter Information') .'"></div>';

echo '</form>';
include('includes/footer.inc');
?>
