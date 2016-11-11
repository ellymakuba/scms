<?php

$PageSecurity = 8;
include ('includes/session.inc');
$title = _('Daily Student Bank Payments');
include('includes/header.inc'); ?>

<script type="text/javascript">
<!--
function confirmation() {
	var answer = confirm("Are you sure you want to add this Payment?")
	if (answer){
		alert("Bye bye!")
		window.location = "http://localhost/";
	}
	else{
		alert("Thanks for sticking around!")
	}
}
//-->
</script>

<?php
echo '<p class="page_title_text"><img src="'.$rootpath.'/css/'.$theme.'/images/money_add.png" title="' .
	 _('Search') . '" alt="">' . ' ' . $title.'</p>';

if (!isset($_POST['Show'])) {
	echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';

	echo '<table class=enclosed>';

	$SQL = 'SELECT bankaccountname,
				bankaccounts.accountcode,
				bankaccounts.currcode
			FROM bankaccounts,
				chartmaster
			WHERE bankaccounts.accountcode=chartmaster.accountcode';

	$ErrMsg = _('The bank accounts could not be retrieved because');
	$DbgMsg = _('The SQL used to retrieve the bank accounts was');
	$AccountsResults = DB_query($SQL,$db,$ErrMsg,$DbgMsg);

	echo '<tr><td>' . _('Transactions Dated') . ':</td>
		<td><input type="text" name="TransDate" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" maxlength=10 size=11
			onChange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')" value="' .
				date($_SESSION['DefaultDateFormat']) . '"></td>
		</tr>';

	echo '</table>';
	echo '<br><div class="centre"><input type="submit" name="Show" value="' . _('Show transactions'). '"></div>';
	echo '</form>';
} else {
	

	$sql="SELECT banktrans.amount,banktrans.inputdate,banktrans.addedby,bankaccounts.bankaccountname as accname,debtorsmaster.name as name,debtorsmaster.debtorno as regno
			FROM banktrans,bankaccounts,debtorsmaster,salesorderdetails
			WHERE banktrans.inputdate='".FormatDateForSQL($_POST['TransDate'])."'
			AND bankaccounts.accountcode=banktrans.bankact
			AND salesorderdetails.id=banktrans.transno
			AND salesorderdetails.student_id=debtorsmaster.id";
	$result = DB_query($sql, $db);
	if (DB_num_rows($result)>0) {
		echo '<table width="50%">';
		echo '<tr><th colspan=7><font size=3 color=blue>';
		echo _('Payment Transactions For').' '.$_POST['TransDate'];
		echo '</font></th></tr>';
		echo '<tr>';
		echo '<th>'._('Student AdmsnNo').' '.'</th>';
		echo '<th>'._('Student Name').' '.'</th>';
		echo '<th>'._('Amount in').' '.$_SESSION['CompanyRecord']['currencydefault'].'</th>';
		echo '<th>'._('Account').'</th>';
		echo '<th>'._('Added By').' '.'</th>';
		echo '</tr>';
		while ($myrow=DB_fetch_array($result)) {
		echo '<tr>';
		echo '<td class=visible>'.$myrow['regno'].'</td>';
		echo '<td class=visible>'.$myrow['name'].'</td>';
		echo '<td class=visible>'.number_format($myrow['amount'],2).'</td>';
		echo '<td class=visible>'.$myrow['accname'].'</td>';
		echo '<td class=visible>'.$myrow['addedby'].'</td>';
		$AccountCurrTotal += $myrow['amount'];
		echo '</tr>';
		}	
			echo '<tr>';
			echo '<td>'.Total.'</td>';
			echo '<td>'.'</td>';
			echo '<td>'.number_format($AccountCurrTotal,2).'</td>';
			echo '</tr>';
		echo '</table>';
	} else {
		prnMsg( _('There are no transactions for this account on that day'), 'info');
	}
	echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<br><div class="centre"><input type="submit" name="Return" value="' . _('Select Another Date'). '"></div>';
	echo '</form>';
}
include('includes/footer.inc');

?>