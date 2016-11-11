<?php
$PageSecurity = 2;
if(isset($_POST['period_id'])){
include('includes/session.inc');
include('includes/PDFStarter.php');
$FontSize=13;
echo '<p class="page_title_text">' . ' ' . _('School Mean') . '';
$_SESSION['class'] = $_POST['class_id'];
$_SESSION['period'] = $_POST['period_id'];
$_SESSION['subject'] = $_POST['subject_id'];			
$PageNumber=1;
$line_height=12;
if ($PageNumber>1){
	$pdf->newPage();
}
$FontSize=18;
$YPos= $Page_Height-$Top_Margin;
$XPos=0;
$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$XPos+200,$YPos-120,0,80);

$DebtorNo=$_POST['debtorno'];


$FontSize=8;$LeftOvers = $pdf->addTextWrap(100,$YPos-($line_height*10),400,$FontSize,strtoupper($_SESSION['CompanyRecord']['coyname']));
		$FontSize=12;
		$LeftOvers = $pdf->addTextWrap(180,$YPos-($line_height*11),400,$FontSize,
		$_SESSION['CompanyRecord']['regoffice3'].' - '.$_SESSION['CompanyRecord']['regoffice5'].' - '.('TEL :').' '.
		$_SESSION['CompanyRecord']['regoffice4']);
		$FontSize=10;
		$LeftOvers = $pdf->addTextWrap(240,$YPos-($line_height*12),300,$FontSize,_('EMAIL :').' '.$_SESSION['CompanyRecord']['email']);


$sql = "SELECT t.title,y.year FROM collegeperiods cp
INNER JOIN terms t ON t.id=cp.term_id
INNER JOIN years y ON y.id=cp.year
WHERE cp.id =  '". $_POST['period_id'] ."'";
$result=DB_query($sql,$db);
$myrow=DB_fetch_array($result);
$year=$myrow['year'];
$term=$myrow['title'];



/*$LeftOvers = $pdf->addTextWrap(100,$YPos-($line_height*11),500,$FontSize, _('Reportcard For').': ' . $myrow[0].'    '._('Period').': ' .$myrow2[1].'-'.$myrow2[2]);*/	
$LeftOvers = $pdf->addTextWrap(200,$YPos-($line_height*12),400,$FontSize,_('SCHOOL MEAN SCORE'));
 $LeftOvers = $pdf->addTextWrap(200,$YPos-($line_height*12.3),50,$FontSize,'______________________________________________________________________________');


$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*15),300,$FontSize, _('Period').': ' . $term.' '.$year);	
$YPos +=20;
$YPos -=$line_height;
//Note, this is ok for multilang as this is the value of a Select, text in option is different

$YPos -=(12*$line_height);

$pdf->line(39, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height);

$YPos -=50;
$YPos -=$line_height;
$Left_Margin2=100;
$pdf->line(39, $YPos+$line_height,500, $YPos+$line_height);
$pdf->line(39, $YPos,500, $YPos);

$LeftOvers = $pdf->addTextWrap(40,$YPos+1,300,$FontSize,_('Rank'));
$LeftOvers = $pdf->addTextWrap(70,$YPos+1,300,$FontSize,_('Classes'));
$LeftOvers = $pdf->addTextWrap(200,$YPos+1,300,$FontSize,_('Mean Score'));
$LeftOvers = $pdf->addTextWrap(300,$YPos+1,300,$FontSize,_('Mean Grade'));
$line_width=40;
$XPos=160;
$YPos2=$YPos+$line_height;
$count=0;
$i=0;
$reportgrade=0;
$total_mean=0;


if($count>0)
$subject_mean=number_format($total_mean/$count,2);
else
$subject_mean=0;
$count2=0;
$total_mean=0;
$sqlclass = "SELECT gl.* FROM gradelevels gl
INNER JOIN class_means cm ON cm.class=gl.id
WHERE cm.period_id='" . $_POST['period_id'] ."'
ORDER BY cm.mean DESC";
$resultclass = DB_query($sqlclass,$db);	
while ($myrowclass= DB_fetch_array($resultclass))
{
		$sql = "SELECT cm.mean,gl.grade_level FROM class_means cm
		INNER JOIN gradelevels gl ON gl.id=cm.class
		WHERE cm.period_id='" . $_POST['period_id'] ."'
		AND cm.class='" . $myrowclass['id'] ."'
		";
		$result=DB_query($sql,$db);
		if(DB_num_rows($result)>0)
		{
		while($myrow=DB_fetch_array($result)){
					$count2=$count2+1;
					$sql2 = "SELECT grade FROM reportcardgrades
					WHERE range_from <=  '".$myrow['mean']."'
					AND range_to >='". $myrow['mean']."'";
					$result2=DB_query($sql2,$db);
					$myrow2=DB_fetch_array($result2);
					$reportgrade=$myrow2['grade'];
					
			$LeftOvers = $pdf->addTextWrap(40,$YPos-10,300,$FontSize,$count2);
			$LeftOvers = $pdf->addTextWrap(70,$YPos-10,300,$FontSize,$myrow['grade_level']);	
			$LeftOvers = $pdf->addTextWrap(200,$YPos-10,300,$FontSize,number_format($myrow['mean'],2));
			$LeftOvers = $pdf->addTextWrap(300,$YPos-10,300,$FontSize,$reportgrade);
			$total_mean=$total_mean+$myrow['mean'];	
			$YPos -=$line_height;
			}
		}
		
}	
if($count2>0){
$school_mean=$total_mean/$count2;
}
else
$school_mean=0;	
$sql = "SELECT grade FROM reportcardgrades
		WHERE range_from <=  '".$school_mean."'
		AND range_to >='". $school_mean."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_array($result);
		$schoolgrade=$myrow['grade'];	
$YPos -=$line_height;		
$LeftOvers = $pdf->addTextWrap(100,$YPos-10,300,$FontSize,_('School Mean Score:').' '.number_format($school_mean,2));	
$LeftOvers = $pdf->addTextWrap(350,$YPos-10,300,$FontSize,_('School Mean:').' '.$schoolgrade);		
$pdf->line(39, $YPos2,39, $YPos+($line_height*1));
$pdf->line(69, $YPos2,69, $YPos+($line_height*1));
$pdf->line(198, $YPos2,198, $YPos+($line_height*1));
$pdf->line(298, $YPos2,298, $YPos+($line_height*1));
$pdf->line(500, $YPos2,500, $YPos+($line_height*1));
$pdf->line(39, $YPos+$line_height,500, $YPos+$line_height);

$pdf->Output('Receipt-'.$_GET['ReceiptNumber'], 'I');


}
else { /*The option to print PDF was not hit */
include('includes/session.inc');
$title = _('School Mean');
include('includes/header.inc');
echo '<FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<CENTER><TABLE class="enclosed"><TR><TD>' . _('Period:') . '</TD><TD><SELECT Name="period_id">';
		DB_data_seek($result, 0);
		$sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
		INNER JOIN terms ON terms.id=cp.term_id
		INNER JOIN years ON years.id=cp.year ";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['id'] == $_POST['id']) {  
				echo '<OPTION SELECTED VALUE=';
			} else {
				echo '<OPTION VALUE=';
			}
			echo $myrow['id'] . '>'.' '.$myrow['title'].' '.$myrow['year'];
		} //end while loop
	echo '</SELECT></TD></TR>';
	echo "</TABLE>";
	echo "<P><CENTER><INPUT TYPE='Submit' NAME='PrintPDF' VALUE='" . _('PrintPDF') . "'>";

	include('includes/footer.inc');;
} /*end of else not PrintPDF */

?>