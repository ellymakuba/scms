<?php
/* $Revision: 1.21 $ */
/* $Id: BankAccounts.php 3845 2010-09-30 14:50:07Z tim_schofield $*/

$PageSecurity = 10;

include('includes/session.inc');

$title = _('Estate Management');

include('includes/header.inc');

echo '<p class="page_title_text">' . ' ' . _('Estate Management') . '';       

if (isset($_GET['SelectedEsate'])) {
	$SelectedEsate=$_GET['SelectedEsate'];
} elseif (isset($_POST['SelectedEsate'])) {
	$SelectedEsate=$_POST['SelectedEsate'];
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

	$sql="SELECT count(estate)
			FROM estates WHERE estate='".$_POST['estate']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_row($result);

	if ($myrow[0]>0 and !isset($SelectedEsate)) {
		$InputError = 1;
		prnMsg( _('The estate already exists in the database'),'error');
		$Errors[$i] = 'estate';
		$i++;
	}
	
	if (isset($SelectedEsate) AND $InputError !=1) {

		/*Check if there are already transactions against this account - cant allow change currency if there are*/
			$sql = "UPDATE estates
				SET estate='" . $_POST['estate'] . "'
			WHERE id = '" . $SelectedEsate . "'";
		

		$msg = _('The status details have been updated');
	} elseif ($InputError !=1) {

	/*Selectedbank account is null cos no item selected on first time round so must be adding a    record must be submitting new entries in the new bank account form */

		$sql = "INSERT INTO estates (estate,transport
						)
				VALUES ('" . $_POST['estate'] . "','" . $_POST['transport'] . "'
					)";
		$msg = _('The new estate has been entered');
	}

	//run the SQL from either of the above possibilites
	if( $InputError !=1 ) {
		$ErrMsg = _('The status could not be inserted or modified because');
		$DbgMsg = _('The SQL used to insert/modify the estate details was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);

		prnMsg($msg,'success');
		echo '<br>';
		unset($_POST['estate']);
		unset($_POST['transport']);
		unset($SelectedEsate);
	}


} elseif (isset($_GET['delete'])) {
//the link to delete a selected record was clicked instead of the submit button

	$CancelDelete = 0;

// PREVENT DELETES IF DEPENDENT RECORDS IN 'BankTrans'

	$sql= "SELECT COUNT(estate_id) FROM debtorsmaster WHERE estate_id='$SelectedEsate'";
	$result = DB_query($sql,$db);
	$myrow = DB_fetch_row($result);
	if ($myrow[0]>0) {
		$CancelDelete = 1;
		
		if($myrow[0]=1){
		echo '<br> ' . _('There is one student under this estate');
		prnMsg(_('Cannot delete this estate since there is a student under it'),'warn');
		}
		else{
		echo '<br> ' . _('There are') . ' ' . $myrow[0] . ' ' . _('students in this estate');
		prnMsg(_('Cannot delete this estate since there are students under it'),'warn');
		}
	}
	if (!$CancelDelete) {
		$sql="DELETE FROM estates WHERE id='$SelectedEsate'";
		$result = DB_query($sql,$db);
		prnMsg(_('Estate deleted'),'success');
	} //end if Delete bank account

	unset($_GET['delete']);
	unset($SelectedEsate);
}

/* Always show the list of accounts */
If (!isset($SelectedEsate)) {
	$sql = "SELECT *
		FROM estates
		ORDER BY id";
	$result = DB_query($sql,$db);

	echo '<table class=enclosed>';
	
	echo "<tr><th>" . _('Estate') . "</th>
	<th>" . _('Transport Fee') . "</th>
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
			<td>%s</td>
			<td><a href=\"%s&SelectedEsate=%s\">" . _('Edit') . "</a></td>
			<td><a href=\"%s&SelectedEsate=%s&delete=1&title=%s\">" . _('Delete') . "</a></td>
			</tr>",
			$myrow['estate'],
			$myrow['transport'],
			$_SERVER['PHP_SELF']  . "?" . SID,
			$myrow['id'],
			$_SERVER['PHP_SELF'] . "?" . SID,
			$myrow['id'],
			urlencode($myrow['status']));

	} //END WHILE LIST LOOP
	echo '</table><p>';
}

if (isset($SelectedEsate)) {
	echo '<p>';
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Show all estates') . '</a></div>';
	echo '<p>';
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($SelectedEsate) AND !isset($_GET['delete'])) {
	//editing an existing bank account  - not deleting

	$sql = "SELECT *
		FROM estates
		WHERE id='$SelectedEsate'";

	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['estate'] = $myrow['estate'];
	
	echo '<input type=hidden name=SelectedEsate VALUE=' . $SelectedEsate . '>';
	echo '<input type=hidden name=title VALUE=' . $_POST['estate'] . '>';
	echo '<table class=enclosed> ';
} else { //end of if $Selectedbank account only do the else when a new record is being entered
	echo '<table class=enclosed><tr>';

	
}

// Check if details exist, if not set some defaults
if (!isset($_POST['estate'])) {
	$_POST['estate']='';
}
if (!isset($_POST['transport'])) {
	$_POST['transport']='';
}
echo '<td>' . _('Estate') . ': </td>
			<td><input tabindex="2" ' . (in_array('estate',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="estate" value="' . $_POST['estate'] . '" size=40 maxlength=30></td></tr>
		<tr><td class="visible">' . _('Transport Fee') . ': </td>
                       <td class="visible"><input tabindex="3" ' . (in_array('transport',$Errors) ?  'class="inputerror"' : '' ) .' type="Text" name="transport" value="' . $_POST['transport'] . '" size=20 maxlength=50></td></tr>	
			';
		


echo '</tr></table><br>
		<div class="centre"><input tabindex="7" type="Submit" name="submit" value="'. _('Enter Information') .'"></div>';

echo '</form>';
include('includes/footer.inc');
?>
