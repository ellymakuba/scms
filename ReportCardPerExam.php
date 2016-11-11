<?php
$PageSecurity = 2;
if(isset($_POST['period_id']) && isset($_POST['student_id']) && isset($_POST['PrintPDF']) && isset($_POST['exam_mode']))
{
	include('includes/session.inc');
	include('includes/PDFStarter.php');
	require('grades/EndTermReportClassPerExam.php');
	require('grades/ModeClass.php');
	include('includes/phplot/phplot.php');
	include("Numbers/Words.php");
	
	$sql = "SELECT SUM(totalinvoice) as total FROM invoice_items,salesorderdetails 
	WHERE salesorderdetails.id=invoice_items.invoice_id
	AND student_id='".$_POST['student_id']."'";
	$DbgMsg = _('The SQL that was used to retrieve the information was');
	$ErrMsg = _('Could not check whether the group is recursive because');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$row = DB_fetch_array($result);
	$studenttotal = $row['total'];
	
	$sql = "SELECT SUM(ovamount) as totalpayment FROM debtortrans WHERE debtorno='".$_POST['student_id']."'";
	$ErrMsg = _('Could not check whether the group is recursive because');
	$result = DB_query($sql,$db,$ErrMsg,$DbgMsg);
	$row = DB_fetch_array($result);
	$studenttotalpayment = -$row['totalpayment'];
	$totalbalance=$studenttotal-$studenttotalpayment;
	if($totalbalance<0)	
	{
	 prnMsg(_('Cannot print a reportcard of students with a balance, Please let him clear'),'warn');
	}
	else
	{	
		$FontSize=13;
		$_SESSION['student'] = $_POST['student_id'];
		$_SESSION['period'] = $_POST['period_id'];		
		$PageNumber=1;
		$line_height=12;
		if ($PageNumber>1)
		{
			$pdf->newPage();
		}
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
		$FontSize=10;
		$LeftOvers = $pdf->addTextWrap(240,$YPos-($line_height*12),300,$FontSize,_('EMAIL :').' '.$_SESSION['CompanyRecord']['email']);
		$YPos-=(2*$line_height);
		$pdf->SetFont('times', '', 12, '', 'false');
		$FontSize=11;
		$style = array('width' => 0.70, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'phase' => 10, 'color' => array(12, 12, 12));
		$sql = "SELECT class_id,stream_id FROM exam_ranks  
		WHERE period_id =  '". $_POST['period_id'] ."'
		AND student_id='". $_POST['student_id'] ."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$class=$myrow[0];
		$stream=$myrow[1];
	
		$sql = "SELECT title FROM markingperiods  
		WHERE id =  '". $_POST['exam_mode'] ."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$mode_title=$myrow[0];
		
		$sql = "SELECT rank,class_id FROM exam_ranks  
		WHERE period_id =  '". $_POST['period_id'] ."'
		AND exam_id=  '". $_POST['exam_mode'] ."'
		AND student_id='". $_POST['student_id'] ."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$class_rank=$myrow[0];
	
		$sql = "SELECT COUNT(*) FROM exam_ranks  
		WHERE period_id =  '". $_POST['period_id'] ."'
		AND exam_id=  '". $_POST['exam_mode'] ."'
		AND class_id='$class'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$total_students=$myrow[0];
	
		$sql = "SELECT COUNT(*) FROM exam_ranks  
		WHERE period_id =  '". $_POST['period_id'] ."'
		AND exam_id=  '". $_POST['exam_mode'] ."'
		AND stream_id='$stream'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$out_of=$myrow[0];
		
		$sql = "SELECT mean,student_id FROM exam_ranks  
		WHERE period_id =  '". $_POST['period_id'] ."'
		AND stream_id='$stream'
		AND exam_id='".$_POST['exam_mode']."'
		ORDER BY mean DESC";
		$result=DB_query($sql,$db);
		$previous_mean=0;
		while($myrow=DB_fetch_array($result))
		{
			if ($myrow['mean'] == $previous_mean)
			{
				$streaRrank=$streaRrank;
				$rank_ties=$rank_ties+1;
			}
			else
			{
				$streaRrank=$rank_ties+$streaRrank+1;
				$rank_ties=0;
			}
			$previous_mean=$myrow['mean'];
			if($myrow['student_id']==$_POST['student_id'])
			{
				$sqlgenerate2 = "UPDATE exam_ranks
				SET streamRank='" . $streaRrank . "'
				WHERE student_id='".$_POST['student_id']."'
				AND period_id='".$_POST['period_id']."'
				AND exam_id='".$_POST['exam_mode']."'";
				$generate2 = DB_query($sqlgenerate2,$db);
			}	
		}		
		$sql = "SELECT streamRank FROM exam_ranks  
		WHERE period_id =  '". $_POST['period_id'] ."'
		AND student_id='".$_POST['student_id']."'
		AND exam_id='".$_POST['exam_mode']."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$streamRank=$myrow[0];
		
		$sql = "SELECT DISTINCT(rs.student_id),dtr.name,dtr.debtorno,gl.grade_level,dtr.grade_level_id,
		dtr.age,gl.id,dtr.balance 
		FROM registered_students rs
		INNER JOIN debtorsmaster dtr ON dtr.id=rs.student_id
		INNER JOIN classes cl ON cl.id=rs.class_id 
		INNER JOIN gradelevels gl ON gl.id=cl.grade_level_id
		WHERE dtr.id =  '". $_POST['student_id'] ."'
		AND rs.period_id='". $_POST['period_id'] ."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$feebalance=$myrow[8];
	
		$sql3 = "SELECT SUM(dm.age) as age,COUNT(dm.id) as student_count FROM debtorsmaster dm
		INNER JOIN registered_students rs ON rs.student_id=dm.id
		INNER JOIN classes cl ON cl.id=rs.class_id
		INNER JOIN gradelevels gl ON gl.id=cl.grade_level_id
		WHERE gl.id =  '". $myrow[7] ."'
		AND rs.period_id='". $_POST['period_id'] ."'";
		$result3=DB_query($sql3,$db);
		$myrow3=DB_fetch_row($result3);
		$age_sum=$myrow3[0];
		$student_count=$myrow3[1];
		//$average_standard_age=number_format($age_sum/$student_count,0);
			
		$sql2="SELECT cp.id,terms.title,years.year,cp.end_semester_date FROM collegeperiods cp
		INNER JOIN terms ON terms.id=cp.term_id
		INNER JOIN years ON years.id=cp.year 
		WHERE cp.id='".$_SESSION['period']."'";
		$result2=DB_query($sql2,$db);
		$myrow2=DB_fetch_row($result2);		
	
		$LeftOvers = $pdf->addTextWrap(240,$YPos-($line_height*12),350,$FontSize,$mode_title.' '._('REPORT CARD'));
		 $LeftOvers = $pdf->addTextWrap(239,$YPos-($line_height*12.3),55,$FontSize,
		 '______________________________________________________________________________');
		$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*14),300,$FontSize,_('NAME :'));
		$pdf->SetFont('times', 'B', 12, '', 'false');
		$LeftOvers = $pdf->addTextWrap(82,$YPos-($line_height*14),300,$FontSize,$myrow[1]);
		$pdf->SetFont('times', '', 12, '', 'false');
		$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*15),300,$FontSize,_('STREAM POSITION:'));
		$pdf->SetFont('times', 'B', 12, '', 'false');
		$LeftOvers = $pdf->addTextWrap(150,$YPos-($line_height*15),300,$FontSize,$streamRank);
		$pdf->SetFont('times', '', 12, '', 'false');
		$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*16),300,$FontSize,_('CLASS POSITION:'));
		$pdf->SetFont('times', 'B', 12, '', 'false');
		$LeftOvers = $pdf->addTextWrap(140,$YPos-($line_height*16),300,$FontSize,$class_rank);
		$pdf->SetFont('times', '', 12, '', 'false');
		$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*17),300,$FontSize,_('AGE').':'.$myrow[6].' '._('YEARS'));
	
		$LeftOvers = $pdf->addTextWrap(250,$YPos-($line_height*14),300,$FontSize, _('ADM NO').': ' . $myrow[2]);
		$LeftOvers = $pdf->addTextWrap(250,$YPos-($line_height*15),300,$FontSize, _('OUT OF(STREAM):'));
		$pdf->SetFont('times', 'B', 12, '', 'false');
		$LeftOvers = $pdf->addTextWrap(353,$YPos-($line_height*15),300,$FontSize, $out_of);
		$pdf->SetFont('times', '', 12, '', 'false');
		$LeftOvers = $pdf->addTextWrap(250,$YPos-($line_height*16),300,$FontSize, _('OUT OF(CLASS):'));
		$pdf->SetFont('times', 'B', 12, '', 'false');
		$LeftOvers = $pdf->addTextWrap(343,$YPos-($line_height*16),300,$FontSize, $total_students);
		$pdf->SetFont('times', '', 12, '', 'false');
		$LeftOvers = $pdf->addTextWrap(230,$YPos-($line_height*17),300,$FontSize, _('AVERAGE CLASS AGE').': ' . $average_standard_age);
	
		$LeftOvers = $pdf->addTextWrap(450,$YPos-($line_height*14),300,$FontSize,strtoupper($myrow2[1]));	
		$LeftOvers = $pdf->addTextWrap(450,$YPos-($line_height*15),300,$FontSize, _('CLOSING').': ' . ConvertSQLDate($myrow2[3]));	
		$LeftOvers = $pdf->addTextWrap(450,$YPos-($line_height*16),200,$FontSize,_('CLASS:'));
		$pdf->SetFont('times', 'B', 12, '', 'false');
		$LeftOvers = $pdf->addTextWrap(500,$YPos-($line_height*16),300,$FontSize,$myrow[3]);
		$pdf->SetFont('times', '', 12, '', 'false');	
		$standard=$myrow[3];
		$YPos +=20;
		$YPos -=$line_height;
		$YPos -=(12*$line_height);
	
		$YPos -=83;
		$YPos3=$YPos;
		$YPos -=$line_height;
		$pdf->line(60, $YPos+$line_height,$Page_Width-$Right_Margin-25, $YPos+$line_height,$style);
	
		$line_width=70;
		$XPos=170;
		$XPos5=100;
		$XPos6=100;
		$YPos2=$YPos;
		$count=0;
		$i=0;
		$bus_report = new bus_report($_POST['student_id'],$_POST['period_id'],$db);
		$YPos -=(5*$line_height);		
		foreach ($bus_report->scheduled_subjects as $a => $b)
		 {
			$count=$count+1;
			$scheduled = new scheduled($b['subject_id'],$db);
			$scheduled->set_calendar_vars($b['id'],$b['subject_id'],$_POST['student_id'],$_POST['period_id'],$_POST['exam_mode'],$db);
			$LeftOvers = $pdf->addTextWrap(70,$YPos+1,300,$FontSize,$scheduled->subject_name);
			$pdf->line(60, $YPos+$line_height,$Page_Width-$Right_Margin-25, $YPos+$line_height,$style);
			$status_array = tep_get_status($_POST['period_id'],$_POST['student_id'],$db);
			$XPos2=200;
			$YPos -=(1*$line_height);
			$LeftOvers = $pdf->addTextWrap($XPos2+50,$YPos+15,300,$FontSize,$scheduled->exam_marks);
			$XPos2 +=40;			
			
			$totalmarks_array =$bus_report->total_marks($b['subject_id'],$_POST['student_id'],$_POST['period_id'],$b['id'],$db);
			if(!isset($scheduled->exam_marks))
			{
				$subjectGrade='';
				$subjectGradeComment='Missed Exam';
			}
			else
			{				
				$sql = "SELECT grade,comment FROM reportcardgrades
				WHERE range_from <=  '". $scheduled->exam_marks ."'
				AND range_to >='". $scheduled->exam_marks ."'
				AND grading LIKE '". $scheduled->grading."'";
				$result=DB_query($sql,$db);
				$myrow=DB_fetch_row($result);
				$subjectGrade=$myrow[0];
				$subjectGradeComment=$myrow[1];
			}
			$LeftOvers = $pdf->addTextWrap($XPos2+40,$YPos+15,300,$FontSize,$subjectGrade);
			$LeftOvers = $pdf->addTextWrap($XPos2+65,$YPos+15,300,$FontSize,$subjectGradeComment);			
			$totalmarks_array2=$totalmarks_array2+$totalmarks_array;					
		}
		$pdf->starttransform();
		$pdf->xy($XPos,332);
		$pdf->rotate(90);
		$LeftOvers = $pdf->addTextWrap($XPos2-93,$YPos2-110,300,$FontSize,_('MARKS(%)'));
		$pdf->stoptransform();
		$pdf->line($XPos2+2,$YPos3,$XPos2+2, $YPos-16,$style);
		$pdf->starttransform();
		$pdf->xy($XPos,332);
		$pdf->rotate(90);
		$LeftOvers = $pdf->addTextWrap($XPos2-93,$YPos2-140,300,$FontSize,_('Grade'));
		$pdf->stoptransform();
		$pdf->line($XPos2+30,$YPos3,$XPos2+30, $YPos-16,$style);	
		$LeftOvers = $pdf->addTextWrap($XPos2+65,$YPos2-40,300,$FontSize,_('Remarks'));
		$pdf->line($XPos2+60,$YPos3,$XPos2+60, $YPos-16,$style);
		$pdf->line(60, $YPos3,60, $YPos-16,$style);
		$pdf->line(540,$YPos3,540, $YPos-16,$style);
		$pdf->line(60, $YPos+$line_height+1,$Page_Width-$Right_Margin-25, $YPos+$line_height+1,$style);
	
		$XPos3=250;
		$total_marks=student_marks_exam_mode($_POST['student_id'],$_POST['period_id'],$_POST['exam_mode'],$db);
		$LeftOvers = $pdf->addTextWrap($XPos3,$YPos+1,300,$FontSize,number_format($total_marks,0));
		
		$LeftOvers = $pdf->addTextWrap(70,$YPos+1,300,$FontSize,_('Total'));
		$pdf->line(60, $YPos-3,$Page_Width-$Right_Margin-25, $YPos-3,$style);
		$LeftOvers = $pdf->addTextWrap(70,$YPos-13,300,$FontSize,_('Class Position'));
		$pdf->line(60, $YPos-16,$Page_Width-$Right_Margin-25, $YPos-16,$style);
		//$LeftOvers = $pdf->addTextWrap($XPos3+15,$YPos+1,300,$FontSize,number_format($marks,0));
		$LeftOvers = $pdf->addTextWrap($XPos3+10,$YPos-15,300,$FontSize,$class_rank);
			
		$sql = "select no_of_subjects,mean,meanScore  from exam_ranks
		WHERE period_id='".$_POST['period_id']."'
		AND student_id='".$_POST['student_id']."'
		AND exam_id='".$_POST['exam_mode']."'";
		$result = DB_query($sql,$db);
		$row = DB_fetch_row($result);
		$noOfSubjects=$row[0];
		$totalPoints=$row[1];
		$meanScore=$row[2];	
		
		$sql = "SELECT grade,comment FROM reportcardgrades
		WHERE title=  '". $meanScore ."'
		AND grading LIKE 'other'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$grade=$myrow[0];
		$comment=$myrow[1];
				
		$YPos -=(2*$line_height);
		$LeftOvers = $pdf->addTextWrap(40,$YPos-10,300,$FontSize,_('Total Subjects').' :'.$noOfSubjects);
		$LeftOvers = $pdf->addTextWrap(150,$YPos-10,300,$FontSize,_('Total Points').' :'.number_format($totalPoints,0));
		
		if($noOfSubjects>0){
		$LeftOvers = $pdf->addTextWrap(350,$YPos-10,300,$FontSize,_('Mean Score').' :
		'.number_format($totalPoints/$noOfSubjects,3));
		}
			
		$sql = "SELECT grade_level_id FROM debtorsmaster 
		WHERE id='".$_POST['student_id']."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$grade_id=$myrow[0];
			
		$LeftOvers = $pdf->addTextWrap(450,$YPos-10,300,$FontSize,_('Mean Grade').' :'.$grade);
		
		
		
		$YPos -=(7*$line_height);
		$FontSize=10;
		$LeftOvers = $pdf->addTextWrap(45,$YPos,300,$FontSize,('Class Teacher'));
		$LeftOvers = $pdf->addTextWrap(130,$YPos,500,$FontSize,'______________________________________________________________________________');
		$YPos -=(1.7*$line_height);
		$LeftOvers = $pdf->addTextWrap(130,$YPos,500,$FontSize,'______________________________________________________________________________    ');
		$YPos -=(1.7*$line_height);
		$LeftOvers = $pdf->addTextWrap(130,$YPos,100,$FontSize,'______________________________________________________________________________');
		$LeftOvers = $pdf->addTextWrap(350,$YPos,300,$FontSize,('Signature'));
		$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,'______________________________________________________________________________');
		
		$YPos -=(1.7*$line_height);
		$LeftOvers = $pdf->addTextWrap(45,$YPos,300,$FontSize,('Report Card seen by Parent/Gurdian'));
		$LeftOvers = $pdf->addTextWrap(195,$YPos,70,$FontSize,'______________________________________________________________________________');
	
		$LeftOvers = $pdf->addTextWrap(350,$YPos,300,$FontSize,('Signature'));
		$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,'______________________________________________________________________________');
		
		
		$pdf->Output('ReportCard-', 'I');
	}	
}
else 
{ /*The option to print PDF was not hit */
	include('includes/session.inc');
	$title = _('Manage Students');
	include('includes/header.inc');
	echo '<p class="page_title_text">' . ' ' . _('View CAT/EXAM Report Card') . '';
	if(isset($_GET['ID']))
	{
		$_POST['student_id']=$_GET['ID'];
	}
	echo '<FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
	echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
	echo '<CENTER><TABLE class="enclosed"><TR><TD>' . _('student:') . '</TD><TD><SELECT Name="student_id">';
	DB_data_seek($result, 0);
	$sql = 'SELECT id,name FROM debtorsmaster';
	$result = DB_query($sql, $db);
	while ($myrow = DB_fetch_array($result)) 
	{
		if ($myrow['id'] == $_POST['student_id'])
		{  
			echo '<OPTION SELECTED VALUE=';
		} 
		else
		{
			echo '<OPTION VALUE=';
		}
		echo $myrow['id'] . '>' . $myrow['name'];
	} //end while loop
	echo '</SELECT></TD></TR>';
	echo '<CENTER><TR><TD>' . _('Period:') . '</TD><TD><SELECT Name="period_id">';
	DB_data_seek($result, 0);
	$sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
	INNER JOIN terms ON terms.id=cp.term_id
	INNER JOIN years ON years.id=cp.year ";
	$result=DB_query($sql,$db);
	while ($myrow = DB_fetch_array($result)) 
	{
		if ($myrow['id'] == $_POST['id']) 
		{  
		echo '<OPTION SELECTED VALUE=';
		} 
		else 
		{
		 echo '<OPTION VALUE=';
		}
		echo $myrow['id'] . '>'.' '.$myrow['title'].' '.$myrow['year'];
	} //end while loop
	echo '</SELECT></TD></TR>';
	echo '<CENTER><TR><TD class="visible">' . _('Exam Mode:') . '</TD><TD class="visible"><SELECT Name="exam_mode">';
		DB_data_seek($result, 0);
		$sql="SELECT * FROM markingperiods ";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) 
		{
			if ($myrow['id'] == $_POST['id'])
			 {  
				echo '<OPTION SELECTED VALUE=';
			} 
			else 
			{
				echo '<OPTION VALUE=';
			}
			echo $myrow['id'] . '>'.' '.$myrow['title'];
		} //end while loop
	echo '</SELECT></TD></TR>';		
	echo "</TABLE>";
	echo "<P><CENTER><INPUT TYPE='Submit' NAME='PrintPDF' VALUE='" . _('View') . "'>";
	include('includes/footer.inc');
} /*end of else not PrintPDF */
?>