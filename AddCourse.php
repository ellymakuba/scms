<?php
/* $Revision: 1.21 $ */
/* $Id: BankAccounts.php 3845 2010-09-30 14:50:07Z tim_schofield $*/

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Program Maintenance');

include('includes/header.inc');

echo '<p class="page_title_text">' . ' ' . _('Program Maintenance') . '';       

if (isset($_GET['SelectedCourse'])) {
	$SelectedCourse=$_GET['SelectedCourse'];
} elseif (isset($_POST['SelectedCourse'])) {
	$SelectedCourse=$_POST['SelectedCourse'];
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

	$sql="SELECT count(course_code)
			FROM courses WHERE course_code='".$_POST['course_code']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]>0 and !isset($SelectedCourse)) {
		$InputError = 1;
		prnMsg( _('The Course code already exists in the database'),'error');
		$Errors[$i] = 'course_code';
		$i++;
	}
	
	if (isset($SelectedCourse) AND $InputError !=1) {

			$sql = "UPDATE courses
				SET course_name='" . $_POST['course_name'] . "',
				course_code='" . $_POST['course_code'] . "',
				course_duration='" . $_POST['course_duration'] . "',
				course_cost='" . $_POST['course_cost'] . "',
				course_type_id='" . $_POST['course_type_id'] . "',
				department_id ='" . $_POST['department_id'] . "',
				additional ='" . $_POST['additional'] . "'
			WHERE courses.id = '" . $SelectedCourse . "'";

		$msg = _('The course details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO courses (
						course_name,
						course_code,
						course_duration,
						course_cost,
						course_type_id,
						department_id,
						additional
						)
				VALUES ('" . $_POST['course_name'] . "',
					'" . $_POST['course_code'] . "',
					'" . $_POST['course_duration'] . "',
					'" . $_POST['course_cost'] . "',
					'" . $_POST['course_type_id'] . "',
					'" . $_POST['department_id'] . "',
					'" . $_POST['additional'] . "'
					)";
		$msg = _('The new course has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The course could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the course details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg($msg,'success');
		echo '<br>';
		unset($_POST['course_code']);
		unset($_POST['course_name']);
		unset($_POST['course_duration']);
		unset($_POST['course-cost']);
		unset($_POST['course_type_id']);
		unset($_POST['department_id']);
		unset($_POST['additional']);
		unset($SelectedCourse);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$sql= "SELECT COUNT(*) FROM subjects WHERE course_id='$SelectedCourse'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this Course since there are subjects under it'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('subjects under this course');

	}
	if (!$CancelDelete) {
		$sql="DELETE FROM courses WHERE id='$SelectedCourse'";
		$result = DB_query($sql,$db);
		prnMsg(_('Course deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedBankAccount);
}

/* Always show the list of accounts */
If (!isset($SelectedCourse)) {
	$sql = "SELECT *
		FROM courses
		ORDER BY id";
	$result = DB_query($sql,$db);

	echo '<table class=enclosed>';
	
	echo "<tr><th>" . _('Program Code') . "</th>
		<th>" . _('Program Name') . "</th>
		<th>" . _('Option') . "</th>
		<th>" . _('Program Duration') . "</th>
		<th>" . _('Program Cost') . "</th>
		<th>" . _('Program Type') . "</th>
		<th>" . _('Department') . "</th>
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

	$sql2 = "SELECT name
	FROM course_types
	WHERE id='".$myrow['course_type_id']."'";
	$result2 = DB_query($sql2,$db);
	$myrow2=DB_fetch_array($result2);
	$course_type=$myrow2['name'];


	$sql3 = "SELECT department_name
	FROM departments
	WHERE id='".$myrow['department_id']."'";
	$result3 = DB_query($sql3,$db);
	$myrow3=DB_fetch_array($result3);
	$department_name=$myrow3['department_name'];
	
		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href=\"%s&SelectedCourse=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedCourse=%s&delete=1&course_code=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow['course_code'],
			$myrow['course_name'],
			$myrow['additional'],
			$myrow['course_duration'],
			$myrow['course_cost'],
			$course_type,
			$department_name,
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['id'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['id'],
			urlencode($myrow['course_code']));

	} //END WHILE LIST LOOP
	echo '</table><p>';
}

if (isset($SelectedCourse)) {
	echo '<p>';
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Show all courses') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($SelectedCourse) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT *
		FROM Courses
		WHERE id='$SelectedCourse'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['course_name'] = $myrow['course_name'];
	$_POST['course_code']  = $myrow['course_code'];
	$_POST['course_duration']  = $myrow['course_duration'];
	$_POST['course_cost'] = $myrow['course_cost'];
	$_POST['course_type_id'] = $myrow['course_type_id'];
	$_POST['department_id'] = $myrow['department_id'];
	$_POST['additional'] = $myrow['additional'];
	
	echo '<input type=hidden name=SelectedCourse VALUE=' . $SelectedCourse . '>';
	echo '<input type=hidden name=course_code VALUE=' . $_POST['course_code'] . '>';
	echo '<table class=enclosed> ';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table class=enclosed><tr>';

	
}

// Check if details exist, if not set some defaults
if (!isset($_POST['course_name'])) {
	$_POST['course_name']='';
}
if (!isset($_POST['course_code'])) {
	$_POST['course_code']='';
}
if (!isset($_POST['course_duration'])) {
        $_POST['course_duration']='';
}
if (!isset($_POST['course_cost'])) {
	$_POST['course_cost']='';
}
if (!isset($_POST['course_type_id'])) {
	$_POST['course_type_id']='';
}
if (!isset($_POST['department_id'])) {
	$_POST['department_id']='';
}
if (!isset($_POST['additional'])) {
	$_POST['additional']='';
}

echo '<td class="visible">' . _('Program Name') . ': </td>
			<td class="visible"><input tabindex="2" ' . (in_array('course_name',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="course_name" value="' . $_POST['course_name'] . '" size=40 maxlength=50></td></tr>
		<tr><td class="visible">' . _('Option') . ': </td>
                       <td class="visible"><input tabindex="3" ' . (in_array('additional',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="additional" value="' . $_POST['additional'] . '" size=40 maxlength=50></td></tr>
	<tr><td class="visible">' . _('Program Code') . ': </td>
                       <td class="visible"><input tabindex="3" ' . (in_array('course_code',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="course_code" value="' . $_POST['course_code'] . '" size=40 maxlength=50></td></tr>				   
		<tr><td class="visible">' . _('Program Duration') . ': </td>
			<td class="visible"><input tabindex="3" ' . (in_array('course_duration',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="course_duration" value="' . $_POST['course_duration'] . '" size=40 maxlength=50></td></tr>
		<tr><td class="visible">' . _('Program Cost') . ': </td>
			<td class="visible"><input tabindex="4" ' . (in_array('course_cost',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="course_cost" value="' . $_POST['course_cost'] . '" size=40 maxlength=50></td></tr>
			<tr><td class="visible">' . _('Course Type') . ': </td><td class="visible"><select tabindex="5" name="course_type_id">';


$result = DB_query('SELECT * FROM course_types',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['id']==$_POST['course_type_id']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['id'] . '>' . $myrow['name'];
} //end while loop

echo '</select></td></tr>
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




echo '</select></td>';

echo '</tr></table><br>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Enter Information') .'"></div>';

echo '</form>';
include('includes/footer.inc');
?>
