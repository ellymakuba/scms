<?php
/* $Revision: 1.21 $ */
/* $Id: BankAccounts.php 3845 2010-09-30 14:50:07Z tim_schofield $*/

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Stream Management');

include('includes/header.inc');

echo '<p class="page_title_text">' . ' ' . _('Stream Management') . '';       

if (isset($_GET['SelectedClass'])) {
	$SelectedClass=$_GET['SelectedClass'];
} elseif (isset($_POST['SelectedClass'])) {
	$SelectedClass=$_POST['SelectedClass'];
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

	
	if (isset($SelectedClass) AND $InputError !=1) {
	$sql="SELECT id FROM classes WHERE class_name='".$_POST['class_name']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_array($result);
	$class_id=$myrow['id'];
	
	$sql = "UPDATE debtorsmaster SET 		 
grade_level_id='".$_POST['grade_level_id']."'
WHERE class_id = '".$class_id."'";
$query = DB_query($sql,$db);


	
			$sql = "UPDATE classes
				SET class_name='" . $_POST['class_name'] . "',
				grade_level_id='" . $_POST['grade_level_id'] . "',
				next_stream='" . $_POST['next_stream'] . "',
				previous_stream='" . $_POST['previous_stream'] . "'
			WHERE id = '" . $SelectedClass . "'";
		
		$msg = _('The class details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO classes (
						class_name,
						grade_level_id,
						next_stream,
						previous_stream
						)
				VALUES ('" . $_POST['class_name'] . "',
					'" . $_POST['grade_level_id'] . "',
					'" . $_POST['next_stream'] . "',
					'" . $_POST['previous_stream'] . "'
					)";
		$msg = _('The new class has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The class could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the class details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg($msg,'success');
		echo '<br>';
		unset($_POST['class_name']);
		unset($_POST['previous_stream']);
		unset($_POST['next_stream']);
		unset($_POST['grade_level_id']);
		unset($SelectedClass);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$sql= "SELECT COUNT(class_id) FROM registered_students WHERE class_id='$SelectedClass'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this class since there are students who have registered for it'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('active registratons for this class');

	}
	
	if (!$CancelDelete) {
		$sql="DELETE FROM classes WHERE id='$SelectedClass'";
		$result = DB_query($sql,$db);
		prnMsg(_('Class deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedClass);
}

/* Always show the list of accounts */
If (!isset($SelectedClass)) {
	$sql = "SELECT *
		FROM classes
		ORDER BY id";
	$result = DB_query($sql,$db);
	echo '<table class="enclosed">';	
	echo "<tr><th>" . _('Stream') . "</th>
		<th>" . _('Class') . "</th>
		<th>" . _('Previous Stream') . "</th>
		<th>" . _('Next Stream') . "</th>
	</tr>";
	while ($myrow = DB_fetch_array($result)) {
	$sql2 = "SELECT grade_level
	FROM gradelevels
	WHERE id='".$myrow['grade_level_id']."'";
	$result2 = DB_query($sql2,$db);
	$myrow2=DB_fetch_array($result2);
	$grade_level=$myrow2['grade_level'];
	
	$sql2 = "SELECT class_name
	FROM classes
	WHERE id='".$myrow['previous_stream']."'";
	$result2 = DB_query($sql2,$db);
	$myrow2=DB_fetch_array($result2);
	$previous=$myrow2['class_name'];
	
	$sql2 = "SELECT class_name
	FROM classes
	WHERE id='".$myrow['next_stream']."'";
	$result2 = DB_query($sql2,$db);
	$myrow2=DB_fetch_array($result2);
	$next=$myrow2['class_name'];
	
	
		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href=\"%s&SelectedClass=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedClass=%s&delete=1&class_name=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow['class_name'],
			$grade_level,
			$previous,
			$next,
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['id'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['id'],
			urlencode($myrow['class_name']));

	} //END WHILE LIST LOOP
	echo '</table><p>';
}

if (isset($SelectedClass)) {
	echo '<p>';
	echo "<br /><div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Show all Streams') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($SelectedClass) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT *
		FROM classes
		WHERE id='$SelectedClass'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['class_name'] = $myrow['class_name'];
	$_POST['grade_level_id']  = $myrow['grade_level_id'];
	$_POST['next_stream'] = $myrow['next_stream'];
	$_POST['previous_stream'] = $myrow['previous_stream'];
	
	
	echo '<input type=hidden name=SelectedClass VALUE=' . $SelectedClass . '>';
	echo '<input type=hidden name=class_name VALUE=' . $_POST['class_name'] . '>';
	echo '<table class="enclosed"> ';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table class="enclosed"><tr>';

	
}

// Check if details exist, if not set some defaults
if (!isset($_POST['class_name'])) {
	$_POST['class_name']='';
}
if (!isset($_POST['grade_level_id'])) {
        $_POST['grade_level_id']='';
}
if (!isset($_POST['next_stream'])) {
        $_POST['next_stream']='';
}
if (!isset($_POST['previous_stream'])) {
        $_POST['previous_stream']='';
}
echo '</br><tr><td>' . _('Class Name') . ': </td>
			<td><input tabindex="2" ' . (in_array('class_name',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="class_name" value="' . $_POST['class_name'] . '" size=40 maxlength=50></td></tr>';

echo '<tr><td>' . _('Grade Level') . ': </td><td><select tabindex="5" name="grade_level_id">';
$result = DB_query('SELECT * FROM gradelevels',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['id']==$_POST['grade_level_id']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['id'] . '>' . $myrow['grade_level'];
} //end while loop

echo '</select></td></tr>';

echo '<tr><td>' . _('Previous Stream') . ': </td><td><select tabindex="5" name="previous_stream">';
$result = DB_query('SELECT * FROM classes',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['id']==$_POST['previous_stream']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['id'] . '>' . $myrow['class_name'];
} //end while loop

echo '</select></td></tr>';
echo '<tr><td>' . _('Next Stream') . ': </td><td><select tabindex="5" name="next_stream">';
$result = DB_query('SELECT * FROM classes',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['id']==$_POST['next_stream']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['id'] . '>' . $myrow['class_name'];
} //end while loop

echo '</select></td></tr>';	
echo '</table><br>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Enter Information') .'"></div>';

echo '</form>';
include('includes/footer.inc');
?>
