<?php
/* $Id: PcTypeTabs.php 3924 2010-09-30 15:10:30Z tim_schofield $ */

$PageSecurity = 15;

include('includes/session.inc');
$title = _('Add/Edit college Period');
include('includes/header.inc');

echo '<p class="page_title_text">' . ' ' . _('College Period Maintenance') . '';


if (isset($Errors)) {
	unset($Errors);
}
$Errors = array();

if (isset($_POST['submit'])) {
$InputError = 0;
	$i=1;
	$sql="SELECT COUNT(start_date) FROM collegeperiods WHERE start_date='".$_POST['start_date']."'";
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_row($result);
	if ($myrow[0]>0) {
		$InputError = 1;
		prnMsg( _('The period already exists in the database'),'error');
		$Errors[$i] = 'start_date';
		$i++;
	}
	
	elseif ($InputError ==0){	
	$sql = "INSERT INTO collegeperiods (title,term_id,start_date,end_date,year) 
		VALUES ('" . $_POST['title'] ."','" . $_POST['term'] ."','" . FormatDateForSQL($_POST['start_date']) ."','" . FormatDateForSQL($_POST['end_date']) ."','" . $_POST['year'] ."') ";

	$ErrMsg = _('This period could not be added because');
	$result = DB_query($sql,$db,$ErrMsg);
	prnMsg( _('Period Added'),'success');
	include('includes/footer.inc');
			exit;
	
		
	}
}
	echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class=enclosed cellspacing=4><tr><td valign=top><table class=enclosed>';
	
	echo '<tr><td>' . _('Exam') . ":</td>
		<td><select name='title'>";
		$sql="SELECT id,title FROM markingperiods ";
		$result=DB_query($sql,$db);
		echo '<OPTION SELECTED VALUE=0>' . _('Select Title');
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'] . '>' . $myrow['title'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td></tr>';
	echo '<tr><td>' . _('Term') . ":</td>
		<td><select name='term'>";
		$sql="SELECT id,title FROM terms ";
		$result=DB_query($sql,$db);
		echo '<OPTION SELECTED VALUE=0>' . _('Select Term');
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'] . '>' . $myrow['title'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td></tr>';
	echo '<tr>
			<td colspan=5><tr><td>'._('Start Date').":</td>
			<td><input type='text' class='date' alt='".$_SESSION['DefaultDateFormat']."' name='start_date' maxlength=10 size=11></td></tr>";
	echo '<tr>
			<td colspan=5><tr><td>'._('End Date').":</td>
			<td><input type='text' class='date' alt='".$_SESSION['DefaultDateFormat']."' name='end_date' maxlength=10 size=11></td></tr>";
echo '<tr><td>' . _('Year') . ":</td>
		<td><select name='year'>";
		$sql="SELECT id,year FROM years ";
		$result=DB_query($sql,$db);
		echo '<OPTION SELECTED VALUE=0>' . _('Select Year');
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'] . '>' . $myrow['year'];
		} //end while loop
		DB_data_seek($result,0);
		echo '</select></td></tr>';	
		echo '</table></td><td><table class=enclosed>';
		echo'</table></td></tr></table>';
?>
<?php 
echo "<br><div class='centre'><input tabindex=20 type='Submit' name='submit' value='" . _('Add College Period') . "'>&nbsp;<input tabindex=21 type=submit action=RESET VALUE='" . _('Reset') . "'></div>"; ?>
<?php
include('includes/footer.inc');
?>