<?php
$PageSecurity =10;
include('includes/session.inc');
$title = _('School Preferences');
include('includes/header.inc');
if (isset($Errors)) {
	unset($Errors);
}
$InputError = 0;
$Errors = array();
$i=1;
if (isset($_POST['submit'])) {
	if (strlen($_POST['CoyName']) > 40 OR strlen($_POST['CoyName'])==0) {
		$InputError = 1;
		prnMsg(_('The school name must be entered and be fifty characters or less long'), 'error');
		$Errors[$i] = 'CoyName';
		$i++;
	}
	if (strlen($_POST['RegOffice1']) >40) {
		$InputError = 1;
		prnMsg(_('The Line 1 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'RegOffice1';
		$i++;
	}
	if (strlen($_POST['RegOffice2']) >40) {
		$InputError = 1;
		prnMsg(_('The Line 2 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'RegOffice2';
		$i++;
	}
	if (strlen($_POST['RegOffice3']) >40) {
		$InputError = 1;
		prnMsg(_('The Line 3 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'RegOffice3';
		$i++;
	}
	if (strlen($_POST['RegOffice4']) >40) {
		$InputError = 1;
		prnMsg(_('The Line 4 of the address must be forty characters or less long'),'error');
		$Errors[$i] = 'RegOffice4';
		$i++;
	}
	if (strlen($_POST['RegOffice5']) >20) {
		$InputError = 1;
		prnMsg(_('The Line 5 of the address must be twenty characters or less long'),'error');
		$Errors[$i] = 'RegOffice5';
		$i++;
	}
	if (strlen($_POST['RegOffice6']) >15) {
		$InputError = 1;
		prnMsg(_('The Line 6 of the address must be fifteen characters or less long'),'error');
		$Errors[$i] = 'RegOffice6';
		$i++;
	}
	if (strlen($_POST['Telephone']) >25) {
		$InputError = 1;
		prnMsg(_('The telephone number must be 25 characters or less long'),'error');
		$Errors[$i] = 'Telephone';
		$i++;
	}
	if (strlen($_POST['Fax']) >35) {
		$InputError = 1;
		prnMsg(_('The website must be 35 characters or less long'),'error');
		$Errors[$i] = 'Fax';
		$i++;
	}
	if (strlen($_POST['Email']) >55) {
		$InputError = 1;
		prnMsg(_('The email address must be 55 characters or less long'),'error');
		$Errors[$i] = 'Email';
		$i++;
	}/*
	if (strlen($_POST['Email'])>0 and !IsEmailAddress($_POST['Email'])) {
		$InputError = 1;
		prnMsg(_('The email address is not correctly formed'),'error');
		$Errors[$i] = 'Email';
		$i++;
	}
*/
	if ($InputError !=1){

		$sql = "UPDATE companies SET
				coyname='" . $_POST['CoyName'] . "',
				companynumber = '" . $_POST['CompanyNumber'] . "',
				gstno='" . $_POST['GSTNo'] . "',
				regoffice1='" . $_POST['RegOffice1'] . "',
				regoffice2='" . $_POST['RegOffice2'] . "',
				regoffice3='" . $_POST['RegOffice3'] . "',
				regoffice4='" . $_POST['RegOffice4'] . "',
				regoffice5='" . $_POST['RegOffice5'] . "',
				regoffice6='" . $_POST['RegOffice6'] . "',
				telephone='" . $_POST['Telephone'] . "',
				fax='" . $_POST['Fax'] . "',
				email='" . $_POST['Email'] . "',
				currencydefault='" . $_POST['CurrencyDefault'] . "',
				debtorsact='" . $_POST['DebtorsAct'] . "',
				pytdiscountact='" . $_POST['PytDiscountAct'] . "',
				creditorsact='" . $_POST['CreditorsAct'] . "',
				payrollact='" . $_POST['PayrollAct'] . "',
				grnact='" . $_POST['GRNAct'] . "',
				exchangediffact='" . $_POST['ExchangeDiffAct'] . "',
				purchasesexchangediffact='" . $_POST['PurchasesExchangeDiffAct'] . "',
				retainedearnings='" . $_POST['RetainedEarnings'] . "',
				gllink_debtors='" . $_POST['GLLink_Debtors'] . "',
				gllink_creditors='" . $_POST['GLLink_Creditors'] . "',
				gllink_stock='" . $_POST['GLLink_Stock'] ."',
				freightact='" . $_POST['FreightAct'] . "'
			WHERE coycode=1";

			$ErrMsg =  _('The campus preferences could not be updated because');
			$result = DB_query($sql,$db,$ErrMsg);
			prnMsg( _('Campus preferences updated'),'success');

			/* Alter the exchange rates in the currencies table */

			/* Get default currency rate */
			$sql='SELECT rate from currencies WHERE currabrev="'.$_POST['CurrencyDefault'].'"';
			$result = DB_query($sql,$db);
			$myrow = DB_fetch_row($result);
			$NewCurrencyRate=$myrow[0];

			/* Set new rates */
			$sql='UPDATE currencies SET rate=rate/"'.$NewCurrencyRate.'"';
			$ErrMsg =  _('Could not update the currency rates');
			$result = DB_query($sql,$db,$ErrMsg);

			/* End of update currencies */

			$ForceConfigReload = True; // Required to force a load even if stored in the session vars
			include('includes/GetConfig.php');
			$ForceConfigReload = False;

	} else {
		prnMsg( _('Validation failed') . ', ' . _('no updates or deletes took place'),'warn');
	}

} /* end of if submit */

	echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') . '" alt="">' . ' ' . $title.'<br>';

echo '<form method="post" action=' . $_SERVER['PHP_SELF'] . '>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table class="enclosed">';

if ($InputError != 1) {
	$sql = "SELECT coyname,
		gstno,
		companynumber,
		regoffice1,
		regoffice2,
		regoffice3,
		regoffice4,
		regoffice5,
		regoffice6,
		telephone,
		fax,
		email,
		currencydefault,
		debtorsact,
		pytdiscountact,
		creditorsact,
		payrollact,
		grnact,
		exchangediffact,
		purchasesexchangediffact,
		retainedearnings,
		gllink_debtors,
		gllink_creditors,
		gllink_stock,
		freightact
	FROM companies
	WHERE coycode=1";



	$ErrMsg =  _('The campus preferences could not be retrieved because');
	$result = DB_query($sql, $db,$ErrMsg);


	$myrow = DB_fetch_array($result);

	$_POST['CoyName'] = $myrow['coyname'];
	$_POST['GSTNo'] = $myrow['gstno'];
	$_POST['CompanyNumber']  = $myrow['companynumber'];
	$_POST['RegOffice1']  = $myrow['regoffice1'];
	$_POST['RegOffice2']  = $myrow['regoffice2'];
	$_POST['RegOffice3']  = $myrow['regoffice3'];
	$_POST['RegOffice4']  = $myrow['regoffice4'];
	$_POST['RegOffice5']  = $myrow['regoffice5'];
	$_POST['RegOffice6']  = $myrow['regoffice6'];
	$_POST['Telephone']  = $myrow['telephone'];
	$_POST['Fax']  = $myrow['fax'];
	$_POST['Email']  = $myrow['email'];
	$_POST['CurrencyDefault']  = $myrow['currencydefault'];
	$_POST['DebtorsAct']  = $myrow['debtorsact'];
	$_POST['PytDiscountAct']  = $myrow['pytdiscountact'];
	$_POST['CreditorsAct']  = $myrow['creditorsact'];
	$_POST['PayrollAct']  = $myrow['payrollact'];
	$_POST['GRNAct'] = $myrow['grnact'];
	$_POST['ExchangeDiffAct']  = $myrow['exchangediffact'];
	$_POST['PurchasesExchangeDiffAct']  = $myrow['purchasesexchangediffact'];
	$_POST['RetainedEarnings'] = $myrow['retainedearnings'];
	$_POST['GLLink_Debtors'] = $myrow['gllink_debtors'];
	$_POST['GLLink_Creditors'] = $myrow['gllink_creditors'];
	$_POST['GLLink_Stock'] = $myrow['gllink_stock'];
	$_POST['FreightAct'] = $myrow['freightact'];
}

echo '<tr><td class="visible">' . _('Name') . ' (' . _('to appear on reports') . '):</td>
	<td class="visible"><input '.(in_array('CoyName',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="1" type="Text" Name="CoyName" value="' . stripslashes($_POST['CoyName']) . '" size=52 maxlength=50></td>
</tr>';
echo '<tr><td class="visible">' . _('Building') . ':</td>
	<td class="visible"><input '.(in_array('RegOffice1',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="4" type="Text" Name="RegOffice1" size=42 maxlength=40 value="' . stripslashes($_POST['RegOffice1']) . '"></td>
</tr>';

echo '<tr><td class="visible">' . _('Street') . ':</td>
	<td class="visible"><input '.(in_array('RegOffice2',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="5" type="Text" Name="RegOffice2" size=42 maxlength=40 value="' . stripslashes($_POST['RegOffice2']) . '"></td>
</tr>';

echo '<tr><td class="visible">' . _('Box') . ':</td>
	<td class="visible"><input '.(in_array('RegOffice3',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="6" type="Text" Name="RegOffice3" size=42 maxlength=40 value="' . stripslashes($_POST['RegOffice3']) . '"></td>
</tr>';

echo '<tr><td class="visible">' . _('Telephone') . ':</td>
	<td><input '.(in_array('RegOffice4',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="7" type="Text" Name="RegOffice4" size=42 maxlength=40 value="' . stripslashes($_POST['RegOffice4']) . '"></td>
</tr>';

echo '<tr><td class="visible">' . _('Town') . ':</td>
	<td class="visible"><input '.(in_array('RegOffice5',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="8" type="Text" Name="RegOffice5" size=22 maxlength=20 value="' . stripslashes($_POST['RegOffice5']) . '"></td>
</tr>';

echo '<tr><td class="visible">' . _('Level') . ':</td>
	<td class="visible"><input '.(in_array('RegOffice6',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="9" type="Text" Name="RegOffice6" size=17 maxlength=15 value="' . stripslashes($_POST['RegOffice6']) . '"></td>
</tr>';

echo '<tr><td class="visible">' . _('Mobile Number') . ':</td>
	<td class="visible"><input '.(in_array('Telephone',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="10" type="Text" Name="Telephone" size=26 maxlength=25 value="' . $_POST['Telephone'] . '"></td>
</tr>';

echo '<tr><td class="visible">' . _('Website') . ':</td>
	<td class="visible"><input '.(in_array('Fax',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="11" type="Text" Name="Fax" size=40 maxlength=35 value="' . $_POST['Fax'] . '"></td>
</tr>';

echo '<tr><td class="visible">' . _('Email Address') . ':</td>
	<td class="visible"><input '.(in_array('Email',$Errors) ?  'class="inputerror"' : '' ) .' tabindex="12" type="Text" Name="Email" size=50 maxlength=55 value="' . $_POST['Email'] . '"></td>
</tr>';


$result=DB_query("SELECT currabrev, currency FROM currencies",$db);

echo '<tr><td class="visible">' . _('Home Currency') . ':</td><td class="visible"><select tabindex="13" Name=CurrencyDefault>';

while ($myrow = DB_fetch_array($result)) {
	if ($_POST['CurrencyDefault']==$myrow['currabrev']){
		echo "<option selected VALUE='". $myrow['currabrev'] . "'>" . $myrow['currency'];
	} else {
		echo "<option VALUE='". $myrow['currabrev'] . "'>" . $myrow['currency'];
	}
} //end while loop

DB_free_result($result);

echo '</select></td></tr>';

$result=DB_query("SELECT accountcode,
			accountname
		FROM chartmaster,
			accountgroups
		WHERE chartmaster.group_=accountgroups.groupname
		AND accountgroups.pandl=0
		ORDER BY chartmaster.accountcode",$db);


echo '<tr><td class="visible">' . _('Creditors Control GL Account') . ':</td><td class="visible"><select tabindex="15" Name=CreditorsAct>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['CreditorsAct']==$myrow[0]){
		echo "<option selected VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<option  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_data_seek($result,0);

echo '</select></td></tr>';

echo '<tr><td class="visible">' . _('Payment Discount GL Account') . ':</td><td class="visible"><select tabindex="22" Name=PytDiscountAct>';

while ($myrow = DB_fetch_row($result)) {
	if ($_POST['PytDiscountAct']==$myrow[0]){
		echo "<option selected VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	} else {
		echo "<option  VALUE='". $myrow[0] . "'>" . $myrow[1] . ' ('.$myrow[0].')';
	}
} //end while loop

DB_data_seek($result,0);

echo '</select></td></tr>';

echo '<tr><td class="visible">' . _('Create GL entries for accounts receivable transactions') . ':</td><td class="visible"><select tabindex="23" Name=GLLink_Debtors>';

if ($_POST['GLLink_Debtors']==0){
	echo '<option selected VALUE=0>' . _('No');
	echo '<option VALUE=1>' . _('Yes');
} else {
	echo '<option selected VALUE=1>' . _('Yes');
	echo '<option VALUE=0>' . _('No');
}

echo '</select></td></tr>';

echo '<tr><td class="visible">' . _('Create GL entries for accounts payable transactions') . ':</td><td class="visible"><select tabindex="24" Name=GLLink_Creditors>';

if ($_POST['GLLink_Creditors']==0){
	echo '<option selected VALUE=0>' . _('No');
	echo '<option VALUE=1>' . _('Yes');
} else {
	echo '<option selected VALUE=1>' . _('Yes');
	echo '<option VALUE=0>' . _('No');
}

echo '</select></td></tr>';

echo '<tr>
		<td class="visible">' . _('Create GL entries for stock transactions')  . ':</td>
		<td class="visible"><select tabindex="25" name="GLLink_Stock">';

if ($_POST['GLLink_Stock']=='0'){
	echo '<option selected="selected" value="0">' . _('No') . '</option>';
	echo '<option value="1">' . _('Yes') . '</option>';
} else {
	echo '<option selected="selected" value="1">' . _('Yes') . '</option>';
	echo '<option value="0">' . _('No') . '</option>';
}

echo '</select></td>
	</tr>';
echo '</table><br><div class="centre"><input tabindex="26" type="Submit" Name="submit" value="' . _('Update') . '"></div>';

include('includes/footer.inc');
?>