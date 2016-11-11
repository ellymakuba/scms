<?php

/* $Id: UserSettings.php 4020 2010-09-30 16:10:47Z tim_schofield $*/

$PageSecurity=1;

include('includes/session.inc');
$title = _('User Settings');
include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/user.png" title="' .
	_('User Settings') . '" alt="">' . ' ' . _('User Settings') . '</p>';

$PDFLanguages = array(_('Latin Western Languages'),
						_('Eastern European Russian Japanese'),
						_('Chinese'),
						_('Korean'),
						_('Vietnamese'),
						_('Hebrew'),
						_('Arabic'),
						_('Thai'));


if (isset($_POST['Modify'])) {
	// no input errors assumed initially before we test
	$InputError = 0;

	
 	$update_pw = 'N';
	if ($_POST['pass'] != ''){
		if ($_POST['pass'] != $_POST['passcheck']){
			$InputError = 1;
			prnMsg(_('The password and password confirmation fields entered do not match'),'error');
		}else{
			$update_pw = 'Y';
		}
	}
	if ($_POST['passcheck'] != ''){
		if ($_POST['pass'] != $_POST['passcheck']){
			$InputError = 1;
			prnMsg(_('The password and password confirmation fields entered do not match'),'error');
		}else{
			$update_pw = 'Y';
		}
	}

	if ($InputError != 1) {
		// no errors
		if ($update_pw != 'Y'){
			$sql = "UPDATE www_users
				SET email='". $_POST['email'] ."'
				WHERE userid = '" . $_SESSION['UserID'] . "'";

			$ErrMsg =  _('The user alterations could not be processed because');
			$DbgMsg = _('The SQL that was used to update the user and failed was');

			$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

			prnMsg( _('The user settings have been updated') . '. ' . _('Be sure to remember your password for the next time you login'),'success');
	$sql = "SELECT fullaccess FROM www_users
		WHERE userid=  '" . trim($_SESSION['UserID']) . "'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$user=$myrow[0];
		if($user==7){		
			$sql = "UPDATE debtorsmaster
				SET boxno='" . $_POST['boxno'] . "',
					town='" . $_POST['town'] . "',
					zip='" . $_POST['zip'] . "',
					state='" . $_POST['state'] . "',
					mobileno='" . $_POST['mobileno'] . "',
					relationship='" . $_POST['relationship'] . "',
					gname='" . $_POST['gname'] . "',
					gboxno='" . $_POST['gboxno'] . "',
					gtown='" . $_POST['gtown'] . "',
					gstate='" . $_POST['gstate'] . "',
					gmobileno='" . $_POST['gmobileno'] . "',
					email='". $_POST['email'] ."'
				WHERE debtorno = '" . $_SESSION['UserID'] . "'";

			$ErrMsg =  _('The student alterations could not be processed because');
			$DbgMsg = _('The SQL that was used to update the user and failed was');

			$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);
			}	
		} else {
			$sql = "UPDATE www_users
				SET email='". $_POST['email'] ."',
					password='" . CryptPass($_POST['pass']) . "'
				WHERE userid = '" . $_SESSION['UserID'] . "'";

			$ErrMsg =  _('The user alterations could not be processed because');
			$DbgMsg = _('The SQL that was used to update the user and failed was');

			$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);

			prnMsg(_('The user settings have been updated'),'success');
		$sql = "SELECT fullaccess FROM www_users
		WHERE userid=  '" . trim($_SESSION['UserID']) . "'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$user=$myrow[0];
		if($user==7){	
			$sql = "UPDATE debtorsmaster
				SET boxno='" . $_POST['boxno'] . "',
					town='" . $_POST['town'] . "',
					zip='" . $_POST['zip'] . "',
					state='" . $_POST['state'] . "',
					mobileno='" . $_POST['mobileno'] . "',
					relationship='" . $_POST['relationship'] . "',
					gname='" . $_POST['gname'] . "',
					gboxno='" . $_POST['gboxno'] . "',
					gtown='" . $_POST['gtown'] . "',
					gstate='" . $_POST['gstate'] . "',
					gmobileno='" . $_POST['gmobileno'] . "',
					email='". $_POST['email'] ."'
				WHERE debtorno = '" . $_SESSION['UserID'] . "'";

			$ErrMsg =  _('The student alterations could not be processed because');
			$DbgMsg = _('The SQL that was used to update the user and failed was');

			$result = DB_query($sql,$db, $ErrMsg, $DbgMsg);
			}
		}
	 
		include ('includes/LanguageSetup.php');

	}
}

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '?' . SID . '>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

echo '<table class=enclosed><tr><td>';
echo '<table class=enclosed><tr><td colspan="2"><h3>User Profile</h3></td></tr>';
echo '<tr><td class="visible">' . _('User ID') . ':</td><td class="visible">';
echo $_SESSION['UserID'] . '</td></tr>';

echo '<tr><td class="visible">' . _('User Name') . ':</td><td class="visible">';
echo $_SESSION['UsersRealName'] . '</td>
		<input type="hidden" name="RealName" VALUE="'.$_SESSION['UsersRealName'].'"<td></tr>';

if (!isset($_POST['passcheck'])) {
	$_POST['passcheck']='';
}
if (!isset($_POST['pass'])) {
	$_POST['pass']='';
}
echo '</select></td></tr>
	<tr><td class="visible">' . _('New Password') . ":</td>
	<td class=\"visible\"><input type='password' name='pass' size=20 value='" .  $_POST['pass'] . "'></td></tr>
	<tr><td class=\"visible\">" . _('Confirm Password') . ":</td>
	<td class=\"visible\"><input type='password' name='passcheck' size=20  value='" . $_POST['passcheck'] . "'></td></tr>
	<tr><td colspan=2 align='center'><i>" . _('If you leave the password boxes empty your password will not change') . '</i></td></tr>
	<tr><td class="visible">' . _('Email') . ':</td>';

$sql = "SELECT email from www_users WHERE userid = '" . $_SESSION['UserID'] . "'";
$result = DB_query($sql,$db);
$myrow = DB_fetch_array($result);
if(!isset($_POST['email'])){
	$_POST['email'] = $myrow['email'];
}

echo "<td class=\"visible\"><input type=text name='email' size=40 value='" . $_POST['email'] . "'></td></tr>";
$sql = "SELECT fullaccess FROM www_users
		WHERE userid=  '" . trim($_SESSION['UserID']) . "'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$user=$myrow[0];
echo '</select></table></td>';
if($user==7){
$sql = "SELECT * from debtorsmaster WHERE debtorno = '" . trim($_SESSION['UserID']) . "'";
$result = DB_query($sql,$db);
$myrow = DB_fetch_array($result);
if(!isset($_POST['boxno'])){
	$_POST['boxno'] = $myrow['boxno'];
}
if(!isset($_POST['town'])){
	$_POST['town'] = $myrow['town'];
}
if(!isset($_POST['zip'])){
	$_POST['zip'] = $myrow['zip'];
}
if(!isset($_POST['state'])){
	$_POST['state'] = $myrow['state'];
}
if(!isset($_POST['mobileno'])){
	$_POST['mobileno'] = $myrow['mobileno'];
}
if(!isset($_POST['relationship'])){
	$_POST['relationship'] = $myrow['relationship'];
}
if(!isset($_POST['gname'])){
	$_POST['gname'] = $myrow['gname'];
}
if(!isset($_POST['gmobileno'])){
	$_POST['gmobileno'] = $myrow['gmobileno'];
}
if(!isset($_POST['gboxno'])){
	$_POST['gboxno'] = $myrow['gboxno'];
}
if(!isset($_POST['gtown'])){
	$_POST['gtown'] = $myrow['gtown'];
}
if(!isset($_POST['gstate'])){
	$_POST['gstate'] = $myrow['gstate'];
}
echo '<td><table class=enclosed>';
echo '<tr><td colspan="2"><h3>Student Contact</h3></td></tr>';
echo '<tr><td class="visible">' . _('P.O BOX') . ':</td>
			<td class="visible"><input type="Text" name="boxno" size=30 maxlength=40 value="' . $_POST['boxno'] . '"></td></tr>';
		echo '<tr><td class="visible">' . _('Town') . ':</td>
			<td class="visible"><input  type="Text" name="town" size=30 maxlength=40 value="' . $_POST['town'] . '"></td></tr>';
		echo '<tr><td class="visible">' . _('Postal Code') . ':</td>
			<td class="visible"><input  type="Text" name="zip" size=30 maxlength=40 value="' . $_POST['zip'] . '"></td></tr>';
		echo '<tr><td class="visible">' . _('State') . ':</td>
			<td class="visible"><input  type="Text" name="state" size=30 maxlength=40 value="' . $_POST['state'] . '"></td></tr>';
		echo '<tr><td class="visible">' . _('Mobile No') . ':</td>
			<td class="visible"><input  type="Text" name="mobileno" size=30 maxlength=40 value="' . $_POST['mobileno'] . '"></td></tr>';			
	echo '</select></table></td><td><table class=enclosed>';
echo '<tr><td colspan="2"><h3>Guardian Contact</h3></td></tr>';
echo '<tr><td class="visible">' . _('Relationship to Student') . ':</td>
			<td class="visible"><input  type="Text" name="relationship" size=30 maxlength=40 value="' . $_POST['relationship'] . '"></td></tr>';	
	echo '<tr><td class="visible">' . _('Full Name') . ':</td>
			<td class="visible"><input  type="Text" name="gname" size=30 maxlength=40 value="' . $_POST['gname'] . '"></td></tr>';
echo '<tr><td class="visible">' . _('Mobile No') . ':</td>
			<td class="visible"><input  type="Text" name="gmobileno" size=30 maxlength=40 value="' . $_POST['gmobileno'] . '"></td></tr>';
	echo '<tr><td class="visible">' . _('P.O BOX') . ':</td>
			<td class="visible"><input  type="Text" name="gboxno" size=30 maxlength=40 value="' . $_POST['gboxno'] . '"></td></tr>';
		echo '<tr><td class="visible">' . _('Town') . ':</td>
			<td class="visible"><input  type="Text" name="gtown" size=30 maxlength=40 value="' . $_POST['gtown'] . '"></td></tr>';
		echo '<tr><td class="visible">' . _('Postal Code') . ':</td>
			<td class="visible"><input  type="Text" name="gstate" size=30 maxlength=40 value="' . $_POST['gstate'] . '"></td></tr>';
	echo '</select></table>';		
}		

echo '</select></td></tr></table>';
echo "<br /><div class='centre'><input type='Submit' name='Modify' value=" . _('Modify') . '></div>
	</form>';

include('includes/footer.inc');

?>