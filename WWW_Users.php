<?php
if (isset($_POST['UserID']) AND isset($_POST['ID'])){
	if ($_POST['UserID'] == $_POST['ID']) {
		$_POST['Language'] = $_POST['UserLanguage'];
	}
}
include('includes/session.inc');
$ModuleList = array(_('Registration Module'),_('Exam Module'),_('Finance Module'),_('Billing Module'),_('Reports Module'),_('Setup'));
$title = _('User Maintenance');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/group_add.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'<br>';
$sql = 'SELECT secroleid,secrolename FROM securityroles ORDER BY secroleid';
$Sec_Result = DB_query($sql, $db);
$SecurityRoles = array();
while( $Sec_row = DB_fetch_row($Sec_Result) ) 
{
	$SecurityRoles[$Sec_row[0]] = $Sec_row[1];
}
DB_free_result($Sec_Result);
if (isset($_GET['SelectedUser'])){
	$SelectedUser = $_GET['SelectedUser'];
} elseif (isset($_POST['SelectedUser'])){
	$SelectedUser = $_POST['SelectedUser'];
}
if (isset($_POST['submit'])) {
	$InputError = 0;
	if (strlen($_POST['UserID'])<3)
	{
		$InputError = 1;
		prnMsg(_('The user ID entered must be at least 4 characters long'),'error');
	} elseif (ContainsIllegalCharacters($_POST['UserID'])) 
	{
		$InputError = 1;
		prnMsg(_('User names cannot contain any of the following characters') . " - ' & + \" \\ " . _('or a space'),'error');
	} elseif (strlen($_POST['Password'])<5){
		if (!$SelectedUser){
			$InputError = 1;
			prnMsg(_('The password entered must be at least 5 characters long'),'error');
		}
	} elseif (strstr($_POST['Password'],$_POST['UserID'])!= False){
		$InputError = 1;
		prnMsg(_('The password cannot contain the user id'),'error');
	} elseif ((strlen($_POST['Cust'])>0) AND (strlen($_POST['BranchCode'])==0)) {
		$InputError = 1;
		prnMsg(_('If you enter a Customer Code you must also enter a Branch Code valid for this Customer'),'error');
	}
	if ((strlen($_POST['BranchCode'])>0) AND ($InputError !=1)) {
		$sql = "SELECT custbranch.debtorno
		FROM custbranch
		WHERE custbranch.debtorno='" . $_POST['Cust'] . "'
		AND custbranch.branchcode='" . $_POST['BranchCode'] . "'";
		$ErrMsg = _('The check on validity of the customer code and branch failed because');
		$DbgMsg = _('The SQL that was used to check the customer code and branch was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		if (DB_num_rows($result)==0)
		{
			prnMsg(_('The entered Branch Code is not valid for the entered Customer Code'),'error');
			$InputError = 1;
		}
	}
	$i=0;
	$ModulesAllowed = '';
	while ($i < count($ModuleList))
	{
		$FormVbl = "Module_" . $i;
		$ModulesAllowed .= $_POST[($FormVbl)] . ',';
		$i++;
	}
	$_POST['ModulesAllowed']= $ModulesAllowed;
	if ($SelectedUser AND $InputError !=1) {
		if (!isset($_POST['Cust']) OR $_POST['Cust']==NULL OR $_POST['Cust']=='')
		{
			$_POST['Cust']='';
			$_POST['BranchCode']='';
		}
		$UpdatePassword = "";
		if ($_POST['Password'] != ""){
			$UpdatePassword = "password='" . CryptPass($_POST['Password']) . "',";
		}

		$sql = "UPDATE www_users SET realname='" . $_POST['RealName'] . "',phone='" . $_POST['Phone'] ."',
		email='" . $_POST['Email'] ."'," . $UpdatePassword . "salesman='" . $_POST['Salesman'] . "',
		fullaccess='" . $_POST['Access'] . "',modulesallowed='" . $ModulesAllowed . "',blocked='" . $_POST['Blocked'] . "'
		WHERE userid = '". $SelectedUser . "'";
		prnMsg( _('The selected user record has been updated'), 'success' );
	} elseif ($InputError !=1) {
		$sql = "INSERT INTO www_users (userid,realname,password,phone,email,fullaccess,modulesallowed)
		VALUES ('" . $_POST['UserID'] . "','" . $_POST['RealName'] ."','" . CryptPass($_POST['Password']) ."',
		'" . $_POST['Phone'] . "','" . $_POST['Email'] ."','" . $_POST['Access'] . "','" . $ModulesAllowed . "')";
		prnMsg( _('A new user record has been inserted'), 'success' );
	}
	if ($InputError!=1){
		$ErrMsg = _('The user alterations could not be processed because');
		$DbgMsg = _('The SQL that was used to update the user and failed was');
		$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
		unset($_POST['UserID']);
		unset($_POST['RealName']);
		unset($_POST['Salesman']);
		unset($_POST['Phone']);
		unset($_POST['Email']);
		unset($_POST['Password']);
		unset($_POST['PageSize']);
		unset($_POST['Access']);
		unset($_POST['DefaultLocation']);
		unset($_POST['ModulesAllowed']);
		unset($_POST['Blocked']);
		unset($_POST['Theme']);
		unset($_POST['UserLanguage']);
		unset($_POST['PDFLanguage']);
		unset($SelectedUser);
	}

} elseif (isset($_GET['delete'])) {
		$sql='SELECT userid FROM audittrail where userid="'. $SelectedUser .'"';
		$result=DB_query($sql, $db);
		if (DB_num_rows($result)!=0) {
			prnMsg(_('Cannot delete user as entries already exist in the audit trail'), 'warn');
		} else {

			$sql="DELETE FROM www_users WHERE userid='" . $SelectedUser . "'";
			$ErrMsg = _('The User could not be deleted because');;
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg(_('User Deleted'),'info');
		}
		unset($SelectedUser);
}
if (isset($SelectedUser)) 
{
	echo "<div class='centre'><a href='" . $_SERVER['PHP_SELF'] ."?" . SID . "'>" . _('Review Existing Users') . '</a></div><br>';
}
echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . "?" . SID . ">";
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
if (isset($SelectedUser)) {
	$sql = "SELECT userid,realname,phone,email,password,salesman,pagesize,fullaccess,defaultlocation,modulesallowed,
	blocked,theme,language,pdflanguage
	FROM www_users
	WHERE userid='" . $SelectedUser . "'";
	$result = DB_query($sql, $db);
	$myrow = DB_fetch_array($result);

	$_POST['UserID'] = $myrow['userid'];
	$_POST['RealName'] = $myrow['realname'];
	$_POST['Phone'] = $myrow['phone'];
	$_POST['Email'] = $myrow['email'];
	$_POST['Salesman'] = $myrow['salesman'];
	$_POST['PageSize'] = $myrow['pagesize'];
	$_POST['Access'] = $myrow['fullaccess'];
	$_POST['DefaultLocation'] = $myrow['defaultlocation'];
	$_POST['ModulesAllowed'] = $myrow['modulesallowed'];
	$_POST['Theme'] = $myrow['theme'];
	$_POST['UserLanguage'] = $myrow['language'];
	$_POST['Blocked'] = $myrow['blocked'];
	$_POST['PDFLanguage'] = $myrow['pdflanguage'];

	echo "<input type='hidden' name='SelectedUser' value='" . $SelectedUser . "'>";
	echo "<input type='hidden' name='UserID' value='" . $_POST['UserID'] . "'>";
	echo "<input type='hidden' name='ModulesAllowed' value='" . $_POST['ModulesAllowed'] . "'>";

	echo '<table class="enclosed"> <tr><td class ="visible">' . _('User code') . ':</td><td class ="visible">';
	echo $_POST['UserID'] . '</td></tr>';

} else { 
	echo '<table class="enclosed"><tr><td class ="visible">' . _('User Login') . ":</td><td class =\"visible\"><input type='text' 
	name='UserID' size=22 maxlength=20 ></td></tr>";
	$i=0;
	if (!isset($_POST['ModulesAllowed'])) {
		$_POST['ModulesAllowed']='';
	}
	foreach($ModuleList as $ModuleName){
		if ($i>0){
			$_POST['ModulesAllowed'] .=',';
		}
		$_POST['ModulesAllowed'] .= '1';
		$i++;
	}
}

if (!isset($_POST['Password'])) {
	$_POST['Password']='';
}
if (!isset($_POST['RealName'])) {
	$_POST['RealName']='';
}
if (!isset($_POST['Phone'])) {
	$_POST['Phone']='';
}
if (!isset($_POST['Email'])) {
	$_POST['Email']='';
}
echo '<tr><td class ="visible">' . _('Password') . ":</td>
	<td class =\"visible\"><input type='password' name='Password' size=22 maxlength=20 value='" . $_POST['Password'] . "'></tr>";
echo '<tr><td class ="visible">' . _('Full Name') . ":</td>
	<td class =\"visible\"><input type='text' name='RealName' value='" . $_POST['RealName'] . "' size=36 maxlength=35></td></tr>";
echo '<tr><td class ="visible">' . _('Telephone No') . ":</td>
	<td><input type='text' name='Phone' value='" . $_POST['Phone'] . "' size=32 maxlength=30></td></tr>";
echo '<tr><td class ="visible">' . _('Email Address') .":</td>
	<td class =\"visible\"><input type='text' name='Email' value='" . $_POST['Email'] ."' size=32 maxlength=55></td></tr>";
echo '<tr><td class ="visible">' . _('Security Role') . ":</td><td class =\"visible\"><select name='Access'>";

foreach ($SecurityRoles as $SecKey => $SecVal) {
	if (isset($_POST['Access']) and $SecKey == $_POST['Access']){
		echo "<option selected value=" . $SecKey . ">" . $SecVal;
	} else {
		echo "<option value=" . $SecKey . ">" . $SecVal;
	}
}
echo '</select></td></tr>';
echo '<input type="hidden" name="ID" value="'.$_SESSION['UserID'].'">';

if (!isset($_POST['Cust'])) {
	$_POST['Cust']='';
}
if (!isset($_POST['BranchCode'])) {
	$_POST['BranchCode']='';
}
if (!isset($_POST['SupplierID'])) {
	$_POST['SupplierID']='';
}
$ModulesAllowed = explode(',',$_POST['ModulesAllowed']);
$i=0;
foreach($ModuleList as $ModuleName){
echo '<tr><td class ="visible">' . _('Display') . ' ' . $ModuleName . ' ' . _('options') . ": </td><td class =\"visible\">
<select name='Module_" . $i . "'>";
	if ($ModulesAllowed[$i]==1 && isset($SelectedUser)){
		echo '<option selected value=1>' . _('Yes') . '</option>';
		echo '<option value=0>' . _('No') . '</option>';
	} else {
	 	echo '<option selected value=0>' . _('No') . '</option>';
		echo '<option value=1>' . _('Yes') . '</option>';
	}
	echo '</select></td></tr>';
	$i++;
}

echo '<tr><td class ="visible">' . _('Account Status') . ":</td><td class =\"visible\"><select name='Blocked'>";
if ($_POST['Blocked']==0){
	echo '<option selected value=0>' . _('Open');
	echo '<option value=1>' . _('Blocked');
} else {
 	echo '<option selected value=1>' . _('Blocked');
	echo '<option value=0>' . _('Open');
}
echo '</select></td></tr>';

echo '</table><br>
	<div class="centre"><input type="submit" name="submit" value="' . _('Enter Information') . '"></div>
	</form>';
	

if (!isset($SelectedUser)) {
	$sql = 'SELECT userid,realname,phone,email,salesman,lastvisitdate,fullaccess,pagesize,theme,language
	FROM www_users';
	$result = DB_query($sql,$db);
	echo '<table class="enclosed">';
	echo "<tr><th>" . _('User Login') . "</th>
		<th>" . _('Full Name') . "</th>
		<th>" . _('Telephone') . "</th>
		<th>" . _('Email') . "</th>
		<th>" . _('Last Visit') . "</th>
		<th>" . _('Security Role') ."</th>
		<th>" . _('Report Size') ."</th>
		<th>" . _('Theme') ."</th>
		<th>" . _('Language') ."</th>
	</tr>";
		while ($myrow = DB_fetch_row($result)) {
	if ($myrow[8]=='') {
		$LastVisitDate = Date($_SESSION['DefaultDateFormat']);
	} else {
		$LastVisitDate = ConvertSQLDate($myrow[8]);
	}
		printf("<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td>%s</td>
					<td><a href=\"%s&SelectedUser=%s\">" . _('Edit') . "</a></td>
					<td><a href=\"%s&SelectedUser=%s&delete=1\">" . _('Delete') . "</a></td>
					</tr>",
					$myrow[0],
					$myrow[1],
					$myrow[2],
					$myrow[3],
					$myrow[4],
					$LastVisitDate,
					$SecurityRoles[($myrow[7])],
					$myrow[8],
					$myrow[9],
					$myrow[10],
					$_SERVER['PHP_SELF']  . "?" . SID,
					$myrow[0],
					$_SERVER['PHP_SELF'] . "?" . SID,
					$myrow[0]);

	} //END WHILE LIST LOOP
	echo '</table><br>';
} //end of ifs and buts!	
	

if (isset($_GET['SelectedUser'])) {
	echo '<script  type="text/javascript">defaultControl(document.forms[0].Password);</script>';
} else {
	echo '<script  type="text/javascript">defaultControl(document.forms[0].UserID);</script>';
}
include('includes/footer.inc');
?>