<?php
/* $Revision: 1.21 $ */
/* $Id: BankAccounts.php 3845 2010-09-30 14:50:07Z tim_schofield $*/

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Registration Types Management');

include('includes/header.inc');

echo '<p class="page_title_text">' . ' ' . _('Registration Types Management') . '';       

if (isset($_GET['SelectedRegistrationType'])) {
	$SelectedRegistrationType=$_GET['SelectedRegistrationType'];
} elseif (isset($_POST['SelectedRegistrationType'])) {
	$SelectedRegistrationType=$_POST['SelectedRegistrationType'];
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

	$sql="SELECT count(name)
			FROM registration_types WHERE name='".$_POST['name']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]>0 and !isset($SelectedRegistrationType)) {
		$InputError = 1;
		prnMsg( _('The registration type already exists in the database'),'error');
		$Errors[$i] = 'name';
		$i++;
	}
	
	if (isset($SelectedRegistrationType) AND $InputError !=1) {

		/*Check if there are already transactions against this account - cant allow change currency if there are*/
			$sql = "UPDATE registration_types
				SET name='" . $_POST['name'] . "'
			WHERE id = '" . $SelectedRegistrationType . "'";
		

		$msg = _('The registration type details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO registration_types (name
						)
				VALUES ('" . $_POST['name'] . "'
					)";
		$msg = _('The new registration type has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The registration type could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the registration type details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg($msg,'success');
		echo '<br>';
		unset($_POST['name']);
		unset($SelectedRegistrationType);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$sql= "SELECT COUNT(registration_type_id) FROM registered_students WHERE registration_type_id='$SelectedRegistrationType'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		
		if($myrow[0]=1){
		echo '<br> ' . _('There is one subject under this registration type');
		prnMsg(_('Cannot delete this registration type since there is a course registered under it'),'warn');
		}
		else{
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('courses in this group');
		prnMsg(_('Cannot delete this registration type since there are courses registered under it'),'warn');
		}
	}
	if (!$CancelDelete) {
		$sql="DELETE FROM registered_students WHERE id='$prnMsg(_('Cannot delete this term since there are periods under it'),'warn');'";
		$result = DB_query($sql,$db);
		prnMsg(_('Registration Type deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedRegistrationType);
}

/* Always show the list of accounts */
If (!isset($SelectedRegistrationType)) {
	$sql = "SELECT *
		FROM registration_types
		ORDER BY id";
	$result = DB_query($sql,$db);

	echo '<table class=enclosed>';
	
	echo "<tr><th>" . _('Name') . "</th>
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
			<td><a href=\"%s&SelectedRegistrationType=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedRegistrationType=%s&delete=1&title=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow['name'],
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['id'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['id'],
			urlencode($myrow['name']));

	} //END WHILE LIST LOOP
	echo '</table><p>';
}

if (isset($SelectedRegistrationType)) {
	echo '<p>';
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Show all Registration Types') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($SelectedRegistrationType) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT *
		FROM registration_types
		WHERE id='$SelectedRegistrationType'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['name'] = $myrow['name'];
	
	echo '<input type=hidden name=SelectedRegistrationType VALUE=' . $SelectedRegistrationType . '>';
	echo '<table class=enclosed> ';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table class=enclosed><tr>';

	
}

// Check if details exist, if not set some defaults
if (!isset($_POST['name'])) {
	$_POST['name']='';
}

echo '<td>' . _('Name') . ': </td>
			<td><input tabindex="2" ' . (in_array('name',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="name" value="' . $_POST['name'] . '" size=40 maxlength=50></td></tr>';
		


echo '</tr></table><br>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Enter Information') .'"></div>';

echo '</form>';
include('includes/footer.inc');
?>
