<?php

$PageSecurity = 2;
if(isset($_POST['percentage_below']) && isset($_POST['class']) && isset($_POST['PrintPDF'])){
include('includes/session.inc');
include('includes/PDFStarter.php');


$line_width=50;
$line_height=12;
$PageNumber = 1;                       
$FontSize=6;
NewPageHeader ();
$YPos -= (2 * $line_height);
$sql = "SELECT dm.*,gl.grade_level, 

(
						SELECT
				            coalesce(sum(invoice_items.totalinvoice),  0) AS total 
				        FROM
				            invoice_items  INNER JOIN
				            salesorderdetails ON (salesorderdetails.id = invoice_items.invoice_id)
				        WHERE  
				            salesorderdetails.student_id  = dm.id ) as student_total,
	                (
	                    SELECT 
	                        coalesce(-sum(debtortrans.ovamount), 0) AS amount 
	                    FROM
	                        debtortrans INNER JOIN
	                        salesorderdetails  ON (salesorderdetails.id = debtortrans.transno)
	                    WHERE 
	                        salesorderdetails.student_id = dm.id) AS paid,
	                ( select student_total - paid ) AS owing,
					( select  (1-(paid/student_total))*100 ) AS percentage


FROM debtorsmaster dm
INNER JOIN gradelevels gl ON gl.id=dm.grade_level_id
WHERE dm.grade_level_id='".$_POST['class']."'
order BY dm.name ";
$result = DB_query($sql,$db);
$BoxHeight=84;	
$studentcard=1;
$percent=0;
while ($myrow= DB_fetch_array($result))
{
$percent=number_format($myrow['percentage'],2);
if($percent>0)
$percent=$percent;
else
$percent=0;
//$percent=number_format($row['percentage'],2);
if($percent> $_POST['percentage_over'] && $percent< $_POST['percentage_below']){
if (($j%2)==1)
$YPos -=$line_height;

if ($studentcard==1) {
$pdf->line($Left_Margin, $YPos,190, $YPos);
$HeadPos1= $YPos;	
$pdf->line($Left_Margin, $YPos-$BoxHeight,$Left_Margin, $YPos);
$pdf->addJpegFromFile($_SESSION['LogoFile'] ,70,$YPos-32,0,30);
$LeftOvers = $pdf->addTextWrap(70,$YPos-40,300,$FontSize,_('STUDENT MEAL CARD'));
$LeftOvers = $pdf->addTextWrap(42,$YPos-45,300,$FontSize,_('ADMNO').':'.$myrow['debtorno']);
$LeftOvers = $pdf->addTextWrap(42,$YPos-50,300,$FontSize,_('Name').':'.$myrow['name']);
$LeftOvers = $pdf->addTextWrap(42,$YPos-55,300,$FontSize,_('Course').':'.$myrow['course_name']);
$LeftOvers = $pdf->addTextWrap(42,$YPos-60,300,$FontSize,_('Balance').':'.$myrow['owing']);
$LeftOvers = $pdf->addTextWrap(42,$YPos-65,300,$FontSize,_('Percentage Balance KSH').':'.$percent._('%'));
$FontSize=4;
$LeftOvers = $pdf->addTextWrap(42,$YPos-70,300,$FontSize,_('This card is not interchable and MUST be produced for services at the Institution'));
$FontSize=5;
$LeftOvers = $pdf->addTextWrap(42,$YPos-75,300,$FontSize,_('Registrar Sign'));
 $LeftOvers = $pdf->addTextWrap(70,$YPos-77,50,$FontSize,'______________________________________________________________________________');
$LeftOvers = $pdf->addTextWrap(42,$YPos-82,300,$FontSize,_('Once this card is lost you pay 50 replacement'));
$pdf->line($Left_Margin+150, $YPos-$BoxHeight,$Left_Margin+150, $YPos);
$YPos -=(9*$line_height);
$pdf->line($Left_Margin, $YPos+24,190, $YPos+24);
$studentcard=2;
}
elseif($studentcard==2) {
$FontSize=6;
$YPos = $HeadPos1;
$pdf->line(205, $YPos-$BoxHeight,205, $YPos);
$pdf->addJpegFromFile($_SESSION['LogoFile'] ,235,$YPos-32,0,30);
$pdf->line(205, $YPos,355, $YPos);
$LeftOvers = $pdf->addTextWrap(238,$YPos-40,300,$FontSize,_('STUDENT MEAL CARD'));	
$LeftOvers = $pdf->addTextWrap(210,$YPos-45,300,$FontSize,_('ADMNO').':'.$myrow['debtorno']);
$LeftOvers = $pdf->addTextWrap(210,$YPos-50,300,$FontSize,_('Name').':'.$myrow['name']);
$LeftOvers = $pdf->addTextWrap(210,$YPos-55,300,$FontSize,_('Course').':'.$myrow['course_name']);
$LeftOvers = $pdf->addTextWrap(210,$YPos-60,300,$FontSize,_('Balance').':'.$myrow['owing']);
$LeftOvers = $pdf->addTextWrap(210,$YPos-65,300,$FontSize,_('Percentage Balance KSH').':'.$percent._('%'));
$FontSize=4;
$LeftOvers = $pdf->addTextWrap(210,$YPos-70,300,$FontSize,_('This card is not interchable and MUST be produced for services at the Institution'));
$FontSize=5;
$LeftOvers = $pdf->addTextWrap(210,$YPos-75,300,$FontSize,_('Registrar Sign'));
 $LeftOvers = $pdf->addTextWrap(240,$YPos-77,50,$FontSize,'______________________________________________________________________________');
$LeftOvers = $pdf->addTextWrap(210,$YPos-82,300,$FontSize,_('Once this card is lost you pay 50 replacement'));
$pdf->line(355, $YPos-$BoxHeight,355, $YPos);
$YPos -=(9*$line_height);
$pdf->line(205, $YPos+24,355, $YPos+24);
$studentcard=3;
}
elseif($studentcard==3) {
$FontSize=6;
$YPos = $HeadPos1;
$pdf->line(365, $YPos-$BoxHeight,365, $YPos);
$pdf->addJpegFromFile($_SESSION['LogoFile'] ,398,$YPos-32,0,30);
$pdf->line(365, $YPos,512, $YPos);
$LeftOvers = $pdf->addTextWrap(398,$YPos-40,300,$FontSize,_('STUDENT MEAL CARD'));		
$LeftOvers = $pdf->addTextWrap(370,$YPos-45,300,$FontSize,_('ADMNO').':'.$myrow['debtorno']);
$LeftOvers = $pdf->addTextWrap(370,$YPos-50,300,$FontSize,_('Name').':'.$myrow['name']);
$LeftOvers = $pdf->addTextWrap(370,$YPos-55,300,$FontSize,_('Course').':'.$myrow['course_name']);
$LeftOvers = $pdf->addTextWrap(370,$YPos-60,300,$FontSize,_('Balance').':'.$myrow['owing']);
$LeftOvers = $pdf->addTextWrap(370,$YPos-65,300,$FontSize,_('Percentage Balance KSH').':'.$percent._('%'));
$FontSize=4;
$LeftOvers = $pdf->addTextWrap(370,$YPos-70,300,$FontSize,_('This card is not interchable and MUST be produced for services at the Institution'));
$FontSize=5;
$LeftOvers = $pdf->addTextWrap(370,$YPos-75,300,$FontSize,_('Registrar Sign'));
 $LeftOvers = $pdf->addTextWrap(400,$YPos-77,50,$FontSize,'______________________________________________________________________________');
$LeftOvers = $pdf->addTextWrap(370,$YPos-82,300,$FontSize,_('Once this card is lost you pay 50 replacement'));
$pdf->line(512, $YPos-$BoxHeight,512, $YPos);
$YPos -=(9*$line_height);
$pdf->line(365, $YPos+24,512, $YPos+24);	
$studentcard=1;
}
if ($YPos < ($Bottom_Margin + (5* $line_height))){ //need 5 lines grace otherwise start new page
			$PageNumber++;
			NewPageHeader ();
		}
	}
}	

$pdf->Output('Receipt-'.$_GET['ReceiptNumber'], 'I');
	
}

else { /*The option to print PDF was not hit */

	include('includes/session.inc');
	$title = _('Manage Students');

include('includes/header.inc');

echo '<FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<CENTER><TABLE><TR><TD class="visible">' . _('Percentage Below') . '</TD><TD class="visible">
<input type="text" Name="percentage_below">';
		
	echo '</TD></TR>';
echo '<CENTER><TR><TD class="visible">' . _('Percentage Over') . '</TD><TD class="visible">
<input type="text" Name="percentage_over">';
	echo '</TD></TR>';	
echo '<tr><td>' . _('Class') . ":</td>
		<td><select name='class'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select Class');
		$sql="SELECT id,grade_level FROM gradelevels
		ORDER BY grade_level";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
		echo '<option value='. $myrow['id'] . '>' . $myrow['grade_level'];
		} //end while loop
		DB_data_seek($result,0);
	echo '</select></td></tr></table>';
	
	$sql = "SELECT sr.secrolename FROM www_users us
	INNER JOIN securityroles sr ON sr.secroleid=us.fullaccess
		WHERE us.userid=  '" . trim($_SESSION['UserID']) . "'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
	if($myrow[0]==_('System Administrator') || $myrow[0]==_('Academic Officer')){
	echo "<P><INPUT TYPE='Submit' NAME='PrintPDF' VALUE='" . _('PrintPDF') . "'>";
	}

	include('includes/footer.inc');
} /*end of else not PrintPDF */


function NewPageHeader () {
	global $PageNumber,
				$pdf,
				$YPos,
				$Page_Height,
				$Page_Width,
				$Top_Margin,
				$FontSize,
				$Left_Margin,
				$Right_Margin,
				$line_height;
				
	/*PDF page header for GL Account report */

	if ($PageNumber > 1){
		$pdf->newPage();
	}

	$YPos= $Page_Height-$Top_Margin;
	
	$YPos -=(2*$line_height);

}

?>