<?php
/* $Revision: 1.21 $ */
/* $Id: BankAccounts.php 3845 2010-09-30 14:50:07Z tim_schofield $*/

$PageSecurity = 10;

include('includes/session.inc');

$title = _('House Management');

include('includes/header.inc');

echo '<p class="page_title_text">' . ' ' . _('House Management') . '';       

if (isset($_GET['SelectedHouse'])) {
	$SelectedHouse=$_GET['SelectedHouse'];
} elseif (isset($_POST['SelectedHouse'])) {
	$SelectedHouse=$_POST['SelectedHouse'];
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

	$sql="SELECT count(house)
			FROM houses WHERE house='".$_POST['house']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]>0 and !isset($SelectedHouse)) {
		$InputError = 1;
		prnMsg( _('The house name already exists in the database'),'error');
		$Errors[$i] = 'house';
		$i++;
	}
	
	if (isset($SelectedHouse) AND $InputError !=1) {

		/*Check if there are already transactions against this account - cant allow change currency if there are*/
			$sql = "UPDATE houses
				SET house='" . $_POST['house'] . "',
				initial='" . $_POST['initial'] . "'
			WHERE id = '" . $SelectedHouse . "'";
		

		$msg = _('The house details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO houses (house,initial)
				VALUES ('" . $_POST['house'] . "','" . $_POST['initial'] . "')";
		$msg = _('The new house name has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The house name could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the house details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg($msg,'success');
		echo '<br>';
		unset($_POST['house']);
		unset($SelectedHouse);
	}


} elseif (isset($_GET['delete'])) {

	$CancelDelete = 0;

	$sql= "SELECT COUNT(house) FROM debtorsmaster WHERE id='$SelectedHouse'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		
		if($myrow[0]=1){
		echo '<br> ' . _('There is one student under this house');
		prnMsg(_('Cannot delete this house since there is a student under it'),'warn');
		}
		else{
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('students in this house');
		prnMsg(_('Cannot delete this house since there are students under it'),'warn');
		}
	}
	if (!$CancelDelete) {
		$sql="DELETE FROM houses WHERE id='$SelectedHouse'";
		$result = DB_query($sql,$db);
		prnMsg(_('Estate deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedHouse);
}

/* Always show the list of accounts */
If (!isset($SelectedHouse)) {
	$sql = "SELECT *
		FROM houses
		ORDER BY house";
	$result = DB_query($sql,$db);

	echo '<table class=enclosed>';
	
	echo "<tr><th>" . _('House') . "</th>
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
			<td><a href=\"%s&SelectedHouse=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedHouse=%s&delete=1&title=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow['house'],
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['id'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['id'],
			urlencode($myrow['status']));

	} //END WHILE LIST LOOP
	echo '</table><p>';
}

if (isset($SelectedHouse)) {
	echo '<p>';
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Show all houses') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($SelectedHouse) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT *
		FROM houses
		WHERE id='$SelectedHouse'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);
	$_POST['house'] = $myrow['house'];
	$_POST['initial'] = $myrow['initial'];
	
	echo '<input type=hidden name=SelectedHouse VALUE=' . $SelectedHouse . '>';
	echo '<input type=hidden name=title VALUE=' . $_POST['house'] . '>';
	echo '<table class=enclosed> ';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table class=enclosed><tr>';

	
}

// Check if details exist, if not set some defaults
if (!isset($_POST['house'])) {
	$_POST['house']='';
}
if (!isset($_POST['initial'])) {
	$_POST['initial']='';
}
echo '<tr><td>' . _('House') . ': </td>
			<td><input tabindex="2" ' . (in_array('house',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="house" value="' . $_POST['house'] . '" size=40 maxlength=30></td></tr>';
echo '<tr><td>' . _('Initial') . ': </td>
			<td><input tabindex="2" ' . (in_array('initial',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="initial" value="' . $_POST['initial'] . '" size=40 maxlength=30></td></tr>';			
echo '</table><br>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Enter Information') .'"></div>';

echo '</form>';
include('includes/footer.inc');
?>
