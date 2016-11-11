<?php

$PageSecurity = 2;
if(isset($_POST['PrintPDF']) && isset($_POST['student_class'])){
include('includes/session.inc');
include('includes/PDFStarter.php');
require('grades/EndTermReportClass.php');
include('includes/phplot/phplot.php');
include("Numbers/Words.php");
	
$PageNumber=1;
$line_height=12;
$FontSize=18;
$YPos= $Page_Height-$Top_Margin;
$XPos=0;
$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$XPos+260,$YPos-120,0,100);
$YPos-=(2*$line_height);
$pdf->SetFont('times', '', 18, '', 'false');
$LeftOvers = $pdf->addTextWrap(100,$YPos-($line_height*10),400,$FontSize,strtoupper($_SESSION['CompanyRecord']['coyname']));
$FontSize=12;
$LeftOvers = $pdf->addTextWrap(180,$YPos-($line_height*11),400,$FontSize,
$_SESSION['CompanyRecord']['regoffice3'].' - '.$_SESSION['CompanyRecord']['regoffice5'].' - '.('TEL :').' '.
$_SESSION['CompanyRecord']['regoffice4']);
$YPos-=(2*$line_height);
$pdf->SetFont('times', '', 12, '', 'false');
$FontSize=12;


$_SESSION['class']=$_POST['student_class'];
$sql = "SELECT count(*) FROM debtorsmaster
WHERE class_id='".$_SESSION['class']."'";
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$query_data = DB_fetch_row($result);
$no_of_students = $query_data[0];

$sql = "SELECT class_name FROM classes
WHERE id='".$_SESSION['class']."'";
$result = DB_query($sql,$db);
$row= DB_fetch_row($result);
$class_name = $row[0];
$style = array('width' => 0.70, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'phase' => 10, 'color' => array(12, 12, 12));

$YPos -=140;
$LeftOvers = $pdf->addTextWrap(250,$YPos,300,$FontSize,strtoupper($class_name));
$YPos -=13;
$sql = "SELECT * FROM debtorsmaster
WHERE class_id='".$_SESSION['class']."'
ORDER BY name ";
$DbgMsg = _('The SQL that was used to retrieve the information was');
$ErrMsg = _('Could not check whether the group is recursive because');
$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
$YPos -=13;	
$YPos3=$YPos;	
$LeftOvers = $pdf->addTextWrap(70,$YPos+1,300,$FontSize,_('NAME'));
$LeftOvers = $pdf->addTextWrap(270,$YPos+1,300,$FontSize,_('ADMNO')); 
$LeftOvers = $pdf->addTextWrap(400,$YPos+1,300,$FontSize,_('GENDER'));
$pdf->line(60, $YPos,$Page_Width-$Right_Margin-25, $YPos,$style);
$YPos -=13;	
$counter=0;		
while ($row = DB_fetch_array($result))
{
    if ($YPos < ($Bottom_Margin + (2* $line_height)))
	{ //need 5 lines grace otherwise start new page
		$PageNumber++;
		NewPageHeader ();
	}
	$counter=$counter+1;
	$LeftOvers = $pdf->addTextWrap(70,$YPos+1,300,$FontSize,$counter);
	$LeftOvers = $pdf->addTextWrap(90,$YPos+1,300,$FontSize,$row['name']);
	$LeftOvers = $pdf->addTextWrap(270,$YPos+1,300,$FontSize,$row['debtorno']); 
	$LeftOvers = $pdf->addTextWrap(400,$YPos+1,300,$FontSize,$row['gender']);
	$pdf->line(60, $YPos-1,$Page_Width-$Right_Margin-25, $YPos-1,$style);
	$YPos -=(1*$line_height);
}
$LeftOvers = $pdf->addTextWrap(70,$YPos,300,$FontSize,_('Students Total:').' '.$no_of_students);
$line_width=70;
//$pdf->line(60, $YPos3,60, $YPos,$style);
//$pdf->line(540,$YPos3,540, $YPos,$style);
$pdf->line(60, $YPos-1,$Page_Width-$Right_Margin-25, $YPos-1,$style);
$pdf->Output('ReportCard-'.$_GET['ReceiptNumber'], 'I');
	
}
else { /*The option to print PDF was not hit */
include('includes/session.inc');
	$title = _('Manage Students');

include('includes/header.inc');

echo "<form method='post' action=" . $_SERVER['PHP_SELF'] . '>';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<table class="enclosed">';	
    echo '<tr><td>' . _('Class') . ":</td>
		<td><select name='student_class'>";
		echo '<OPTION SELECTED VALUE=0>' . _('Select Class');
		$sql="SELECT cl.id,cl.class_name,gl.grade_level FROM classes cl
		INNER JOIN gradelevels gl ON gl.id=cl.grade_level_id";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
	echo '<option value='. $myrow['id'] . '>' . $myrow['class_name'];
		} //end while loop
		DB_data_seek($result,0);
	echo '</select></td></tr></table>';
		echo '<table class="enclosed">';
echo "<br><div class='centre'><input  type='Submit' name='PrintPDF' value='" . _('Display Students') . "'>&nbsp;<input  type=submit action=RESET VALUE='" . _('Reset') . "'></div>";	
echo '</table>';
include('includes/footer.inc');
} /*end of else not PrintPDF */
function NewPageHeader () {
	global $PageNumber,
				$pdf,
				$YPos,
				$YPos2,
				$YPos4,
				$Page_Height,
				$Page_Width,
				$Top_Margin,
				$FontSize,
				$Left_Margin,
				$XPos,
				$XPos2,
				$Right_Margin,
				$line_height;
				$line_width;

	/*PDF page header for GL Account report */

	if ($PageNumber > 1){
		$pdf->newPage();
	}
$YPos= $Page_Height-$Top_Margin;
}
?>