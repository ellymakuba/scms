<?php

$PageSecurity = 2;
if(isset($_POST['period_id'])  && isset($_POST['class_id']) && isset($_POST['PrintPDF'])){
include('includes/session.inc');
include('includes/PDFStarter.php');
require('grades/EndTermReportClass.php');
include("Numbers/Words.php");
$FontSize=13;
$_SESSION['student'] = $myrowclass['student_id'];
$_SESSION['period'] = $_POST['period_id'];
$PageNumber=1;
$line_height=12;
$reportcard=1;
$sqlclass = "SELECT DISTINCT(rs.student_id) FROM registered_students rs
INNER JOIN termly_student_ranks tsr ON tsr.student_id=rs.student_id
 WHERE rs.class_id='" .$_POST['class_id']. "'
 AND rs.period_id='" .$_POST['period_id']. "'
 AND tsr.period_id='" .$_POST['period_id']. "'
 AND tsr.class_id='" .$_POST['class_id']. "'
 ORDER BY tsr.rank";
$resultclass = DB_query($sqlclass,$db);
if(DB_num_rows($resultclass)>0)
{
	while ($myrowclass=DB_fetch_array($resultclass))
	{
		$FontSize=18;
		$YPos= $Page_Height-$Top_Margin;
		$XPos=0;
		$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$XPos+260,$YPos-120,0,100);
		$YPos-=(2*$line_height);
		$pdf->SetFont('times', '', 18, '', 'false');
		$LeftOvers = $pdf->addTextWrap(100,$YPos-($line_height*10),400,$FontSize,
		strtoupper($_SESSION['CompanyRecord']['coyname']));
		$FontSize=12;
		$LeftOvers = $pdf->addTextWrap(180,$YPos-($line_height*11),400,$FontSize,
		$_SESSION['CompanyRecord']['regoffice3'].' - '.$_SESSION['CompanyRecord']['regoffice5'].' - '
		.('TEL :').' '.
		$_SESSION['CompanyRecord']['regoffice4']);
		$FontSize=10;
		$LeftOvers = $pdf->addTextWrap(240,$YPos-($line_height*12),300,$FontSize,_('EMAIL :').
		' '.$_SESSION['CompanyRecord']['email']);
		$YPos-=(2*$line_height);
		$pdf->SetFont('times', '', 12, '', 'false');
		$FontSize=10;
		$style = array('width' => 0.70, 'cap' => 'butt','join' =>'miter','dash' => 0,'phase' =>10, 'color' => array(12, 12, 12));
		$sql = "SELECT rank,class_id,mean FROM termly_student_ranks
		WHERE period_id =  '". $_POST['period_id'] ."'
		AND student_id='". $myrowclass['student_id'] ."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$rank=$myrow[0];
		$class_id=$myrow[1];
		$marks=$myrow[2];

		$sql = "SELECT class_rank,class_id FROM termly_class_ranks
		WHERE period_id =  '". $_POST['period_id'] ."'
		AND student_id='". $myrowclass['student_id'] ."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$class_rank=$myrow[0];
		$class=$myrow[1];

		$sql = "SELECT COUNT(*) FROM termly_class_ranks
		WHERE period_id =  '". $_POST['period_id'] ."'
		AND class_id='$class'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$total_students=$myrow[0];

		$sql = "SELECT COUNT(*) FROM termly_student_ranks
		WHERE period_id =  '". $_POST['period_id'] ."'
		AND class_id='$class_id'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$out_of=$myrow[0];

		$sql = "SELECT DISTINCT(rs.student_id),dtr.name,dtr.debtorno,gl.grade_level,dtr.grade_level_id,
		dtr.age,gl.id,dtr.balance
		FROM registered_students rs
		INNER JOIN debtorsmaster dtr ON dtr.id=rs.student_id
		INNER JOIN classes cl ON cl.id=rs.class_id
		INNER JOIN gradelevels gl ON gl.id=cl.grade_level_id
		WHERE dtr.id =  '". $myrowclass['student_id'] ."'
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

		$LeftOvers = $pdf->addTextWrap(240,$YPos-($line_height*12),400,$FontSize,_('END TERM REPORT CARD'));
		 $LeftOvers = $pdf->addTextWrap(239,$YPos-($line_height*12.3),75,$FontSize,
		 '______________________________________________________________________________');
		$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*14),300,$FontSize,_('NAME :'));
		$pdf->SetFont('times', 'B', 12, '', 'false');
		$LeftOvers = $pdf->addTextWrap(82,$YPos-($line_height*14),300,$FontSize,$myrow[1]);
		$pdf->SetFont('times', '', 12, '', 'false');
		$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*15),300,$FontSize,_('STREAM POSITION:'));
		$pdf->SetFont('times', 'B', 12, '', 'false');
		$LeftOvers = $pdf->addTextWrap(150,$YPos-($line_height*15),300,$FontSize,$rank);
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

		$YPos -=85;
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
		$bus_report = new bus_report($myrowclass['student_id'],$_POST['period_id'],$db);
		$status_array = tep_get_status($_POST['period_id'],$myrowclass['student_id'],$db);
		foreach ($status_array as $r => $s)
		{
			$pdf->starttransform();
			$pdf->xy($XPos,332);
			$pdf->rotate(90);
			$LeftOvers = $pdf->addTextWrap($XPos-25,$YPos-70,300,$FontSize,$s['title']);
			$pdf->stoptransform();
			$XPos +=40;
		}
		$YPos -=(5*$line_height);
		foreach ($bus_report->scheduled_subjects as $a => $b)
		 {
			//$count=$count+1;
			$sql = "select no_of_subjects from termly_class_ranks
			WHERE student_id='".$myrowclass['student_id']."'
			AND period_id='".$_POST['period_id']."'";
			$result = DB_query($sql,$db);
			$row = DB_fetch_row($result);
			$count=$row[0];

			$scheduled = new scheduled($b['subject_id'],$db);
			$scheduled->set_calendar_vars($b['id'],$b['subject_id'],$myrowclass['student_id'],$_POST['period_id'],$db);
			$LeftOvers = $pdf->addTextWrap(70,$YPos+1,300,$FontSize,$scheduled->subject_name);
			$pdf->line(60, $YPos+$line_height,$Page_Width-$Right_Margin-25, $YPos+$line_height,$style);
			$status_array = tep_get_status($_POST['period_id'],$myrowclass['student_id'],$db);
			$XPos2=200;
			$YPos -=(1*$line_height);
			foreach ($scheduled->status as $y=>$z)
			{
				$i++;
				$LeftOvers = $pdf->addTextWrap($XPos2+10,$YPos+15,300,$FontSize,$z['marks']);
				$pdf->line($XPos2,$YPos3,$XPos2, $YPos-16,$style);
				$XPos2 +=40;
			}
			$cat_marks =$bus_report->average_cat_marks($b['subject_id'],$myrowclass['student_id'],$_POST['period_id'],$b['id'],$db);
			$totalmarks_array =$bus_report->total_marks($b['subject_id'],$myrowclass['student_id'],$_POST['period_id'],$b['id'],$db);
			$sql = "SELECT grade,comment FROM reportcardgrades
			WHERE range_from <=  '". $totalmarks_array ."'
			AND range_to >='". $totalmarks_array ."'
			AND grading LIKE '". $scheduled->grading."'";
			$result=DB_query($sql,$db);
			$myrow=DB_fetch_row($result);
			$LeftOvers = $pdf->addTextWrap($XPos2+10,$YPos+15,300,$FontSize,number_format($cat_marks,0));
			$LeftOvers = $pdf->addTextWrap($XPos2+40,$YPos+15,300,$FontSize,$totalmarks_array);
			$LeftOvers = $pdf->addTextWrap($XPos2+62,$YPos+15,300,$FontSize,$myrow[0]);
			$LeftOvers = $pdf->addTextWrap($XPos2+81,$YPos+15,300,$FontSize,$myrow[1]);
			$totalmarks_array2=$totalmarks_array2+$totalmarks_array;
		}
		$pdf->starttransform();
		$pdf->xy($XPos,332);
		$pdf->rotate(90);
		$LeftOvers = $pdf->addTextWrap($XPos2-54,$YPos2-70,300,$FontSize,_('CAT(AVG)'));
		$LeftOvers = $pdf->addTextWrap($XPos2-54,$YPos2-100,300,$FontSize,_('TOTAL(%)'));
		$pdf->stoptransform();
		$pdf->line($XPos2+2,$YPos3,$XPos2+2, $YPos-16,$style);
		$pdf->line($XPos2+80,$YPos3,$XPos2+80, $YPos-16,$style);
		$pdf->starttransform();
		$pdf->xy($XPos,332);
		$pdf->rotate(90);
		$LeftOvers = $pdf->addTextWrap($XPos2-54,$YPos2-124,300,$FontSize,_('Grade'));
		$pdf->stoptransform();
		$pdf->line($XPos2+30,$YPos3,$XPos2+30, $YPos-16,$style);
		$LeftOvers = $pdf->addTextWrap($XPos2+85,$YPos2-40,300,$FontSize,_('Remarks'));
		$pdf->line($XPos2+60,$YPos3,$XPos2+60, $YPos-16,$style);
		$pdf->line(60, $YPos3,60, $YPos-16,$style);
		$pdf->line(540,$YPos3,540, $YPos-16,$style);
		$pdf->line(60, $YPos+$line_height+1,$Page_Width-$Right_Margin-25, $YPos+$line_height+1,$style);

		$XPos3=220;
		foreach ($status_array as $r => $s)
		{
			$total_marks=mode_marks($myrowclass['student_id'],$_POST['period_id'],$s['id'],$db);
			$position=get_student_position($myrowclass['student_id'],$_POST['period_id'],$s['id'],$db);
			$LeftOvers = $pdf->addTextWrap($XPos3-10,$YPos+1,300,$FontSize,$total_marks);
			$LeftOvers = $pdf->addTextWrap($XPos3-10,$YPos-15,300,$FontSize,number_format($position,0));

			$XPos3 +=40;

		}//end of ssubjects array foreach
		$LeftOvers = $pdf->addTextWrap(70,$YPos+1,300,$FontSize,_('Total'));
		$pdf->line(60, $YPos-3,$Page_Width-$Right_Margin-25, $YPos-3,$style);
		$LeftOvers = $pdf->addTextWrap(70,$YPos-13,300,$FontSize,_('Class Position'));
		$pdf->line(60, $YPos-16,$Page_Width-$Right_Margin-25, $YPos-16,$style);
		$LeftOvers = $pdf->addTextWrap($XPos3+45,$YPos+1,300,$FontSize,number_format($marks,0));
		$LeftOvers = $pdf->addTextWrap($XPos3+15,$YPos-15,300,$FontSize,$class_rank);

		$YPos -=(2*$line_height);
		$LeftOvers = $pdf->addTextWrap(40,$YPos-10,300,$FontSize,_('Total Subjects').' :'.$count);
		$LeftOvers = $pdf->addTextWrap(150,$YPos-10,300,$FontSize,_('Total Points').' :'.number_format($marks,0));
		$LeftOvers = $pdf->addTextWrap(300,$YPos-10,300,$FontSize,_('Mean').' :'.number_format($marks/$count,3));
		$out_of=100*$count;

		//$LeftOvers = $pdf->addTextWrap(350,$YPos-10,300,$FontSize,_('Out of').' :'.$out_of);
		$sql = "SELECT meanScore FROM termly_class_ranks
		WHERE student_id='".$myrowclass['student_id']."'
		AND period_id='".$_POST['period_id']."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$mean_grade=$myrow[0];

		$sql = "SELECT grade,comment FROM reportcardgrades
		WHERE title =  '". $mean_grade ."'
		AND grading LIKE 'other'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$grade=$myrow[0];
		$comment=$myrow[1];

		$sql = "SELECT grade_level_id FROM debtorsmaster
		WHERE id='".$myrowclass['student_id']."'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		$grade_id=$myrow[0];

		$LeftOvers = $pdf->addTextWrap(450,$YPos-10,300,$FontSize,_('Mean Grade').' :'.$grade);
		$YPos -=(1.5*$line_height);
		$LeftOvers = $pdf->addTextWrap(250,$YPos-10,300,$FontSize,_('Student Tracking Record'));
		$LeftOvers = $pdf->addTextWrap(250,$YPos-11,60,$FontSize,'______________________________________________________________________________');
		$FontSize=8;
		$YPos -=(2.5*$line_height);
		$pdf->line($Left_Margin, $YPos+$line_height,$Page_Width-$Right_Margin, $YPos+$line_height,$style);
		$YPos3=$YPos;
		$pdf->line($Left_Margin,$YPos-70,$Left_Margin, $YPos+12,$style);
		$pdf->line(565,$YPos-70,565, $YPos+12,$style);
		$LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos+1,300,$FontSize,_('CLASSES'));
		$LeftOvers = $pdf->addTextWrap($Left_Margin+5,$YPos-10,300,$FontSize,_('TERMS'));
		$forms_array=get_forms($db);
		$terms_array=get_form_terms($db);
		$nw = new Numbers_Words();
		foreach ($forms_array as $farr => $form)
		{
			$LeftOvers = $pdf->addTextWrap($XPos5+45,$YPos+1,300,$FontSize,$form['grade_level']);
			$pdf->line($XPos5+35,$YPos-70,$XPos5+35, $YPos+12,$style);
			foreach ($terms_array as $tarr => $term)
			{
				$sql = "SELECT tcr.mean,tcr.class_id,tcr.class_rank,cm.mean,cp.id,tcr.meanscore FROM termly_class_ranks tcr
				INNER JOIN collegeperiods cp ON cp.id=tcr.period_id
				INNER JOIN class_means cm ON cm.class=tcr.class_id
				WHERE student_id = '". $myrowclass['student_id']."'
				AND cp.term_id='". $term['id']."'
				AND tcr.class_id ='". $form['id']."'";
				$result=DB_query($sql,$db);
				$myrow=DB_fetch_row($result);
				$tracking_mean_grade=$myrow[5];
				$classid=$myrow[1];
				$classrank=$myrow[2];
				$classmean=$myrow[3];
				$periodic=$myrow[4];

				$sql = "SELECT grade,comment FROM reportcardgrades
				WHERE title=  '". $tracking_mean_grade ."'
				AND grading LIKE 'other'";
				$result=DB_query($sql,$db);
				$myrow=DB_fetch_row($result);
				$tracking_grade=$myrow[0];

				$sql = "SELECT COUNT(*),SUM(meanScore) FROM termly_class_ranks tcr,collegeperiods cp
				WHERE cp.term_id='". $term['id']."'
				AND tcr.class_id='$classid'
				AND cp.id=tcr.period_id
				AND cp.year=tcr.academic_year
				AND tcr.period_id='$periodic'";
				$result=DB_query($sql,$db);
				$myrow=DB_fetch_row($result);
				$class_count=$myrow[0];
				$classmean=$myrow[1];
				if($class_count>0){
				$classmean=$classmean/$class_count;
				}
				else{
				$class_count=0;
				}
				$classmean=number_format($classmean,0);
				if($classmean >0)
				{
					$sql = "SELECT rg.grade FROM reportcardgrades rg,termly_class_ranks tcr,collegeperiods cp
					WHERE title =  '".$classmean."'
					AND grading LIKE 'other'
					AND cp.term_id='". $term['id']."'
					AND tcr.class_id ='". $form['id']."'";
					$result=DB_query($sql,$db);
					$myrow=DB_fetch_row($result);
					$class_mean_grade=$myrow[0];
				}
				else
				{
					$class_mean_grade='';
				}

				$LeftOvers = $pdf->addTextWrap($XPos6+37,$YPos-10,200,$FontSize,$term['id']);
				$LeftOvers = $pdf->addTextWrap($XPos6+35,$YPos-18,200,$FontSize,$tracking_grade);
				$LeftOvers = $pdf->addTextWrap($XPos6+35,$YPos-28,200,$FontSize,number_format($tracking_mean_grade,3));
				$LeftOvers = $pdf->addTextWrap($XPos6+37,$YPos-37,200,$FontSize,$classrank);
				$LeftOvers = $pdf->addTextWrap($XPos6+37,$YPos-50,200,$FontSize,$class_count);
				$LeftOvers = $pdf->addTextWrap($XPos6+37,$YPos-58,200,$FontSize,number_format($classmean,0));
				$LeftOvers = $pdf->addTextWrap($XPos6+37,$YPos-68,200,$FontSize,$class_mean_grade);
				$pdf->line($XPos6+35,$YPos-70,$XPos6+35, $YPos,$style);
				$XPos6 +=35;
			}//end of foreach ($terms_array as $tarr => $term)
			$XPos5 +=(1.5*$line_width);
			$XPos6=$XPos5;
		}
		$pdf->line($Left_Margin, $YPos-10,$Page_Width-$Right_Margin, $YPos-10,$style);
		$LeftOvers = $pdf->addTextWrap(45,$YPos-18,200,$FontSize,('Student Mean Grade'));
		$pdf->line($Left_Margin, $YPos-20,$Page_Width-$Right_Margin, $YPos-20,$style);
		$LeftOvers = $pdf->addTextWrap(45,$YPos-28,300,$FontSize,('Total Points'));
		$pdf->line($Left_Margin, $YPos-30,$Page_Width-$Right_Margin, $YPos-30,$style);
		$LeftOvers = $pdf->addTextWrap(45,$YPos-37,300,$FontSize,('Student Class Position'));
		$pdf->line($Left_Margin, $YPos-40,$Page_Width-$Right_Margin, $YPos-40,$style);
		$LeftOvers = $pdf->addTextWrap(45,$YPos-48,300,$FontSize,('Out Of'));
		$pdf->line($Left_Margin, $YPos-50,$Page_Width-$Right_Margin, $YPos-50,$style);
		$LeftOvers = $pdf->addTextWrap(45,$YPos-58,300,$FontSize,('Class Mean Mark'));
		$pdf->line($Left_Margin, $YPos-60,$Page_Width-$Right_Margin, $YPos-60,$style);
		$LeftOvers = $pdf->addTextWrap(45,$YPos-68,300,$FontSize,('Class Mean Grade'));
		$pdf->line($Left_Margin, $YPos-70,$Page_Width-$Right_Margin, $YPos-70,$style);
		$pdf->line($Left_Margin, $YPos,$Page_Width-$Right_Margin, $YPos,$style);

		$sql2="SELECT start_marks_posting_date,end_marks_posting_date,opening_hour,b_opening_hour FROM collegeperiods
		WHERE id='".$_POST['period_id']."'";
		$result2=DB_query($sql2,$db);
		$myrow2=DB_fetch_array($result2);
		$next_opening=$myrow2['start_marks_posting_date'];
		$next_hour=$myrow2['opening_hour'];
		$b_next_hour=$myrow2['b_opening_hour'];
		$borders_next_opening=$myrow2['end_marks_posting_date'];

		$YPos -=(7*$line_height);
		$FontSize=10;
		$LeftOvers = $pdf->addTextWrap(45,$YPos,300,$FontSize,('Class Teacher'));
		$LeftOvers = $pdf->addTextWrap(128,$YPos,500,$FontSize,$comment);
		$LeftOvers = $pdf->addTextWrap(130,$YPos,500,$FontSize,'______________________________________________________________________________');
		$YPos -=(1.7*$line_height);
		$LeftOvers = $pdf->addTextWrap(130,$YPos,100,$FontSize,'______________________________________________________________________________');
		$LeftOvers = $pdf->addTextWrap(350,$YPos,300,$FontSize,('Signature'));
		$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,'______________________________________________________________________________');
		$YPos -=(1.7*$line_height);
		$LeftOvers = $pdf->addTextWrap(45,$YPos,300,$FontSize,('Head Teacher'));
		$LeftOvers = $pdf->addTextWrap(128,$YPos,500,$FontSize,$comment);
		$LeftOvers = $pdf->addTextWrap(130,$YPos,500,$FontSize,'______________________________________________________________________________');
		$YPos -=(1.7*$line_height);
		$LeftOvers = $pdf->addTextWrap(130,$YPos,100,$FontSize,'______________________________________________________________________________');
		$LeftOvers = $pdf->addTextWrap(350,$YPos,300,$FontSize,('Signature'));
		$LeftOvers = $pdf->addTextWrap(400,$YPos,60,$FontSize,'______________________________________________________________________________');
		$YPos -=(1.7*$line_height);
		$LeftOvers = $pdf->addTextWrap(45,$YPos,300,$FontSize,(' Guardian Signature'));
		$LeftOvers = $pdf->addTextWrap(130,$YPos,100,$FontSize,'______________________________________________________________________________');
		$YPos -=(1.7*$line_height);
		$LeftOvers = $pdf->addTextWrap(45,$YPos,300,$FontSize,('Fee Balance'));
		$LeftOvers = $pdf->addTextWrap(110,$YPos,50,$FontSize,$feebalance);
		$LeftOvers = $pdf->addTextWrap(100,$YPos,50,$FontSize,'______________________________________________________________________________');

		$LeftOvers = $pdf->addTextWrap(330,$YPos,300,$FontSize,('Next Term Begins on :'));
		$LeftOvers = $pdf->addTextWrap(420,$YPos,50,$FontSize,'______________________________________________________________________________');
		if($standard <5)
		{
		$LeftOvers = $pdf->addTextWrap(425,$YPos+1,300,$FontSize,ConvertSQLDate($next_opening).'  '.$next_hour);
		}
		else
		{
		$LeftOvers = $pdf->addTextWrap(425,$YPos+1,300,$FontSize,ConvertSQLDate($borders_next_opening).'  '.$b_next_hour);
		}
		$PageNumber++;
	if ($PageNumber>1){
	$pdf->newPage();
		}

	}
}
$pdf->Output('ReportCard-', 'I');
}
else { /*The option to print PDF was not hit */
include('includes/session.inc');
$title = _('Stream End Term Report Cards');
include('includes/header.inc');
echo '<p class="page_title_text">' . ' ' . $title. '';
echo '<FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<br><CENTER><TABLE class="enclosed"><TR><TD>' . _('Stream:') . '</TD><TD><SELECT Name="class_id">';
		DB_data_seek($result, 0);
		$sql = 'SELECT id,class_name FROM classes ORDER BY class_name';
		$result = DB_query($sql, $db);
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['id'] == $_POST['class_id']) {
				echo '<OPTION SELECTED VALUE=';
			} else {
				echo '<OPTION VALUE=';
			}
			echo $myrow['id'] . '>' . $myrow['class_name'];
		} //end while loop
	echo '</SELECT></TD></TR>';
echo '<TR><TD>' . _('Period:') . '</TD><TD><SELECT Name="period_id">';
		DB_data_seek($result, 0);
		$sql="SELECT cp.id,terms.title,years.year FROM collegeperiods cp
		INNER JOIN terms ON terms.id=cp.term_id
		INNER JOIN years ON years.id=cp.year
		ORDER BY cp.id DESC";
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
	echo "<P><INPUT TYPE='Submit' NAME='PrintPDF' VALUE='" . _('PrintPDF') . "'>";


	include('includes/footer.inc');
} /*end of else not PrintPDF */

?>
