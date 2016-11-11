<?php
include('includes/DefineJournalClass.php');
$PageSecurity = 10;
include('includes/session.inc');
$title = _('Journal Entry');
include('includes/header.inc');
include('includes/SQL_CommonFunctions.inc');
if (isset($_GET['NewJournal']) and $_GET['NewJournal'] == 'Yes' AND isset($_SESSION['JournalDetail']))
{
	unset($_SESSION['JournalDetail']->GLEntries);
	unset($_SESSION['JournalDetail']);
}
if (!isset($_SESSION['JournalDetail']))
{
	$_SESSION['JournalDetail'] = new Journal;
	$SQL = "SELECT accountcode FROM bankaccounts";
	$result = DB_query($SQL,$db);
	$i=0;
	while ($Act = DB_fetch_row($result))
	{
		$_SESSION['JournalDetail']->BankAccounts[$i]= $Act[0];
		$i++;
	}
}
if (isset($_POST['JournalProcessDate']))
{
	$_SESSION['JournalDetail']->JnlDate=$_POST['JournalProcessDate'];
	if (!Is_Date($_POST['JournalProcessDate']))
	{
		prnMsg(_('The date entered was not valid please enter the date to process the journal in the format'). $_SESSION['DefaultDateFormat'],'warn');
		$_POST['CommitBatch']='Do not do it the date is wrong';
	}
}
if (isset($_POST['JournalType']))
{
	$_SESSION['JournalDetail']->JournalType = $_POST['JournalType'];
}
if (isset($_POST['CommitBatch']) and $_POST['CommitBatch']==_('Accept and Process Journal'))
{
	$PeriodNo = GetPeriod($_SESSION['JournalDetail']->JnlDate,$db);
	$result = DB_Txn_Begin($db);
	$TransNo = GetNextTransNo( 0, $db);
	foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem)
	 {
		$SQL = "INSERT INTO gltrans (type,typeno,trandate,inputdate,periodno,account,narrative,amount) ";
		$SQL= $SQL . "VALUES (0,'" . $TransNo . "','" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "','" . date('Y-m-d') . "',
		'" . $PeriodNo . "','" . $JournalItem->GLCode . "','" . $JournalItem->Narrative . "','" . $JournalItem->Amount . "')";
		$ErrMsg = _('Cannot insert a GL entry for the journal line because');
		$DbgMsg = _('The SQL that failed to insert the GL Trans record was');
		$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);			

		if ($_POST['JournalType']=='Reversing')
		{
			$SQL = "INSERT INTO gltrans (type,typeno,trandate,inputdate,periodno,account,narrative,amount) ";
			$SQL= $SQL . "VALUES (0,'" . $TransNo . "','" . FormatDateForSQL($_SESSION['JournalDetail']->JnlDate) . "','" . date('Y-m-d') . "','" . ($PeriodNo + 1) . "',
			'" . $JournalItem->GLCode . "','Reversal - " . $JournalItem->Narrative . "','" . -($JournalItem->Amount) ."')";
			$ErrMsg =_('Cannot insert a GL entry for the reversing journal because');
			$DbgMsg = _('The SQL that failed to insert the GL Trans record was');
			$result = DB_query($SQL,$db,$ErrMsg,$DbgMsg,true);
		}//end of if ($_POST['JournalType']=='Reversing')
	}//end of foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem)
	$ErrMsg = _('Cannot commit the changes');
	$result= DB_Txn_Begin($db);
	prnMsg(_('Journal').' ' . $TransNo . ' '._('has been successfully entered'),'success');
	unset($_POST['JournalProcessDate']);
	unset($_POST['JournalType']);
	unset($_SESSION['JournalDetail']->GLEntries);
	unset($_SESSION['JournalDetail']);
	/*Set up a newy in case user wishes to enter another */
	echo "<br><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . "&NewJournal=Yes'>"._('Enter Another General Ledger Journal').'</a>';
	/*And post the journal too */
	include ('includes/GLPostings.inc');
	include ('includes/footer.inc');
	exit;

} //end of if (isset($_POST['CommitBatch']) and $_POST['CommitBatch']==_('Accept and Process Journal'))
elseif (isset($_GET['Delete']))
{
	$_SESSION['JournalDetail']->Remove_GLEntry($_GET['Delete']);
} 
elseif (isset($_POST['Process']) and $_POST['Process']==_('Accept'))
{ 
	if($_POST['GLCode']!='')
	{
		$extract = explode(' - ',$_POST['GLCode']);
		$_POST['GLCode'] = $extract[0];
	}
	if($_POST['Debit']>0)
	{
		$_POST['GLAmount'] = $_POST['Debit'];
	}
	elseif($_POST['Credit']>0)
	{
		$_POST['GLAmount'] = '-' . $_POST['Credit'];
	}
	if ($_POST['GLManualCode'] != '' AND is_numeric($_POST['GLManualCode'])){
	    $AllowThisPosting = true; //by default
		if ($_SESSION['ProhibitJournalsToControlAccounts'] == 1)
		{
			if ($_SESSION['CompanyRecord']['gllink_debtors'] == '1' AND $_POST['GLManualCode'] == $_SESSION['CompanyRecord']['debtorsact'])
			{
				prnMsg(_('GL Journals involving the debtors control account cannot be entered. The general ledger debtors ledger (AR) integration is enabled so control accounts
				 are automatically maintained by AIRADS System. This setting can be disabled in System Configuration'),'warn');
				$AllowThisPosting = false;
			}
			if ($_SESSION['CompanyRecord']['gllink_creditors'] == '1' AND $_POST['GLManualCode'] == $_SESSION['CompanyRecord']['creditorsact'])
			{
				prnMsg(_('GL Journals involving the creditors control account cannot be entered. The general ledger creditors ledger (AP) integration is enabled so control 
				accounts are automatically maintained by AIRADS System. This setting can be disabled in System Configuration'),'warn');
				$AllowThisPosting = false;
			}
		}//end of if ($_SESSION['ProhibitJournalsToControlAccounts'] == 1)
		if ($AllowThisPosting)
		 {
			$SQL = "SELECT accountname FROM chartmaster	WHERE accountcode='" . $_POST['GLManualCode'] . "'";
			$Result=DB_query($SQL,$db);
			if (DB_num_rows($Result)==0)
			{
				prnMsg(_('The manual GL code entered does not exist in the database') . ' - ' . _('so this GL analysis item could not be added'),'warn');
				unset($_POST['GLManualCode']);
			}
			 else 
			 {
				$myrow = DB_fetch_array($Result);
				$_SESSION['JournalDetail']->add_to_glanalysis($_POST['GLAmount'], $_POST['GLNarrative'], $_POST['GLManualCode'], $myrow['accountname'], $_POST['tag']);
			}
		}//end of if ($AllowThisPosting)
	}//end of if ($_POST['GLManualCode'] != '' AND is_numeric($_POST['GLManualCode']))
	 else 
	 {
		$AllowThisPosting =true; //by default
		if ($_SESSION['ProhibitJournalsToControlAccounts'] == 1)
		{
			if ($_SESSION['CompanyRecord']['gllink_debtors'] == '1' AND $_POST['GLCode'] == $_SESSION['CompanyRecord']['debtorsact'])
			{
				prnMsg(_('GL Journals involving the debtors control account cannot be entered. The general ledger debtors ledger (AR) integration is enabled so control accounts
				 are automatically maintained by AIRADS System. This setting can be disabled in System Configuration'),'warn');
				$AllowThisPosting = false;
			}
			if ($_SESSION['CompanyRecord']['gllink_creditors'] == '1' AND $_POST['GLCode'] == $_SESSION['CompanyRecord']['creditorsact'])
			{
				prnMsg(_('GL Journals involving the creditors control account cannot be entered. The general ledger creditors ledger (AP) integration is enabled so control 
				accounts are automatically maintained by AIRADS System. This setting can be disabled in System Configuration'),'warn');
				$AllowThisPosting = false;
			}
		}//end of if ($_SESSION['ProhibitJournalsToControlAccounts'] == 1)
		if (in_array($_POST['GLCode'], $_SESSION['JournalDetail']->BankAccounts))
		 {
			prnMsg(_('GL Journals involving a bank account cannot be entered'),'warn');
			$AllowThisPosting = false;
		 }
		if ($AllowThisPosting)
		{
			if (!isset($_POST['GLAmount'])) 
			{
				$_POST['GLAmount']=0;
			}
			$SQL = 'SELECT accountname FROM chartmaster WHERE accountcode=' . $_POST['GLCode'];
			$Result=DB_query($SQL,$db);
			$myrow=DB_fetch_array($Result);
			$_SESSION['JournalDetail']->add_to_glanalysis($_POST['GLAmount'], $_POST['GLNarrative'], $_POST['GLCode'], $myrow['accountname'], $_POST['tag']);
		}
	}

	/*Make sure the same receipt is not double processed by a page refresh */
	$Cancel = 1;
	unset($_POST['Credit']);
	unset($_POST['Debit']);
	unset($_POST['tag']);
	unset($_POST['GLManualCode']);
	unset($_POST['GLNarrative']);
}

if (isset($Cancel)){
	unset($_POST['Credit']);
	unset($_POST['Debit']);
	unset($_POST['GLAmount']);
	unset($_POST['GLCode']);
	unset($_POST['tag']);
	unset($_POST['GLManualCode']);
}
echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method=post name="form">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/maintenance.png" title="' . _('Search') . '" alt="">' . ' ' . $title;
if (!Is_Date($_SESSION['JournalDetail']->JnlDate))
{
	$_SESSION['JournalDetail']->JnlDate = date('d/m/Y');
}
	echo '<table class="enclosed"><tr><td>'._('Date to Process Journal').":</td>
			<td><input type='text' class='date' alt='".$_SESSION['DefaultDateFormat']."' name='JournalProcessDate' 
			value='".$_SESSION['JournalDetail']->JnlDate."' size=11></td>";
	echo '<td>' . _('Type') . ':</td>
			<td><select name=JournalType>';
		echo "<option selected value = 'Normal'>" . _('Normal');
	echo '</select></td></tr></table>';
	echo '<br>';
	echo '<table class="enclosed">';
	echo '<tr><th colspan=2><font size=3 color=blue><b>' . _('Journal Line Entry') . '</b></font></th></tr>';
	echo '<tr><th>' . _('GL Account Code') . '</th>';
	echo '<th>' . _('Select GL Account') . '</th></tr>';
	if (!isset($_POST['GLManualCode'])) 
	{
		$_POST['GLManualCode']='';
	}
	echo '<tr><td><input class="number" type=Text Name="GLManualCode" Maxlength=12 size=12 onChange="inArray(this.value, GLCode.options,'.
		"'".'The account code '."'".'+ this.value+ '."'".' doesnt exist'."'".')"' .' VALUE='. $_POST['GLManualCode'] .'  ></td>';
	$sql="SELECT accountcode,accountname FROM chartmaster ORDER BY accountcode";
	$result=DB_query($sql, $db);
	echo '<td><select name="GLCode" onChange="return assignComboToInput(this,'.'GLManualCode'.')">';
	while ($myrow=DB_fetch_array($result))
	{
		if (isset($_POST['tag']) and $_POST['tag']==$myrow['accountcode'])
		{
			echo '<option selected value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'].' - ' .$myrow['accountname'];
		} 
		else 
		{
			echo '<option value=' . $myrow['accountcode'] . '>' . $myrow['accountcode'].' - ' .$myrow['accountname'];
		}
	}
	echo '</select></td></tr>';
	if (!isset($_POST['GLNarrative'])) 
	{
		$_POST['GLNarrative'] = '';
	}
	if (!isset($_POST['Credit'])) 
	{
		$_POST['Credit'] = '';
	}
	if (!isset($_POST['Debit'])) 
	{
		$_POST['Debit'] = '';
	}
	echo '<tr><th>' . _('Debit') . "</th>".'<td><input type=Text class="number" Name = "Debit" ' .'onChange="eitherOr(this, '.'Credit'.')"'.
				'Maxlength=12 size=10 value=' . $_POST['Debit'] . '></td></tr>';
	echo '<tr><th>' . _('Credit') . "</th>".'<td><input type=Text class="number" Name = "Credit" ' .'onChange="eitherOr(this, '.'Debit'.')"'.
				'Maxlength=12 size=10 value=' . $_POST['Credit'] . '></td></tr>';
	echo '<tr><th>'. _('Comment'). '</th>';
	echo '<td><input type="text" name="GLNarrative" maxlength=100 size=100 value="' . $_POST['GLNarrative'] . '"></td>';
	echo '</tr></table><br>'; /*Close the main table */
	echo "<div class='centre'><input type=submit name=Process value='" . _('Accept') . "'></div><br><br>";	
	echo "<table class='enclosed'>";
	echo '<tr><th colspan=6><div class="centre"><font size=3 color=blue><b>' . _('Journal Summary') . '</b></font></div></th></tr>';
	echo "<tr><th>"._('GL Account')."</th><th>"._('Debit')."</th><th>"._('Credit')."</th><th>"._('Narrative').'</th></tr>';
    $debittotal=0;
	$credittotal=0;
	$j=0;
		foreach ($_SESSION['JournalDetail']->GLEntries as $JournalItem) 
						{
								if ($j==1) 
								{
									echo '<tr class="OddTableRows">';
									$j=0;
								} 
								else 
								{
									echo '<tr class="EvenTableRows">';
									$j++;
								}
							
							echo "<td>" . $JournalItem->GLCode . ' - ' . $JournalItem->GLActName . "</td>";
								if($JournalItem->Amount>0)
								{
								echo "<td class='number'>" . number_format($JournalItem->Amount,2) . '</td><td></td>';
								$debittotal=$debittotal+$JournalItem->Amount;
								}
								elseif($JournalItem->Amount<0)
								{
									$credit=(-1 * $JournalItem->Amount);
								echo "<td></td><td class='number'>" . number_format($credit,2) . '</td>';
								$credittotal=$credittotal+$credit;
								}

							echo '<td>' . $JournalItem->Narrative  . "</td>
									<td><a href='" . $_SERVER['PHP_SELF'] . '?' . SID . '&Delete=' . $JournalItem->ID . "'>"._('Delete').'</a></td>
							</tr>';
						}

			echo '<tr class="EvenTableRows"><td></td>
					<td class=number><b> Total </b></td>
					<td class=number class="number"><b>' . number_format($debittotal,2) . '</b></td>
					<td class=number class="number"><b>' . number_format($credittotal,2) . '</b></td>';
			if ($debittotal!=$credittotal) {
				echo '<td align=center style="background-color: #fddbdb"><b>Required to balance - ' .
					number_format(abs($debittotal-$credittotal),2);
			}
			if ($debittotal>$credittotal) {echo ' Credit';} else if ($debittotal<$credittotal) {echo ' Debit';}
			echo '</b></td></tr></table>';

if (ABS($_SESSION['JournalDetail']->JournalTotal)<0.001 AND $_SESSION['JournalDetail']->GLItemCounter > 0){
	echo "<br><br><div class='centre'><input type=submit name='CommitBatch' value='"._('Accept and Process Journal')."'></div>";
} elseif(count($_SESSION['JournalDetail']->GLEntries)>0) {
	echo '<br><br>';
	prnMsg(_('The journal must balance ie debits equal to credits before it can be processed'),'warn');
}

if (!isset($_GET['NewJournal']) or $_GET['NewJournal']=='')
 {
	echo "<script>defaultControl(document.form.GLManualCode);</script>";
} 
else 
{
	echo "<script>defaultControl(document.form.JournalProcessDate);</script>";
}
echo '</form>';
include('includes/footer.inc');
?>