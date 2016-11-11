<?php
$PageSecurity = 10;
include('includes/session.inc');
$title = _('Period Maintenance');
include('includes/header.inc');
echo '<p class="page_title_text">' . ' ' . _('Period Maintenance') . '';  
if (isset($_GET['SelectedPeriod'])) {
	$SelectedPeriod=$_GET['SelectedPeriod'];
} elseif (isset($_POST['SelectedPeriod'])) {
	$SelectedPeriod=$_POST['SelectedPeriod'];
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();
if (isset($_POST['submit'])) {
	$InputError = 0;
	$i=1;
	$sql="SELECT start_semester_date
		FROM collegeperiods WHERE  start_semester_date= '".FormatDateForSQL($_POST['start_semester_date'])."'
		OR start_semester_date= '".FormatDateForSQL($_POST['end_semester_date'])."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]>0 and !isset($SelectedPeriod)) {
		$InputError = 1;
		prnMsg( _('Their is a period with the same start date already in the database'),'error');
		$Errors[$i] = 'start_semester_date';
		$i++;
	}
	if (!isset($_POST['start_semester_date'])) {
		$InputError = 1;
		prnMsg( _('The start semester date cannot be empty'),'error');
		$Errors[$i] = 'start_semester_date';
		$i++;
	}
	
	$sql="SELECT COUNT(start_semester_date)
		FROM collegeperiods WHERE  start_semester_date <=  '".FormatDateForSQL($_POST['start_semester_date'])."'
		AND end_semester_date >='".FormatDateForSQL($_POST['end_semester_date'])."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]>0 and !isset($SelectedPeriod)) {
		$InputError = 1;
		prnMsg( _('This date falls in another period'),'error');
		$Errors[$i] = 'start_semester_date';
		$i++;
	}
	
	if (isset($SelectedPeriod) AND $InputError !=1) {

			$sql = "UPDATE collegeperiods
				SET term_id='" . $_POST['term'] . "',
				start_semester_date='" . FormatDateForSQL($_POST['start_semester_date']). "',
				end_semester_date='" . FormatDateForSQL($_POST['end_semester_date']) . "',
				start_marks_posting_date='" . FormatDateForSQL($_POST['start_marks_posting_date']) . "',
				end_marks_posting_date='" . FormatDateForSQL($_POST['end_marks_posting_date']) . "',
				opening_hour='".$_POST['opening_hour']."',
				b_opening_hour='".$_POST['b_opening_hour']."',
				year ='" . $_POST['year'] . "'
			WHERE id = '" . $SelectedPeriod . "'";

		$msg = _('The period details have been updated');
	} elseif ($InputError !=1) {
$sql = "SELECT id FROM collegeperiods
		WHERE term_id='".$_POST['term'] ."'
		AND year='". $_POST['year'] ."'";
		$result=DB_query($sql,$db);
if(DB_fetch_row($result)>0){
		prnMsg(_('These term and year already exist in the system,please enter a different period'),'warn');
		
}
else{
	$sql = "INSERT INTO collegeperiods (
			term_id,
			start_semester_date,
			end_semester_date,
			start_marks_posting_date,
			end_marks_posting_date,
			year,
			opening_hour,
			b_opening_hour
						)
				VALUES ('" . $_POST['term'] . "',
					'" . FormatDateForSQL($_POST['start_semester_date']) . "',
					'" . FormatDateForSQL($_POST['end_semester_date']) . "',
					'" . FormatDateForSQL($_POST['start_marks_posting_date']) . "',
					'" . FormatDateForSQL($_POST['end_marks_posting_date']) . "',
					'" . $_POST['year'] . "',
					'" . $_POST['opening_hour'] . "',
					'" . $_POST['b_opening_hour'] . "'
					)";
		$msg = _('The new period has been entered');
	}
}
	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The period could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the period details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		echo '<br>';
		unset($_POST['start_semester_date']);
		unset($_POST['end_semester_date']);
		unset($_POST['start_marks_posting_date']);
		unset($_POST['end_marks_posting_date']);
		unset($_POST['term']);
		unset($_POST['year']);
		unset($_POST['opening_hour']);
		unset($_POST['b_opening_hour']);
		unset($SelectedPeriod);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$sql= "SELECT COUNT(*) FROM registered_students WHERE period_id='$SelectedPeriod'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		prnMsg(_('Cannot delete this period since there are students under it'),'warn');
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('students under this period');

	}
	if (!$CancelDelete) {
		$sql="DELETE FROM collegeperiods WHERE id='$SelectedPeriod'";
		$result = DB_query($sql,$db);
		prnMsg(_('period deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedPeriod);
}

/* Always show the list of accounts */
If (!isset($SelectedPeriod)) {
	$sql = "SELECT *
		FROM collegeperiods
		ORDER BY id";
	$result = DB_query($sql,$db);

	echo '<table class=enclosed>';
	
	echo "<tr><th>" . _('Term') . "</th>
		<th>" . _('Start Term Date') . "</th>
		<th>" . _('End Term Date') . "</th>
		<th>" . _('Day Next Opening Date') . "</th>
		<th>" . _('Borders Next Opening Date') . "</th>
		<th>" . _('Day Next Opening Hour') . "</th>
		<th>" . _('Borders Next Opening Hour') . "</th>
		<th>" . _('Year') . "</th>
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

		$sqlterm = "SELECT title
		FROM terms
		WHERE id='".$myrow['term_id']."'";
	$resultterm = DB_query($sqlterm,$db);
	$myrowterm = DB_fetch_array($resultterm);
	$term=$myrowterm['title'];
	
	$sqlterm = "SELECT year
	FROM years
	WHERE id='".$myrow['year']."'";
	$resultterm = DB_query($sqlterm,$db);
	$myrowterm = DB_fetch_array($resultterm);
	$year=$myrowterm['year'];

		printf("<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td>%s</td>
			<td><a href=\"%s&SelectedPeriod=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedPeriod=%s&delete=1&term_id=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$term,
			ConvertSQLDate($myrow['start_semester_date']),
			ConvertSQLDate($myrow['end_semester_date']),
			ConvertSQLDate($myrow['start_marks_posting_date']),
			ConvertSQLDate($myrow['end_marks_posting_date']),
			$myrow['opening_hour'],
			$myrow['b_opening_hour'],
			$year,
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['id'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['id'],
			urlencode($myrow['term_id']));

	} //END WHILE LIST LOOP
	echo '</table><p>';
}

if (isset($SelectedPeriod)) {
	echo '<p>';
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Show all periods') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($SelectedPeriod) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT *
		FROM collegeperiods
		WHERE id='$SelectedPeriod'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['term']  = $myrow['term_id'];
	$_POST['start_semester_date']  = $myrow['start_semester_date'];
	$_POST['end_semester_date'] = $myrow['end_semester_date'];
	$_POST['start_marks_posting_date'] = $myrow['start_marks_posting_date'];
	$_POST['end_marks_posting_date'] = $myrow['end_marks_posting_date'];
	$_POST['year'] = $myrow['year'];
	$_POST['opening_hour'] = $myrow['opening_hour'];
	$_POST['b_opening_hour'] = $myrow['b_opening_hour'];
	echo '<input type=hidden name=SelectedPeriod VALUE=' . $SelectedPeriod . '>';
	echo '<input type=hidden name=term_id VALUE=' . $_POST['term_id'] . '>';
	echo '<table class=enclosed> ';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table class=enclosed><tr>';

	
}

// Check if details exist, if not set some defaults

if (!isset($_POST['term'])) {
	$_POST['term']='';
}
if (!isset($_POST['start_semester_date'])) {
        $_POST['start_semester_date']='';
}
if (!isset($_POST['end_semester_date'])) {
	$_POST['end_semester_date']='';
}
if (!isset($_POST['year'])) {
	$_POST['year']='';
}
if (!isset($_POST['start_marks_posting_date'])) {
	$_POST['start_marks_posting_date']='';
}
if (!isset($_POST['end_marks_posting_date'])) {
	$_POST['end_marks_posting_date']='';
}

echo '<td>' .  _('Term') . ': </td><td><select tabindex="5" name="term">';
$result = DB_query('SELECT id,title FROM terms',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['id']==$_POST['term']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['id'] . '>' . $myrow['title'];
} //end while loop

echo '</select></td></tr>
		<tr><td>' . _('Start Term Date') . ': </td>
			<td><input tabindex="3" ' . (in_array('start_semester_date',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="start_semester_date"  size=40 value="' . ConvertSQLDate($_POST['start_semester_date']) . '" size=40 maxlength=50></td></tr>	
			
<tr><td>' . _('End Term Date') . ': </td>
			<td><input tabindex="3" ' . (in_array('end_semester_date',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="end_semester_date"  size=40 value="' . ConvertSQLDate($_POST['end_semester_date']) . '" size=40 maxlength=50></td></tr>			
		
<tr><td>' . _('Dayscolars Next Opening Date') . ': </td>
			<td><input tabindex="3" ' . (in_array('start_marks_posting_date',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="start_marks_posting_date"  size=40 value="' . ConvertSQLDate($_POST['start_marks_posting_date']) . '" size=40 maxlength=50></td></tr>
			
<tr><td>' . _('Dayscolars Next Opening Hour') . ': </td>
			<td><input tabindex="3" ' . (in_array('opening_hour',$Errors) ?  'class="inputerror"' : '' ) .' type="Text"  name="opening_hour"  size=40 value="' . $_POST['opening_hour']. '" size=40 maxlength=50></td></tr>				
			
<tr><td>' . _('Borders Next Opening Date') . ': </td>
<td><input tabindex="3" ' . (in_array('end_marks_posting_date',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" name="end_marks_posting_date"  size=40 value="' . ConvertSQLDate($_POST['end_marks_posting_date']) . '" size=40 maxlength=50></td></tr>	

<tr><td>' . _('Borders Next Opening Hour') . ': </td>
			<td><input tabindex="3" ' . (in_array('b_opening_hour',$Errors) ?  'class="inputerror"' : '' ) .' type="Text"  name="b_opening_hour"  size=40 value="' . $_POST['b_opening_hour']. '" size=40 maxlength=50></td></tr>	';
							
	echo	'<tr><td>' . _('Year') . ': </td><td><select tabindex="5" name="year">';
$result = DB_query('SELECT id,year FROM years',$db);
while ($myrow = DB_fetch_array($result)) {
	if ($myrow['id']==$_POST['year']) {
		echo '<option selected VALUE=';
	} else {
		echo '<option VALUE=';
	}
	echo $myrow['id'] . '>' . $myrow['year'];
} //end while loop

echo '</select></td>';

echo '</tr></table><br>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Enter Information') .'"></div>';

echo '</form>';
include('includes/footer.inc');
?>
