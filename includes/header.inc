<?php
/* $Id: header.inc 3746 2010-09-16 15:13:24Z tim_schofield $ */

	// Titles and screen header
	// Needs the file config.php loaded where the variables are defined for
	//  $rootpath
	//  $title - should be defined in the page this file is included with

	$StrictXHTML=False;

	if (!headers_sent()){
		if ($StrictXHTML) {
			header('Content-type: application/xhtml+xml; charset=utf8');
		} else {
			header('Content-type: text/html; charset=utf8');
		}
	}
	echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
			"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';


	echo '<html xmlns="http://www.w3.org/1999/xhtml"><head><title>' . $title . '</title>';
	if ($StrictXHTML) {
		echo '<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf8" />';
	} else {
		echo '<meta http-equiv="Content-Type" content="application/html; charset=utf8" />';
	}

echo '<link href="' . $rootpath . '/css/'. $_SESSION['Theme'] .'/default.css" rel="stylesheet" type="text/css" />';
echo '<script type="text/javascript" src = "'.$rootpath.'/javascripts/jquery.js"></script>';
echo '<script type="text/javascript" src = "'.$rootpath.'/javascripts/jquery/jquery-ui.js"></script>';
echo '</HEAD>';
echo '<BODY>';


//The module link codes are hard coded in a switch statement below to determine the options to show for each tab */
$ModuleLink = array('RM','EM', 'FM',  'BM','RPM','System');
/*The headings showing on the tabs accross the main index used also in WWW_Users for defining what should be visible to the user */
$ModuleList = array(_('Registration Module'),_('Exam Module'),_('Finance Module'),_('Billing Module'),_('Reports Module'),_('Setup'));
if (isset($_GET['Application'])){ /*This is sent by this page (to itself) when the user clicks on a tab */
	$_SESSION['Module'] = $_GET['Application'];
}
echo '<table width="100%" style="border:2px solid black;" ><tr><td width="20%" valign="top" style="background-color:#EEEEFF;">';
echo  '<div id="accordion">';
$i=0;
while ($i < count($ModuleLink)){
if ($_SESSION['ModulesEnabled'][$i]==1)
{
if (!isset($_SESSION['Module'])OR $_SESSION['Module']=='')
{
   $_SESSION['Module']=$ModuleLink[$i];
}
if ($ModuleLink[$i] == $_SESSION['Module'])
{
   echo '<h3 class="main_menu_selected"><a   href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Application='. $ModuleLink[$i] . '">';
   echo ''.$ModuleList[$i].'</a></h3>';
switch ($_SESSION['Module']) {
case 'RM':
 	echo '<div class="contentList">';
	echo '<li>'. "<a href='" . $rootpath  . '/Students.php?' . SID . "'>" . _('Add New Student') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/SelectStudent.php?' . SID . "'>" . _('Manage Students') . '</a>'.'</li>';
        echo '</div>';
break;
case 'EM':
	echo '<div class="contentList">';
	echo '<li>'."<a href='" . $rootpath . '/RegisterStudents.php?' . SID . "'>" . _('Stream Subject Registration') . '</a>'.'</li>';
	echo '<li>'. " <a href='" . $rootpath . '/InsertMarks.php?' . SID . "'>" . _('Enter Marks For Registered Students') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath . '/DeRegisterStudents.php?' . SID . "'>" . _('Stream Subject Deregistration') . '</a>'.'</li>';
	echo '<li>'. " <a href='" . $rootpath . '/EditMarks.php?' . SID . "'>" . _('Edit Entered Marks') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/AddSubject.php?' . SID . "'>" . _('Manage Subjects') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/AddClassYear.php?' . SID . "'>" . _('Manage Classes') .'</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/AddClass.php?' . SID . "'>" . _('Manage Streams') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/AddMarkingPeriod.php?' . SID . "'>" . _('Manage exam modes') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/AddDepartment.php?' . SID . "'>" . _('Manage Departments') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/AddTerm.php?' . SID . "'>" . _('Manage School Terms') .'</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/AddYear.php?' . SID . "'>" . _('Manager Years') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/AddReportCardGrade.php?' . SID . "'>" . _('Manage Report Card Grades') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/AddExamRule.php?' . SID . "'>" . _('Manage Exam Rules') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/AddExamType.php?' . SID . "'>" . _('Manage Exam Types') . '</a>'.'</li>';
	echo '<li>'. " <a href='" . $rootpath . '/GenerateTermData.php?' . SID . "'>" . _('Generate Term Data') . '</a>'.'</li>';
	echo '</div>';
break;
Case 'FM':
	echo '<div class="contentList">';
	echo '<li>'. " <a href='" . $rootpath . '/Supplier.php?' . SID . "'>" . _('Add New Supplier') . '</a>'.'</li>';
	echo '<li>'.'<a href="' . $rootpath . '/GLJournal.php?' .sid . '&NewJournal=Yes">' . _('Journal Entry') . '</a>'.'</li>';
	echo '<li >'.'<a href="' . $rootpath . '/GLTrialBalance.php?' . SID . '">' . _('Trial Balance') . '</a>'.'</li>';
	echo '<li>'.'<a href="' . $rootpath . '/DailyBankTransactions.php">' . _('Daily Bank Transactions') . '</a>'.'</li>';
	echo '<li>'. '<a href="' . $rootpath . '/RangePayments.php?' . SID . '">' . _('Period Range Payments') . '</a>'.'</li>';
	echo '<li>'. ' <a href="' . $rootpath . '/DailyTransactions.php?' . SID . '">' . _('Daily Payment Transactions') . '</a>'.'</li>';
	echo '<li>'.' <a href="' . $rootpath . "/GLProfit_Loss.php?" . SID . '">' . _('Profit and Loss Statement') . '</a>'.'</li>';
	echo '<li>'.'<a href="' . $rootpath . '/GLBalanceSheet.php?' . SID . '">' . _('Balance Sheet') . '</a>'.'</li>';
	echo '<li>'. ' <a href="' . $rootpath . '/GLBudgets.php?' . SID . '">' . _('GL Budgets') . '</a>'.'</li>';
	echo '<li>'. '<a href="' . $rootpath . '/AccountGroups.php?' . SID . '">' . _('Account Groups') . '</a>'.'</li>';
	echo '<li>'.'<a href="' . $rootpath . '/AccountSections.php?' . SID . '">' . _('Account Sections') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath . '/BankingForm.php' . SID . "'>" . _('Banking') . '</a>'.'</li>';
	echo '</div>';
break;
Case 'BM':
	echo '<div class="contentList">';
	echo '<li>'. " <a href='" . $rootpath . '/FeeStructureItem.php?' . SID . "'>" . _('Products List') . '</a>'.'</li>';
	echo '<li>'. " <a href='" . $rootpath . '/FeeStructure.php?' . SID . "'>" . _('Create Fee Structure') . '</a>'.'</li>';
	echo '<li>'. " <a href='" . $rootpath . '/ManageFeeStructures.php?' . SID . "'>" . _('Manage Fee Structures') . '</a>'.'</li>';
	echo '<li>'. " <a href='" . $rootpath . '/classBilling.php?' . SID . "'>" . _('Annual class billing') . '</a>'.'</li>';
	echo '</div>';
break;
Case 'RPM':
	echo '<div class="contentList">';
	echo '<li>'. " <a href='" . $rootpath . '/EmptyMarkSheet.php?' . SID . "'>" . _('Print Empty Marksheet') . '</a>'.'</li>';
	echo '<li>'. " <a href='" . $rootpath . '/LecturerModeSubject.php?' . SID . "'>" . _('Stream Single Exam Marksheet') . '</a>'.'</li>';
	echo '<li>'. " <a href='" . $rootpath . '/ClassSingleExamMarkSheet.php?' . SID . "'>" . _('Class Single Exam Marksheet') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath . '/StreamSingleExamReportCards.php?' . SID . "'>" . _('Stream Single Exam Report Cards') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath . '/StreamReportCards.php' . SID . "'>" . _('Stream End Term Report Cards') . '</a>'.'</li>';
        echo '<li>'. "<a href='" . $rootpath . '/streamSMSResults.php' . SID . "'>" . _('Stream SMS Results') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/StreamReport.php?' . SID . "'>" . _('Consolidated Stream Marksheet Report') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/ClassReport.php?' . SID . "'>" . _('Consolidated Class Marksheet Report') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath . '/LecturerSubject.php?' . SID . "'>" . _('Exam and CATS Breakdown Report') .'</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/TeacherSubjectMean.php?' . SID . "'>" . _('Class Performance Per Subject') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/SubjectMeanRanking.php?' . SID . "'>" . _('Stream Performance in all subjects') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/SubjectMeanGraph.php?' . SID . "'>" . _('Subjects Mean Graph') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/schoolmean.php?' . SID . "'>" . _('School Mean') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/SchoolMeanGraph.php?' . SID . "'>" . _('School Mean Graphs') . '</a>'.'</li>';
	echo '<li>'. "<a href='" . $rootpath  . '/StudentPerClassReport.php?' . SID . "'>" . _('Student In a Class') . '</a>'.'</li>';
	echo '</div>';
break;
Case 'System' :
	echo '<div class="contentList">';
	echo '<li>'. '<a href="' . $rootpath . '/CompanyPreferences.php?' . SID . '">' . _('Center Preferences') . '</a>'.'</li>';
	echo '<li>'.'<a href="' . $rootpath . '/SystemParameters.php?' . SID . '">' . _('Configuration Settings') . '</a>'.'</li>';
	echo '<li>'.'<a href="' . $rootpath . '/WWW_Users.php?' . SID . '">' . _('User Maintenance') . '</a>'.'</li>';
	echo '<li>'. '<a href="' . $rootpath . '/WWW_Access.php?' . SID . '">' . _('Role Permissions') . '</a>'.'</li>';
	echo '<li>'. ' <a href="' . $rootpath . '/BankAccounts.php?' . SID . '">' . _('Bank Accounts') . '</a>'.'</li>';
	echo '<li>'. '<a href="' . $rootpath . '/Currencies.php?' . SID . '">' . _('Currency Maintenance') . '</a>'.'</li>';
	echo '<li>'. '<a href="' . $rootpath . '/AuditTrail.php">' . _('View Audit Trail') . '</a>'.'</li>';
	echo '<li>'. '<a href="' . $rootpath . '/PaymentMethods.php?' . SID . '">' . _('Payment Methods') . '</a>'.'</li>';
	echo '<li>'. '<a href="' . $rootpath . '/GLAccounts.php?' . SID . '">' ._('GL Account'). '</a>'.'</li>';
	echo '<li>'. '<a href="' . $rootpath . '/EndYear.php?' . SID . '">' ._('Close Academic Year'). '</a>'.'</li>';
	echo '<li>'. '<a href="' . $rootpath . '/AddCollegePeriod.php?' . SID . '">' ._('School Period Set Up'). '</a>'.'</li>';
	echo '</div>';
break;
}
}
else
{
   echo '<h3 class="main_menu_unselected"><a  href="' . $_SERVER['PHP_SELF'] . '?' . SID . '&Application='. $ModuleLink[$i] . '">';
   echo ''.$ModuleList[$i].'</a></h3>';
}
echo '</br>';
}
$i++;
}
echo '</div></td><td width="80%" valign="top" style="border: 2px outset #AAAAAA; background-color:white; padding:5px 5px 5px 5px;">';
echo '<div id="homeContent">';
echo '<TABLE CLASS="callout_main" CELLPADDING="0" CELLSPACING="0">';
	echo '<TR>';
	echo '<TR>';
	echo '<TD CLASS="quick_menu">';
	if (isset($title)) {
		echo '<TABLE CELLPADDING="0" CELLSPACING="0">';
		echo '<TR>';
		echo '<TD ALIGN="left" WIDTH="50%" CLASS="quick_menu_left">';
		echo '' . stripslashes($_SESSION['CompanyRecord']['coyname']);
		echo '</td>';
		echo '<TD ALIGN="left" WIDTH="50%" CLASS="quick_menu_left">';
		echo ' ' . '<img src="'.$rootpath.'/css/'.$theme.'/images/user.png" TITLE="User" ALT="' . _('User') . '"> </img>'.' Logged in as ' .
		stripslashes($_SESSION['UsersRealName']) . '</a>';
		echo '</td>';
		echo '<td class="quick_menu_tabs">';
		echo '<table CELLPADDING="5" CELLSPACING="5" valigh="top"><tr>';
		echo '<td class="quick_menu_tabs" align="center"><a href="' .  $rootpath . '/index.php?' . SID . '">
		<span style="text-decoration:underline;"></span> ' . _('Home') . '</a></td>';
		if (count($_SESSION['AllowedPageSecurityTokens'])>1){
			echo '<td class="quick_menu_tabs" align="center"><a  href="' .  $rootpath . '/SelectStudent.php?' . SID . '">
			<span style="text-decoration:underline;"></span> ' . _('Students') . '</a></td>';
		}
		echo '<td class="quick_menu_tabs" align="center"><a href="' . $rootpath . '/Logout.php?' . SID . '"
		onclick="return confirm(\'' . _('Are you sure you wish to logout?') . '\');">
		<span style="text-decoration:underline;"></span> '  . _('Logout') . '</a></td>';
		echo '</TR></TABLE>';
		echo '</TD></TR></TABLE>';
		echo '</TD>';
		echo '</TR>';
       echo '</TABLE>';
	}
echo '</div>';
?>
