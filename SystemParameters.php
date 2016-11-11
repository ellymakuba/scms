<?php

/* $Id: SystemParameters.php 4012 2010-09-30 16:06:07Z tim_schofield $*/

$PageSecurity =15;

include('includes/session.inc');

$title = _('System Configuration');

include('includes/header.inc');

echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Supplier Types')
	. '" alt="">' . $title. '</p>';

if (isset($_POST['submit'])) {

	//initialise no input errors assumed initially before we test
	$InputError = 0;

	/* actions to take once the user has clicked the submit button
	ie the page has called itself with some user input */

	//first off validate inputs sensible
	/*
		Note: the X_ in the POST variables, the reason for this is to overcome globals=on replacing
		the actial system/overidden variables.
	*/
	if (strlen($_POST['X_PastDueDays1']) > 3 || !is_numeric($_POST['X_PastDueDays1']) ) {
		$InputError = 1;
		prnMsg(_('First overdue deadline days must be a number'),'error');
	} elseif (strlen($_POST['X_PastDueDays2'])  > 3 || !is_numeric($_POST['X_PastDueDays2']) ) {
		$InputError = 1;
		prnMsg(_('Second overdue deadline days must be a number'),'error');
	} elseif (strstr($_POST['X_RomalpaClause'], "'") || strlen($_POST['X_RomalpaClause']) > 5000) {
		$InputError = 1;
		prnMsg(_('The Romalpa Clause may not contain single quotes and may not be longer than 5000 chars'),'error');
	}   elseif (strlen($_POST['X_TaxAuthorityReferenceName']) >25) {
		$InputError = 1;
		prnMsg(_('The Tax Authority Reference Name must be 25 characters or less long'),'error');
	} elseif (strlen($_POST['X_OverChargeProportion']) > 3 || !is_numeric($_POST['X_OverChargeProportion']) ||
		$_POST['X_OverChargeProportion'] < 0 || $_POST['X_OverChargeProportion'] > 100 ) {
		$InputError = 1;
		prnMsg(_('Over Charge Proportion must be a percentage'),'error');
	} elseif (strlen($_POST['X_OverReceiveProportion']) > 3 || !is_numeric($_POST['X_OverReceiveProportion']) ||
		$_POST['X_OverReceiveProportion'] < 0 || $_POST['X_OverReceiveProportion'] > 100 ) {
		$InputError = 1;
		prnMsg(_('Over Receive Proportion must be a percentage'),'error');
	} elseif (strlen($_POST['X_PageLength']) > 3 || !is_numeric($_POST['X_PageLength']) ||
		$_POST['X_PageLength'] < 1 ) {
		$InputError = 1;
		prnMsg(_('Lines per page must be greater than 1'),'error');
	} elseif (strlen($_POST['X_MonthsAuditTrail']) > 2 || !is_numeric($_POST['X_MonthsAuditTrail']) ||
		$_POST['X_MonthsAuditTrail'] < 0 ) {
		$InputError = 1;
		prnMsg(_('The number of months of audit trail to keep must be zero or a positive number less than 100 months'),'error');
	}elseif (strlen($_POST['X_DefaultTaxCategory']) > 1 || !is_numeric($_POST['X_DefaultTaxCategory']) ||
		$_POST['X_DefaultTaxCategory'] < 1 ) {
		$InputError = 1;
		prnMsg(_('DefaultTaxCategory must be between 1 and 9'),'error');
	} elseif (strlen($_POST['X_DefaultDisplayRecordsMax']) > 3 || !is_numeric($_POST['X_DefaultDisplayRecordsMax']) ||
		$_POST['X_DefaultDisplayRecordsMax'] < 1 ) {
		$InputError = 1;
		prnMsg(_('Default maximum number of records to display must be between 1 and 500'),'error');
	}elseif (strlen($_POST['X_MaxImageSize']) > 3 || !is_numeric($_POST['X_MaxImageSize']) ||
		$_POST['X_MaxImageSize'] < 1 ) {
		$InputError = 1;
		prnMsg(_('The maximum size of item image files must be between 50 and 500 (NB this figure refers to KB)'),'error');
	}elseif (!IsEmailAddress($_POST['X_FactoryManagerEmail'])){
		$InputError = 1;
		prnMsg(_('The Factory Manager Email address does not appear to be valid'),'error');
	}elseif (!IsEmailAddress($_POST['X_PurchasingManagerEmail'])){
		$InputError = 1;
		prnMsg(_('The Purchasing Manager Email address does not appear to be valid'),'error');
	}

	if ($InputError !=1){

		$sql = array();

		if ($_SESSION['DefaultDateFormat'] != $_POST['X_DefaultDateFormat'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DefaultDateFormat']."' WHERE confname = 'DefaultDateFormat'";
		}
		if ($_SESSION['DefaultTheme'] != $_POST['X_DefaultTheme'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DefaultTheme']."' WHERE confname = 'DefaultTheme'";
		}
		if ($_SESSION['PastDueDays1'] != $_POST['X_PastDueDays1'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_PastDueDays1']."' WHERE confname = 'PastDueDays1'";
		}
		if ($_SESSION['PastDueDays2'] != $_POST['X_PastDueDays2'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_PastDueDays2']."' WHERE confname = 'PastDueDays2'";
		}
		if ($_SESSION['DefaultCreditLimit'] != $_POST['X_DefaultCreditLimit'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DefaultCreditLimit']."' WHERE confname = 'DefaultCreditLimit'";
		}
		if ($_SESSION['Show_Settled_LastMonth'] != $_POST['X_Show_Settled_LastMonth'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_Show_Settled_LastMonth']."' WHERE confname = 'Show_Settled_LastMonth'";
		}
		if ($_SESSION['RomalpaClause'] != $_POST['X_RomalpaClause'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_RomalpaClause'] . "' WHERE confname = 'RomalpaClause'";
		}
		if ($_SESSION['QuickEntries'] != $_POST['X_QuickEntries'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_QuickEntries']."' WHERE confname = 'QuickEntries'";
		}
		if ($_SESSION['DispatchCutOffTime'] != $_POST['X_DispatchCutOffTime'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DispatchCutOffTime']."' WHERE confname = 'DispatchCutOffTime'";
		}
		if ($_SESSION['AllowSalesOfZeroCostItems'] != $_POST['X_AllowSalesOfZeroCostItems'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_AllowSalesOfZeroCostItems']."' WHERE confname = 'AllowSalesOfZeroCostItems'";
		}
		if ($_SESSION['CreditingControlledItems_MustExist'] != $_POST['X_CreditingControlledItems_MustExist'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_CreditingControlledItems_MustExist']."' WHERE confname = 'CreditingControlledItems_MustExist'";
		}
		if ($_SESSION['DefaultPriceList'] != $_POST['X_DefaultPriceList'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DefaultPriceList']."' WHERE confname = 'DefaultPriceList'";
		}
		if ($_SESSION['Default_Shipper'] != $_POST['X_Default_Shipper'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_Default_Shipper']."' WHERE confname = 'Default_Shipper'";
		}
		if ($_SESSION['DoFreightCalc'] != $_POST['X_DoFreightCalc'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DoFreightCalc']."' WHERE confname = 'DoFreightCalc'";
		}
		if ($_SESSION['FreightChargeAppliesIfLessThan'] != $_POST['X_FreightChargeAppliesIfLessThan'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_FreightChargeAppliesIfLessThan']."' WHERE confname = 'FreightChargeAppliesIfLessThan'";
		}
		if ($_SESSION['DefaultTaxCategory'] != $_POST['X_DefaultTaxCategory'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DefaultTaxCategory']."' WHERE confname = 'DefaultTaxCategory'";
		}
		if ($_SESSION['TaxAuthorityReferenceName'] != $_POST['X_TaxAuthorityReferenceName'] ) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_TaxAuthorityReferenceName'] . "' WHERE confname = 'TaxAuthorityReferenceName'";
		}
		if ($_SESSION['CountryOfOperation'] != $_POST['X_CountryOfOperation'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_CountryOfOperation'] ."' WHERE confname = 'CountryOfOperation'";
		}
		if ($_SESSION['NumberOfPeriodsOfStockUsage'] != $_POST['X_NumberOfPeriodsOfStockUsage'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_NumberOfPeriodsOfStockUsage']."' WHERE confname = 'NumberOfPeriodsOfStockUsage'";
		}
		if ($_SESSION['Check_Qty_Charged_vs_Del_Qty'] != $_POST['X_Check_Qty_Charged_vs_Del_Qty'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_Check_Qty_Charged_vs_Del_Qty']."' WHERE confname = 'Check_Qty_Charged_vs_Del_Qty'";
		}
		if ($_SESSION['Check_Price_Charged_vs_Order_Price'] != $_POST['X_Check_Price_Charged_vs_Order_Price'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_Check_Price_Charged_vs_Order_Price']."' WHERE confname = 'Check_Price_Charged_vs_Order_Price'";
		}
		if ($_SESSION['OverChargeProportion'] != $_POST['X_OverChargeProportion'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_OverChargeProportion']."' WHERE confname = 'OverChargeProportion'";
		}
		if ($_SESSION['OverReceiveProportion'] != $_POST['X_OverReceiveProportion'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_OverReceiveProportion']."' WHERE confname = 'OverReceiveProportion'";
		}
		if ($_SESSION['PO_AllowSameItemMultipleTimes'] != $_POST['X_PO_AllowSameItemMultipleTimes'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_PO_AllowSameItemMultipleTimes']."' WHERE confname = 'PO_AllowSameItemMultipleTimes'";
		}
		if ($_SESSION['SO_AllowSameItemMultipleTimes'] != $_POST['X_SO_AllowSameItemMultipleTimes'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_SO_AllowSameItemMultipleTimes']."' WHERE confname = 'SO_AllowSameItemMultipleTimes'";
		}
		if ($_SESSION['YearEnd'] != $_POST['X_YearEnd'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_YearEnd']."' WHERE confname = 'YearEnd'";
		}
		if ($_SESSION['PageLength'] != $_POST['X_PageLength'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_PageLength']."' WHERE confname = 'PageLength'";
		}
		if ($_SESSION['DefaultDisplayRecordsMax'] != $_POST['X_DefaultDisplayRecordsMax'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_DefaultDisplayRecordsMax']."' WHERE confname = 'DefaultDisplayRecordsMax'";
		}
		if ($_SESSION['MaxImageSize'] != $_POST['X_MaxImageSize'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_MaxImageSize']."' WHERE confname = 'MaxImageSize'";
		}
//new number must be shown
		if ($_SESSION['NumberOfMonthMustBeShown'] != $_POST['X_NumberOfMonthMustBeShown'] ) {
			$sql[] = "UPDATE config SET confvalue = '".$_POST['X_NumberOfMonthMustBeShown']."' WHERE confname = 'NumberOfMonthMustBeShown'";
		}
		if ($_SESSION['part_pics_dir'] != $_POST['X_part_pics_dir'] ) {
			$sql[] = "UPDATE config SET confvalue = 'companies/" . $_SESSION['DatabaseName'] . '/' . $_POST['X_part_pics_dir']."' WHERE confname = 'part_pics_dir'";
		}
		if ($_SESSION['reports_dir'] != $_POST['X_reports_dir'] ) {
			$sql[] = "UPDATE config SET confvalue = 'companies/" . $_SESSION['DatabaseName'] . '/' . $_POST['X_reports_dir']."' WHERE confname = 'reports_dir'";
		}
		if ($_SESSION['AutoDebtorNo'] != $_POST['X_AutoDebtorNo'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_AutoDebtorNo'])."' WHERE confname = 'AutoDebtorNo'";
		}
		if ($_SESSION['HTTPS_Only'] != $_POST['X_HTTPS_Only'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_HTTPS_Only'])."' WHERE confname = 'HTTPS_Only'";
		}
		if ($_SESSION['DB_Maintenance'] != $_POST['X_DB_Maintenance'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_DB_Maintenance'])."' WHERE confname = 'DB_Maintenance'";
		}
		if ($_SESSION['DefaultBlindPackNote'] != $_POST['X_DefaultBlindPackNote'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_DefaultBlindPackNote'])."' WHERE confname = 'DefaultBlindPackNote'";
		}
		if ($_SESSION['ShowValueOnGRN'] != $_POST['X_ShowValueOnGRN'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_ShowValueOnGRN'])."' WHERE confname = 'ShowValueOnGRN'";
		}
		if ($_SESSION['PackNoteFormat'] != $_POST['X_PackNoteFormat'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_PackNoteFormat'])."' WHERE confname = 'PackNoteFormat'";
		}
		if ($_SESSION['CheckCreditLimits'] != $_POST['X_CheckCreditLimits'] ) {
			$sql[] = "UPDATE config SET confvalue = '". ($_POST['X_CheckCreditLimits'])."' WHERE confname = 'CheckCreditLimits'";
		}
		if ($_SESSION['WikiApp'] != $_POST['X_WikiApp'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_WikiApp']."' WHERE confname = 'WikiApp'";
		}
		if ($_SESSION['WikiPath'] != $_POST['X_WikiPath'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_WikiPath']."' WHERE confname = 'WikiPath'";
		}
		if ($_SESSION['ProhibitJournalsToControlAccounts'] != $_POST['X_ProhibitJournalsToControlAccounts'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_ProhibitJournalsToControlAccounts']."' WHERE confname = 'ProhibitJournalsToControlAccounts'";
		}
		if ($_SESSION['InvoicePortraitFormat'] != $_POST['X_InvoicePortraitFormat'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_InvoicePortraitFormat']."' WHERE confname = 'InvoicePortraitFormat'";
		}
		if ($_SESSION['AllowOrderLineItemNarrative'] != $_POST['X_AllowOrderLineItemNarrative'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_AllowOrderLineItemNarrative']."' WHERE confname = 'AllowOrderLineItemNarrative'";
		}
		if ($_SESSION['RequirePickingNote'] != $_POST['X_RequirePickingNote'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_RequirePickingNote']."' WHERE confname = 'RequirePickingNote'";
		}
		if ($_SESSION['geocode_integration'] != $_POST['X_geocode_integration'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_geocode_integration']."' WHERE confname = 'geocode_integration'";
		}
		if ($_SESSION['Extended_SupplierInfo'] != $_POST['X_Extended_SupplierInfo'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_Extended_SupplierInfo']."' WHERE confname = 'Extended_SupplierInfo'";
		}
		if ($_SESSION['Extended_CustomerInfo'] != $_POST['X_Extended_CustomerInfo'] ) {
			$sql[] = "UPDATE config SET confvalue = '". $_POST['X_Extended_CustomerInfo']."' WHERE confname = 'Extended_CustomerInfo'";
		}
		if ($_SESSION['ProhibitPostingsBefore'] != $_POST['X_ProhibitPostingsBefore'] ) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_ProhibitPostingsBefore']."' WHERE confname = 'ProhibitPostingsBefore'";
		}
		if ($_SESSION['WeightedAverageCosting'] != $_POST['X_WeightedAverageCosting'] ) {
			$sql[] = "UPDATE config SET confvalue = '" . $_POST['X_WeightedAverageCosting']."' WHERE confname = 'WeightedAverageCosting'";
		}
		if ($_SESSION['AutoIssue'] != $_POST['X_AutoIssue']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_AutoIssue'] . "' WHERE confname='AutoIssue'";
		}
		if ($_SESSION['ProhibitNegativeStock'] != $_POST['X_ProhibitNegativeStock']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_ProhibitNegativeStock'] . "' WHERE confname='ProhibitNegativeStock'";
		}
		if ($_SESSION['MonthsAuditTrail'] != $_POST['X_MonthsAuditTrail']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_MonthsAuditTrail'] . "' WHERE confname='MonthsAuditTrail'";
		}
		if ($_SESSION['LogSeverity'] != $_POST['X_LogSeverity']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_LogSeverity'] . "' WHERE confname='LogSeverity'";
		}
		if ($_SESSION['LogPath'] != $_POST['X_LogPath']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_LogPath'] . "' WHERE confname='LogPath'";
		}
		if ($_SESSION['UpdateCurrencyRatesDaily'] != $_POST['X_UpdateCurrencyRatesDaily']){
			$sql[] = "UPDATE config SET confvalue='".$_POST['X_UpdateCurrencyRatesDaily']."' WHERE confname='UpdateCurrencyRatesDaily'";
		}
		if ($_SESSION['FactoryManagerEmail'] != $_POST['X_FactoryManagerEmail']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_FactoryManagerEmail'] . "' WHERE confname='FactoryManagerEmail'";
		}
		if ($_SESSION['PurchasingManagerEmail'] != $_POST['X_PurchasingManagerEmail']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_PurchasingManagerEmail'] . "' WHERE confname='PurchasingManagerEmail'";
		}
		if ($_SESSION['AutoCreateWOs'] != $_POST['X_AutoCreateWOs']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_AutoCreateWOs'] . "' WHERE confname='AutoCreateWOs'";
		}
		if ($_SESSION['DefaultFactoryLocation'] != $_POST['X_DefaultFactoryLocation']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_DefaultFactoryLocation'] . "' WHERE confname='DefaultFactoryLocation'";
		}
		if ($_SESSION['DefineControlledOnWOEntry'] != $_POST['X_DefineControlledOnWOEntry']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_DefineControlledOnWOEntry'] . "' WHERE confname='DefineControlledOnWOEntry'";
		}
		if ($_SESSION['FrequentlyOrderedItems'] != $_POST['X_FrequentlyOrderedItems']){
			$sql[] = "UPDATE config SET confvalue='" . $_POST['X_FrequentlyOrderedItems'] . "' WHERE confname='FrequentlyOrderedItems'";
		}
		$ErrMsg =  _('The system configuration could not be updated because');
		if (sizeof($sql) > 1 ) {
			$result = DB_Txn_Begin($db);
			foreach ($sql as $line) {
				$result = DB_query($line,$db,$ErrMsg);
			}
			$result = DB_Txn_Commit($db);
		} elseif(sizeof($sql)==1) {
			$result = DB_query($sql,$db,$ErrMsg);
		}

		prnMsg( _('System configuration updated'),'success');

		$ForceConfigReload = True; // Required to force a load even if stored in the session vars
		include('includes/GetConfig.php');
		$ForceConfigReload = False;
	} else {
		prnMsg( _('Validation failed') . ', ' . _('no updates or deletes took place'),'warn');
	}

} /* end of if submit */

echo '<form method="post" action=' . $_SERVER['PHP_SELF'] . '>';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<table cellpadding=2 class=enclosed width=50%>';

$TableHeader = '<tr><th>' . _('System Variable Name') . '</th>
	<th>' . _('Value') . '</th>';

echo '<tr><th colspan=3>' . _('General Settings') . '</th></tr>';
echo $TableHeader;

// DefaultDateFormat
echo '<tr style="outline: 1px solid"><td>' . _('Default Date Format') . ':</td>
	<td><select name="X_DefaultDateFormat">
	<option '.(($_SESSION['DefaultDateFormat']=='d/m/Y')?'selected ':'').'Value="d/m/Y">d/m/Y</option>
	<option '.(($_SESSION['DefaultDateFormat']=='d.m.Y')?'selected ':'').'Value="d.m.Y">d.m.Y</option>
	<option '.(($_SESSION['DefaultDateFormat']=='m/d/Y')?'selected ':'').'Value="m/d/Y">m/d/Y</option>
	<option '.(($_SESSION['DefaultDateFormat']=='Y/m/d')?'selected ':'').'Value="Y/m/d">Y/m/d</option>
	</select></td>
	<td>' . _('The default date format for entry of dates and display.') . '</td></tr>';

// DefaultTheme
echo '<tr style="outline: 1px solid"><td>' . _('New Users Default Theme') . ':</td>
	 <td><select Name="X_DefaultTheme">';
$ThemeDirectory = dir('css/');
while (false != ($ThemeName = $ThemeDirectory->read())){
	if (is_dir("css/$ThemeName") AND $ThemeName != '.' AND $ThemeName != '..' AND $ThemeName != '.svn'){
		if ($_SESSION['DefaultTheme'] == $ThemeName)
			echo "<option selected value='$ThemeName'>$ThemeName";
		else
			echo "<option value='$ThemeName'>$ThemeName";
	}
}
echo '</select></td>
	</tr>';

echo '<tr><th colspan=3>' . _('Accounts Receivable/Payable Settings') . '</th></tr>';

// PastDueDays1
echo '<tr style="outline: 1px solid"><td>' . _('First Overdue Deadline in (days)') . ':</td>
	<td><input type="Text" class="number" Name="X_PastDueDays1" value="' . $_SESSION['PastDueDays1'] . '" size=3 maxlength=3></td>
	</tr>';

// PastDueDays2
echo '<tr style="outline: 1px solid"><td>' . _('Second Overdue Deadline in (days)') . ':</td>
	<td><input type="Text" class="number" Name="X_PastDueDays2" value="' . $_SESSION['PastDueDays2'] . '" size=3 maxlength=3></td>
	</tr>';

//'AllowOrderLineItemNarrative'
echo '<tr style="outline: 1px solid"><td>' . _('Order Entry allows Line Item Narrative') . ':</td>
	<td><select Name="X_AllowOrderLineItemNarrative">
	<option '.($_SESSION['AllowOrderLineItemNarrative']=='1'?'selected ':'').'value="1">'._('Allow Narrative Entry').'
	<option '.($_SESSION['AllowOrderLineItemNarrative']=='0'?'selected ':'').'value="0">'._('No Narrative Line').'
	</select></td></tr>';

//Default Invoice Format
echo '<tr style="outline: 1px solid"><td>' . _('Invoice Layout') . ':</td>
	<td><select Name="X_InvoicePortraitFormat">
	<option '.($_SESSION['InvoicePortraitFormat']=='0'?'selected ':'').'value="0">'._('Landscape').'
	<option '.($_SESSION['InvoicePortraitFormat']=='1'?'selected ':'').'value="1">'._('Portrait').'
	</select></td></tr>';

//Blind packing note
echo '<tr style="outline: 1px solid"><td>' . _('Show Campus Details on Receipts') . ':</td>
	<td><select Name="X_DefaultBlindPackNote">
	<option '.($_SESSION['DefaultBlindPackNote']=="1"?'selected ':'').'value="1">'._('Show Campus Details').'
	<option '.($_SESSION['DefaultBlindPackNote']=="2"?'selected ':'').'value="2">'._('Hide Campus Details').'
	</select></td>
	</tr>';

//Show values on GRN
echo '<tr style="outline: 1px solid"><td>' . _('Show order values on GRN') . ':</td>
	<td><select Name="X_ShowValueOnGRN">
	<option '.($_SESSION['ShowValueOnGRN']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['ShowValueOnGRN']?'selected ':'').'value="0">'._('No').'
	</select></td></tr>';

//==HJ== drop down list for tax category
$sql = 'SELECT taxcatid, taxcatname FROM taxcategories ORDER BY taxcatname';
$ErrMsg = _('Could not load tax categories table');
$result = DB_query($sql,$db,$ErrMsg);
echo '<tr style="outline: 1px solid"><td>' . _('Default Tax Category') . ':</td>';
echo '<td><select Name="X_DefaultTaxCategory">';
if( DB_num_rows($result) == 0 ) {
	echo '<option selected value="">'._('Unavailable');
} else {
	while( $row = DB_fetch_array($result) ) {
		echo '<option '.($_SESSION['DefaultTaxCategory'] == $row['taxcatid']?'selected ':'').'value="'.$row['taxcatid'].'">'.$row['taxcatname'];
	}
}
echo '</select></td></tr>';


//TaxAuthorityReferenceName
echo '<tr style="outline: 1px solid"><td>' . _('TaxAuthorityReferenceName') . ':</td>
	<td><input type="Text" Name="X_TaxAuthorityReferenceName" size=16 maxlength=25 value="' . $_SESSION['TaxAuthorityReferenceName'] . '"></td></tr>';

// CountryOfOperation
$sql = 'SELECT currabrev, country FROM currencies ORDER BY country';
$ErrMsg = _('Could not load the countries from the currency table');
$result = DB_query($sql,$db,$ErrMsg);
echo '<tr style="outline: 1px solid"><td>' . _('Country Of Operation') . ':</td>';
echo '<td><select name="X_CountryOfOperation">';
if( DB_num_rows($result) == 0 ) {
	echo '<option selected value="">'._('Unavailable');
} else {
	while( $row = DB_fetch_array($result) ) {
		echo '<option '.($_SESSION['CountryOfOperation'] == $row['currabrev']?'selected ':'').'value="'.$row['currabrev'].'">'.$row['country'] . '</option>';
	}
}
echo '</select></td></tr>';


// Check_Qty_Charged_vs_Del_Qty
echo '<tr style="outline: 1px solid"><td>' . _('Check Quantity Charged vs Deliver Qty') . ':</td>
	<td><select Name="X_Check_Qty_Charged_vs_Del_Qty">
	<option '.($_SESSION['Check_Qty_Charged_vs_Del_Qty']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['Check_Qty_Charged_vs_Del_Qty']?'selected ':'').'value="0">'._('No').'
	</select></td></tr>';

// Check_Price_Charged_vs_Order_Price
echo '<tr style="outline: 1px solid"><td>' . _('Check Price Charged vs Order Price') . ':</td>
	<td><select Name="X_Check_Price_Charged_vs_Order_Price">
	<option '.($_SESSION['Check_Price_Charged_vs_Order_Price']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['Check_Price_Charged_vs_Order_Price']?'selected ':'').'value="0">'._('No').'
	</select></td></tr>';

// OverChargeProportion
echo '<tr style="outline: 1px solid"><td>' . _('Allowed Over Charge Proportion') . ':</td>
	<td><input type="Text" class="number" Name="X_OverChargeProportion" size=4 maxlength=3 value="' . $_SESSION['OverChargeProportion'] . '"></td></tr>';

// OverReceiveProportion
echo '<tr style="outline: 1px solid"><td>' . _('Allowed Over Receive Proportion') . ':</td>
	<td><input type="Text" class="number" Name="X_OverReceiveProportion" size=4 maxlength=3 value="' . $_SESSION['OverReceiveProportion'] . '"></td></tr>';

// PO_AllowSameItemMultipleTimes
echo '<tr style="outline: 1px solid"><td>' . _('Purchase Order Allows Same Item Multiple Times') . ':</td>
	<td><select Name="X_PO_AllowSameItemMultipleTimes">
	<option '.($_SESSION['PO_AllowSameItemMultipleTimes']?'selected ':'').'value="1">'._('Yes').'
	<option '.(!$_SESSION['PO_AllowSameItemMultipleTimes']?'selected ':'').'value="0">'._('No').'
	</select></td>&nbsp;<td></td></tr>';

echo '<tr><th colspan=3>' . _('General Settings') . '</th></tr>';
echo $TableHeader;

// YearEnd
$MonthNames = array( 1=>_('January'),
			2=>_('February'),
			3=>_('March'),
			4=>_('April'),
			5=>_('May'),
			6=>_('June'),
			7=>_('July'),
			8=>_('August'),
			9=>_('September'),
			10=>_('October'),
			11=>_('November'),
			12=>_('December') );
echo '<tr style="outline: 1px solid"><td>' . _('Financial Year Ends On') . ':</td>
	<td><select Name="X_YearEnd">';
for ($i=1; $i <= sizeof($MonthNames); $i++ )
	echo '<option '.($_SESSION['YearEnd'] == $i ? 'selected ' : '').'value="'.$i.'">'.$MonthNames[$i];
echo '</select></td></tr>';

//PageLength
echo '<tr style="outline: 1px solid"><td>' . _('Report Page Length') . ':</td>
	<td><input type="text" class="number" name="X_PageLength" size=4 maxlength=6 value="' . $_SESSION['PageLength'] . '"></td><td>&nbsp;</td>
</tr>';

//DefaultDisplayRecordsMax
echo '<tr style="outline: 1px solid"><td>' . _('Default Maximum Number of Records to Show') . ':</td>
	<td><input type="text" class="number" name="X_DefaultDisplayRecordsMax" size=4 maxlength=3 value="' . $_SESSION['DefaultDisplayRecordsMax'] . '"></td>
	</tr>';

//MaxImageSize
echo '<tr style="outline: 1px solid"><td>' . _('Maximum Size in KB of uploaded images') . ':</td>
	<td><input type="text" class="number" name="X_MaxImageSize" size=4 maxlength=3 value="' . $_SESSION['MaxImageSize'] . '"></td></tr>';
//NumberOfMonthMustBeShown
$sql = 'SELECT confvalue
		FROM `config`
		WHERE confname ="numberOfMonthMustBeShown"';

$ErrMsg = _('Could not load the Number Of Month Must be Shown');
$result = DB_query($sql,$db,$ErrMsg);
$row = DB_fetch_array($result);
$_SESSION['NumberOfMonthMustBeShown'] = $row['confvalue'];

echo '<tr style="outline: 1px solid"><td>' . _('Number Of Month Must Be Shown') . ':</td>
		  <td><input type="text" class="number" name="X_NumberOfMonthMustBeShown" size=4 maxlength=3 value="' . $_SESSION['NumberOfMonthMustBeShown'] . '"></td></tr>';

//$part_pics_dir
echo '<tr style="outline: 1px solid"><td>' . _('The directory where images are stored') . ':</td>
	 <td><select name="X_part_pics_dir">';


$CompanyDirectory = 'companies/' . $_SESSION['DatabaseName'] . '/';
$DirHandle = dir($CompanyDirectory);

while ($DirEntry = $DirHandle->read() ){

	if (is_dir($CompanyDirectory . $DirEntry)
		AND $DirEntry != '..'
		AND $DirEntry!='.'
		AND $DirEntry != 'CVS'
		AND $DirEntry != 'reports'
		AND $DirEntry != 'locale'
		AND $DirEntry != 'fonts'   ){

		if ($_SESSION['part_pics_dir'] == $CompanyDirectory . $DirEntry){
			echo '<option selected value="' . $DirEntry . '">' . $DirEntry . '</option>';
		} else {
			echo '<option value="' . $DirEntry . '">' . $DirEntry  . '</option>';
		}
	}
}
echo '</select></td>
	</tr>';


//$reports_dir
echo '<tr style="outline: 1px solid"><td>' . _('The directory where reports are stored') . ':</td>
	<td><select name="X_reports_dir">';

$DirHandle = dir($CompanyDirectory);

while (false != ($DirEntry = $DirHandle->read())){

	if (is_dir($CompanyDirectory . $DirEntry)
		AND $DirEntry != '..'
		AND $DirEntry != 'includes'
		AND $DirEntry!='.'
		AND $DirEntry != 'doc'
		AND $DirEntry != 'css'
		AND $DirEntry != 'CVS'
		AND $DirEntry != 'sql'
		AND $DirEntry != 'part_pics'
		AND $DirEntry != 'locale'
		AND $DirEntry != 'fonts'      ){

		if ($_SESSION['reports_dir'] == $CompanyDirectory . $DirEntry){
			echo '<option selected value="' . $DirEntry . '">' . $DirEntry . '</option>';
		} else {
			echo '<option value="' . $DirEntry . '">' . $DirEntry  . '</option>';
		}
	}
}

echo '</select></td></tr>';

/*Perform Database maintenance DB_Maintenance*/
echo '<tr style="outline: 1px solid"><td>' . _('Perform Database Maintenance At Logon') . ':</td>
	<td><select name="X_DB_Maintenance">';
	if ($_SESSION['DB_Maintenance']=='1'){
		echo '<option selected value="1">'._('Daily') . '</option>';
	} else {
		echo '<option value="1">'._('Daily') . '</option>';
	}
	if ($_SESSION['DB_Maintenance']=='7'){
		echo '<option selected value="7">'._('Weekly') . '</option>';
	} else {
		echo '<option value="7">'._('Weekly') . '</option>';
	}
	if ($_SESSION['DB_Maintenance']=='30'){
		echo '<option selected value="30">'._('Monthly') . '</option>';
	} else {
		echo '<option value="30">'._('Monthly') . '</option>';
	}
	if ($_SESSION['DB_Maintenance']=='0'){
		echo '<option selected value="0">'._('Never') . '</option>';
	} else {
		echo '<option value="0">'._('Never') . '</option>';
	}

	echo '</select></td></tr>';



echo '<tr style="outline: 1px solid"><td>' . _('Prohibit GL Journals to Periods Prior To') . ':</td>
	<td><select Name="X_ProhibitPostingsBefore">';

$sql = 'SELECT lastdate_in_period FROM periods ORDER BY periodno DESC';
$ErrMsg = _('Could not load periods table');
$result = DB_query($sql,$db,$ErrMsg);
while ($PeriodRow = DB_fetch_row($result)){
	if ($_SESSION['ProhibitPostingsBefore']==$PeriodRow[0]){
		echo  '<option selected value="' . $PeriodRow[0] . '">' . ConvertSQLDate($PeriodRow[0]) . '</option>';
	} else {
		echo  '<option value="' . $PeriodRow[0] . '">' . ConvertSQLDate($PeriodRow[0]) . '</option>';
	}
}
echo '</select></td></tr>';

echo '<tr style="outline: 1px solid"><td>' . _('Inventory Costing Method') . ':</td>
	<td><select name="X_WeightedAverageCosting">';

if ($_SESSION['WeightedAverageCosting']==1){
	echo  '<option selected value="1">' . _('Weighted Average Costing') . '</option>';
	echo  '<option value="0">' . _('Standard Costing') . '</option>';
} else {
	echo  '<option selected value="0">' . _('Standard Costing') . '</option>';
	echo  '<option value="1">' . _('Weighted Average Costing') . '</option>';
}

echo '</select></td></tr>';

echo '<tr style="outline: 1px solid"><td>' . _('Prohibit Negative Stock') . ':</td>
		<td>
		<select name="X_ProhibitNegativeStock">';
if ($_SESSION['ProhibitNegativeStock']==0) {
	echo '<option selected value=0>' . _('No') . '</option>';
	echo '<option value=1>' . _('Yes') . '</option>';
} else {
	echo '<option selected value=1>' . _('Yes') . '</option>';
	echo '<option value=0>' . _('No') . '</option>';
}
echo '</select></td></tr>' ;



//Months of Audit Trail to Keep
echo '<tr style="outline: 1px solid"><td>' . _('Months of Audit Trail to Retain') . ':</td>
	<td><input type="text" class="number" name="X_MonthsAuditTrail" size=3 maxlength=2 value="' . $_SESSION['MonthsAuditTrail'] . '"></td></tr>';

//Months of Audit Trail to Keep
echo '<tr style="outline: 1px solid"></tr>';

echo '<tr style="outline: 1px solid"><td>' . _('Default Campus') . ':</td>
	<td><select Name="X_DefaultFactoryLocation">';

$sql = 'SELECT loccode,locationname FROM locations';
$ErrMsg = _('Could not load locations table');
$result = DB_query($sql,$db,$ErrMsg);
while ($LocationRow = DB_fetch_array($result)){
	if ($_SESSION['DefaultFactoryLocation']==$LocationRow['loccode']){
		echo  '<option selected value="' . $LocationRow['loccode'] . '">' . $LocationRow['locationname'] . '</option>';
	} else {
		echo  '<option value="' .  $LocationRow['loccode'] . '">' . $LocationRow['locationname'] . '</option>';
	}
}
echo '</tr>';

echo '<tr style="outline: 1px solid"><td>' . _('Campus E-mail') . ':</td>
	<td><input type="text" name="X_FactoryManagerEmail" size=50 maxlength=50 value="' . $_SESSION['FactoryManagerEmail'] . '"></td>
	</tr>';

echo '<tr style="outline: 1px solid"><td>' . _('Procurement Officer Email Address') . ':</td>
	<td><input type="text" name="X_PurchasingManagerEmail" size=50 maxlength=50 value="' . $_SESSION['PurchasingManagerEmail'] . '"></td</tr>';


echo '</table><br /><div class="centre"><input type="Submit" Name="submit" value="' . _('Update') . '"></div></form>';

include('includes/footer.inc');
?>