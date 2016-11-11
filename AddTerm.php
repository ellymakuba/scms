<?php
$PageSecurity = 10;
include('includes/session.inc');
$title = _('Term Maintenance');
include('includes/header.inc');
echo '<p class="page_title_text">' . ' ' . _('Term Maintenance') . ''; 
if (isset($_GET['SelectedTerm'])) {
	$SelectedTerm=$_GET['SelectedTerm'];
} elseif (isset($_POST['SelectedTerm'])) {
	$SelectedTerm=$_POST['SelectedTerm'];
}

if (isset($Errors)) {
	unset($Errors);
}

$Errors = array();
if (isset($_POST['submit'])) {
	$InputError = 0;
	$i=1;
	$sql="SELECT count(title)
			FROM terms WHERE title='".$_POST['title']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]>0 and !isset($SelectedTerm)) {
		$InputError = 1;
		prnMsg( _('The term already exists in the database'),'error');
		$Errors[$i] = 'title';
		$i++;
	}
	
	if (isset($SelectedTerm) AND $InputError !=1) {

		/*Check if there are already transactions against this account - cant allow change currency if there are*/
			$sql = "UPDATE terms
				SET title='" . $_POST['title'] . "'
			WHERE id = '" . $SelectedTerm . "'";
		

		$msg = _('The term details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO terms (title
						)
				VALUES ('" . $_POST['title'] . "'
					)";
		$msg = _('The new term has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The term could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the term details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg($msg,'success');
		echo '<br>';
		unset($_POST['title']);
		unset($SelectedTerm);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$sql= "SELECT COUNT(term_id) FROM collegeperiods WHERE term_id='$SelectedTerm'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		
		if($myrow[0]=1){
		echo '<br> ' . _('There is one period under this department');
		prnMsg(_('Cannot delete this term since there is a period under it'),'warn');
		}
		else{
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('periods under this term');
		prnMsg(_('Cannot delete this term since there are periods under it'),'warn');
		}
	}
	if (!$CancelDelete) {
		$sql="DELETE FROM terms WHERE id='$prnMsg(_('Cannot delete this term since there are periods under it'),'warn');'";
		$result = DB_query($sql,$db);
		prnMsg(_('Term deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedTerm);
}

/* Always show the list of accounts */
If (!isset($SelectedTerm)) {
	$sql = "SELECT *
		FROM terms
		ORDER BY id";
	$result = DB_query($sql,$db);
	echo '<table class="enclosed">';	
	echo "<tr><th>" . _('Term') . "</th>
	</tr>";
	while ($myrow = DB_fetch_array($result)) {
		printf("<td>%s</td>
			<td><a href=\"%s&SelectedTerm=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedTerm=%s&delete=1&title=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow['title'],
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['id'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['id'],
			urlencode($myrow['title']));

	} //END WHILE LIST LOOP
	echo '</table><p>';
}

if (isset($SelectedTerm)) {
	echo '<p>';
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Show all terms') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($SelectedTerm) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT *
		FROM terms
		WHERE id='$SelectedTerm'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['title'] = $myrow['title'];
	
	echo '<input type=hidden name=SelectedTerm VALUE=' . $SelectedTerm . '>';
	echo '<input type=hidden name=title VALUE=' . $_POST['title'] . '>';
	echo '<table class="enclosed"> ';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table class="enclosed"><tr>';	
}
if (!isset($_POST['title'])) {
	$_POST['title']='';
}

echo '<td>' . _('Title') . ': </td>
			<td><input tabindex="2" ' . (in_array('title',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="title" value="' . $_POST['title'] . '" size=40 maxlength=50></td></tr>';
		


echo '</tr></table><br>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Enter Information') .'"></div>';

echo '</form>';
include('includes/footer.inc');
?>
