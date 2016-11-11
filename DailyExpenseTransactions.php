<?php

$PageSecurity = 8;
include ('includes/session.inc');
$title = _('Daily Expense Transactions');
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

	echo '<tr><td>' . _('Transactions Dated') . ':</td>
		<td><input type="text" name="TransDate" class="date" alt="'.$_SESSION['DefaultDateFormat'].'" maxlength=10 size=11
			onChange="isDate(this, this.value, '."'".$_SESSION['DefaultDateFormat']."'".')" value="' .
				date($_SESSION['DefaultDateFormat']) . '"></td>
		</tr>';

	echo '</table>';
	echo '<br><div class="centre"><input type="submit" name="Show" value="' . _('Show transactions'). '"></div>';
	echo '</form>';
} else {
	$sql="SELECT gltrans.*,chartmaster.accountname as name
			FROM gltrans,chartmaster,accountgroups
			WHERE chartmaster.group_= accountgroups.groupname
			AND gltrans.account = chartmaster.accountcode
			AND (accountgroups.sectioninaccounts = 5 OR accountgroups.sectioninaccounts = 10)
			AND inputdate = '".FormatDateForSQL($_POST['TransDate'])."'";
	$result = DB_query($sql, $db);
	if (DB_num_rows($result)>0) {
		echo '<table border="\1"\>';
		echo '<tr><th colspan=7><font size=3 color=blue>';
		echo _('Payment Transactions For').' '.$_POST['TransDate'];
		echo '</font></th></tr>';
		echo '<tr>';
		echo '<th>'._('Account').'</th>';
		echo '<th>'._('Expense Date').'</th>';
		echo '<th>'._('System Entry Date').'</th>';
		echo '<th>'._('Amount').' '.'</th>';
		echo '<th>'._('Narrative').' '.'</th>';
		while ($myrow=DB_fetch_array($result)) {
		echo '<tr>';
		echo '<td>'.$myrow['name'].'</td>';
		echo '<td>'.$myrow['trandate'].'</td>';
		echo '<td>'.$myrow['inputdate'].'</td>';
		echo '<td>'.$myrow['amount'].'</td>';
		echo '<td>'.$myrow['narrative'].'</td>';
		$AccountCurrTotal += $myrow['amount'];
		echo '</tr>';
		}			echo '<tr>';
			echo '<td>'.Total.'</td>';
			echo '<td>'.$AccountCurrTotal.'</td>';
			echo '</tr>';
		echo '</table>';
	} else {
		prnMsg( _('There are no expense transactions on this day'), 'info');
	}
	echo '<form action=' . $_SERVER['PHP_SELF'] . '?' . SID . ' method=post>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<br><div class="centre"><input type="submit" name="Return" value="' . _('Select Another Date'). '"></div>';
	echo '</form>';
}
include('includes/footer.inc');

?>