<?php
$PageSecurity = 2;
if(isset($_POST['period_id']))
{
	include('includes/session.inc');
	include('grades/ClassSubjectMean.php');
	require('grades/LecturerSubjectClass.php');
	include('grades/OveralStreamReportGenerate.php');
	include('grades/OveralClassReportGenerate.php');
	$FontSize=13;
	
	$sql2 = "SELECT y.run FROM years y
	INNER JOIN collegeperiods cp ON cp.year=y.id
	WHERE cp.id='".$_POST['period_id']."'";
	$result2 = DB_query($sql2,$db);
	$myrow2 = DB_fetch_array($result2);
	$run=$myrow2['run'];
	if($run==1)
	{
		prnMsg(_('This academic year has already been compiled, Unroll first'),'warn'); 
		exit("");
	}	

    $_SESSION['period'] = $_POST['period_id'];
    $sql="SELECT year FROM collegeperiods WHERE id='".$_POST['period_id']."'";
	$result=DB_query($sql, $db);
	$myrow=DB_fetch_array($result);
	$_SESSION['year']=$myrow['year'];		

	$sql="DELETE FROM subject_mean WHERE period_id ='" . $_POST['period_id'] . "'
	AND rolled=0 AND (grade_level_id='" . $_POST['class_id'] . "' OR grade_level_id=0)";
	$Postdelptrans= DB_query($sql,$db);
	
	$sql="DELETE FROM exam_ranks WHERE period_id ='" . $_POST['period_id'] . "'
	AND rolled=0 AND (class_id='" . $_POST['class_id'] . "' OR class_id=0)";
	$Postdelptrans= DB_query($sql,$db);

	$sql="DELETE FROM class_means WHERE period_id ='" . $_POST['period_id'] . "'
	AND rolled=0 AND (class='" . $_POST['class_id'] . "' OR class=0)";
	$Postdelptrans= DB_query($sql,$db);
	
	$sql="DELETE FROM class_subject_mean WHERE period_id ='" . $_POST['period_id'] . "'
	AND rolled=0 AND (class='" . $_POST['class_id'] . "' OR class=0)";
	$Postdelptrans= DB_query($sql,$db);
	
	$sql="DELETE FROM termly_student_ranks WHERE period_id ='" . $_POST['period_id'] . "'
	AND rolled=0 AND (grade_level_id='" . $_POST['class_id'] . "' OR grade_level_id=0)";
	$Postdelptrans= DB_query($sql,$db);
	
	$sql="DELETE FROM termly_class_ranks WHERE period_id ='" . $_POST['period_id'] . "'
	AND rolled=0 AND class_id='" . $_POST['class_id'] . "'";
	$Postdelptrans= DB_query($sql,$db);
	
	$sql="DELETE FROM students_subject_marks WHERE period_id ='" . $_POST['period_id'] . "'
	AND rolled=0 AND (class_id='" . $_POST['class_id'] . "' OR class_id=0)";
	$Postdelptrans= DB_query($sql,$db);

	function studentsRegisteredForSubject($subject,$period,$class,$db)
	{	
		$sql="SELECT COUNT(*) FROM registered_students WHERE subject_id='$subject' AND period_id='$period' AND class_id='$class'";
		$result=DB_query($sql,$db);
		$myrow=DB_fetch_row($result);
		return $myrow[0];
	}
	function studentTakesSubject($student,$subject,$period,$db){
	$studentTakesThisSubject=0;
	$sql="SELECT student_id FROM registered_students WHERE subject_id='$subject' AND period_id='$period' AND student_id='$student'";
	$result=DB_query($sql,$db);
	$num=DB_fetch_row($result);
	if($num[0] >0){
	$studentTakesThisSubject=1;
	}
	
	return $studentTakesThisSubject;
	}
	
	$sqlclass = "SELECT * FROM gradelevels WHERE id='" . $_POST['class_id'] . "'";
	$resultclass = DB_query($sqlclass,$db);	
	while ($myrowclass= DB_fetch_array($resultclass))
	{
		$class_mean=0;
		$total_class_mean=0;
		$counted=0;		
		if($myrowclass['lower']==0)
		{
		$bus_report = new bus_report($myrowclass['id'],$_POST['period_id'],$db);
		$rank =0;		
			if($bus_report->scheduled_students>0)
			{
				foreach ($bus_report->scheduled_students as $sa => $st) 
				{
				$total=0;
				$rank=$rank+1;
				$student_total=0;
				$student_total2=0;
				$total_subject_points=0;
				$subjects_taken_by_student=0;
				$subject_add=0;
				$language_marks = array();
				$science_marks = array();
				$mathematics_marks = array();
				$technical_marks = array();
				$humanity_marks = array();
				$mean=0;
				$scheduled = new scheduled($st['student_id'],$db);
				$scheduled->set_calendar_vars($myrowclass['id'],$st['student_id'],$_POST['period_id'],$st['id'],$db);
				$subject_meangrade_array=0;
				   foreach ($scheduled->subject as $y=>$z) 
					{
					
						if($z['department_id']==1)
						{
						  $engdip=$z['department_id'];
						  array_push($mathematics_marks, $z['tmarks']);
						  if(sizeof($mathematics_marks)<2)
						  {
							$subjects_taken_by_student=$subjects_taken_by_student+1;
						  }
						}
						else if($z['department_id']==2)
						{
						
						  array_push($language_marks, $z['tmarks']);
						   if(sizeof($language_marks)<3)
						  {
							$subjects_taken_by_student=$subjects_taken_by_student+1;
						  }	
						}
						else if($z['department_id']==3){
						  array_push($humanity_marks, $z['tmarks']);
						  if(sizeof($humanity_marks)<2)
						  {
							$subjects_taken_by_student=$subjects_taken_by_student+1;
						  }
						}
						else if($z['department_id']==4){
						  array_push($science_marks, $z['tmarks']);
						 if(sizeof($science_marks)<3)
						  {
							$subjects_taken_by_student=$subjects_taken_by_student+1;
						  }
						}
						else if($z['department_id']==5){
						 array_push($technical_marks, $z['tmarks']);
						 if(sizeof($technical_marks)<2)
						  {
							$subjects_taken_by_student=$subjects_taken_by_student+1;
						  }	
						}
					
						//$student_total2=$student_total2+$z['tmarks'];	
						$sqlroll = "SELECT an.rolled,dm.name FROM students_subject_marks an
						INNER JOIN debtorsmaster dm ON dm.id=an.student_id
						WHERE an.student_id='".$st['student_id']."'
						AND an.period_id='".$_POST['period_id']."'
						AND rolled=1";
						$resultroll = DB_query($sqlroll,$db);	
							if(DB_num_rows($resultroll) >0)
							{
							$myrowroll= DB_fetch_array($resultroll);
							$rolled=$myrowroll['rolled'];
							$name=$myrowroll['name'];
							}
							else
							{
							$sql = "INSERT INTO students_subject_marks (student_id,subject_id,period_id,
							marks,academic_year,class_id)
							VALUES ('" . $st['student_id'] ."','" .$z['id'] ."','" .$_POST['period_id'] ."','" .$z['tmarks'] .
							"','".$_SESSION['year']."','".$myrowclass['id']."')";
							$result = DB_query($sql,$db);
							}				
					}//end of scheduled subject
				//$subjects_taken_by_student=students_subjects_class($st['student_id'],$_POST['period_id'],$db);
				$sqlroll = "SELECT an.rolled,dm.name FROM termly_class_ranks an
				INNER JOIN debtorsmaster dm ON dm.id=an.student_id
				WHERE an.student_id='".$st['student_id']."'
				AND an.period_id='".$_POST['period_id']."'
				AND rolled=1";
				$resultroll = DB_query($sqlroll,$db);	
			
				if(DB_num_rows($resultroll) >0)
				{
					$myrowroll= DB_fetch_array($resultroll);
					$rolled=$myrowroll['rolled'];
					$name=$myrowroll['name'];
				}
				else
				{
					rsort($language_marks);
					$language_size=sizeof($language_marks);
					$total_language_marks=0;			
					for($i=0; $i<$language_size; $i++)
					{
					  if($i<2)
					  {	
						$total_language_marks=$total_language_marks+$language_marks[$i];
						
						$sqlmean = "SELECT title FROM reportcardgrades
						WHERE range_from <=  '". $language_marks[$i] ."'
						AND range_to >='". $language_marks[$i] ."'
						AND grading LIKE 'KISWAHILI'";
						$resultmean=DB_query($sqlmean,$db);
						$myrowmean=DB_fetch_row($resultmean);
						$points=$myrowmean[0];
						$total_subject_points=$total_subject_points+$points;
					  }			   		
					}
			
					rsort($mathematics_marks);
					$mathematics_size=sizeof($mathematics_marks);
					$total_mathematics_marks=0;			
					for($i=0; $i<$mathematics_size; $i++)
					{
					  if($i<1)
					  {	
						$total_mathematics_marks=$total_mathematics_marks+$mathematics_marks[$i];
						
						$sqlmean = "SELECT title FROM reportcardgrades
						WHERE range_from <=  '". $mathematics_marks[$i] ."'
						AND range_to >='". $mathematics_marks[$i] ."'
						AND grading LIKE 'MATHS'";
						$resultmean=DB_query($sqlmean,$db);
						$myrowmean=DB_fetch_row($resultmean);
						$points=$myrowmean[0];
						$total_subject_points=$total_subject_points+$points;
					  }			   		
					}
				
					rsort($humanity_marks);
					$humanity_size=sizeof($humanity_marks);
					$total_humanity_marks=0;			
					for($i=0; $i<$humanity_size; $i++)
					{
					  if($i<1)
					  {	
						$total_humanity_marks=$total_humanity_marks+$humanity_marks[$i];
						
						$sqlmean = "SELECT title FROM reportcardgrades
						WHERE range_from <=  '". $humanity_marks[$i] ."'
						AND range_to >='". $humanity_marks[$i] ."'
						AND grading LIKE 'OTHER'";
						$resultmean=DB_query($sqlmean,$db);
						$myrowmean=DB_fetch_row($resultmean);
						$points=$myrowmean[0];
						$total_subject_points=$total_subject_points+$points;
					  }			   		
					}	
				
					rsort($science_marks);
					$science_size=sizeof($science_marks);
					$total_science_marks=0;			
					for($i=0; $i<$science_size; $i++)
					{
					  if($i<2)
					  {	
						$total_science_marks=$total_science_marks+$science_marks[$i];
						
						$sqlmean = "SELECT title FROM reportcardgrades
						WHERE range_from <=  '". $science_marks[$i] ."'
						AND range_to >='". $science_marks[$i] ."'
						AND grading LIKE 'SCIENCE'";
						$resultmean=DB_query($sqlmean,$db);
						$myrowmean=DB_fetch_row($resultmean);
						$points=$myrowmean[0];
						$total_subject_points=$total_subject_points+$points;
					  }			   		
					}	
					
					rsort($technical_marks);
					$technical_size=sizeof($technical_marks);
					$total_technical_marks=0;			
					for($i=0; $i<$technical_size; $i++)
					{
					  if($i<1)
					  {	
						$total_technical_marks=$total_technical_marks+$technical_marks[$i];
						
						$sqlmean = "SELECT title FROM reportcardgrades
						WHERE range_from <=  '". $technical_marks[$i] ."'
						AND range_to >='". $technical_marks[$i] ."'
						AND grading LIKE 'OTHER'";
						$resultmean=DB_query($sqlmean,$db);
						$myrowmean=DB_fetch_row($resultmean);
						$points=$myrowmean[0];
						$total_subject_points=$total_subject_points+$points;
					  }			   		
					}// end of for($i=0; $i<$technical_size; $i++)
				   $student_total2=$total_language_marks+$total_mathematics_marks+$total_humanity_marks
				   +$total_science_marks+$total_technical_marks;
					if($subjects_taken_by_student==0)
					{
					 $subjects_taken_by_student=1;
					}
					//$mean=$student_total2/$subjects_taken_by_student;
					$mean=$total_subject_points;
					$meanScore=number_format($mean/7,0);
					$sql = "INSERT INTO termly_class_ranks (student_id,period_id,class_id,academic_year,marks,mean,no_of_subjects,
					meanScore)
					VALUES ('" . $st['student_id'] ."','" .$_POST['period_id'] ."','" .$myrowclass['id'] ."',
					'".$_SESSION['year']."',
					'$student_total2','$mean',7,'$meanScore')";
					$result = DB_query($sql,$db);
					$msg = _('Student ranks generated successfuly');	
				
					$ranked=0;
					$rank_ties=0;					
					$sqlrank = "SELECT mean,student_id FROM termly_class_ranks 
					WHERE class_id='".$myrowclass['id']."'
					AND period_id='".$_POST['period_id']."'
					ORDER BY mean DESC";
					$resultrank = DB_query($sqlrank,$db);
					$previous_marks='';	
					while ($myrowrank= DB_fetch_array($resultrank))
					{
						if ($myrowrank['mean'] == $previous_marks) 
						{
						 $ranked=$ranked;
						 $rank_ties=$rank_ties+1;
						}
						else
						{
							$ranked=$rank_ties+$ranked+1;
							$rank_ties=0;
						}
						$stude=$myrowrank['student_id'];
						$sqlgenerate = "UPDATE termly_class_ranks
						SET class_rank='" . $ranked . "'
						WHERE student_id='".$stude."'
						AND period_id='".$_POST['period_id']."'
						AND class_id='" .$myrowclass['id'] ."'";
						$generate = DB_query($sqlgenerate,$db);	
						$previous_marks=$myrowrank['mean'];						
					}//end of while ($myrowrank= DB_fetch_array($resultrank)) 
				}//end of else (DB_num_rows($resultroll) >0)
			}//end of foreach ($bus_report->scheduled_students as $sa => $st)
			}//end of if $bus_report->scheduled_students > 0
}//end of gradelevel not lower
//begin class is lower==1
else
{   
    $bus_report = new bus_report($myrowclass['id'],$_POST['period_id'],$db);
	$rank =0;
	if($bus_report->scheduled_students>0)
	{
			foreach ($bus_report->scheduled_students as $sa => $st) 
			{
			$total=0;
			$rank=$rank+1;
			$student_total=0;
			$student_total2=0;
			$subjects_taken_by_student=0;
			$subject_add=0;
			$mean=0;
			$subject_points=0;
			$scheduled = new scheduled($st['student_id'],$db);
			$scheduled->set_calendar_vars($myrowclass['id'],$st['student_id'],$_POST['period_id'],$st['id'],$db);
			$subject_meangrade_array=0;
			   foreach ($scheduled->subject as $y=>$z) 
				{
					$subjects_taken_by_student=$subjects_taken_by_student+1;
					$student_total2=$student_total2+$z['tmarks'];	
					$sqlroll = "SELECT an.rolled,dm.name FROM students_subject_marks an
					INNER JOIN debtorsmaster dm ON dm.id=an.student_id
					WHERE an.student_id='".$st['student_id']."'
					AND an.period_id='".$_POST['period_id']."'
					AND rolled=1";
					
					if($z['department_id']==1)
					{
						$sqlmean = "SELECT title FROM reportcardgrades
						WHERE range_from <=  '". $z['tmarks'] ."'
						AND range_to >='". $z['tmarks'] ."'
						AND grading LIKE 'MATHS'";
						$resultmean=DB_query($sqlmean,$db);
						$myrowmean=DB_fetch_row($resultmean);
						$points=$myrowmean[0];
						$subject_points=$subject_points+$points;
					}
					elseif($z['department_id']==2)
					{
					 	$sqlmean = "SELECT title FROM reportcardgrades
						WHERE range_from <=  '". $z['tmarks'] ."'
						AND range_to >='". $z['tmarks'] ."'
						AND grading LIKE 'KISWAHILI'";
						$resultmean=DB_query($sqlmean,$db);
						$myrowmean=DB_fetch_row($resultmean);
						$points=$myrowmean[0];
						$subject_points=$subject_points+$points;
					}
					elseif($z['department_id']==3)
					{
						$sqlmean = "SELECT title FROM reportcardgrades
						WHERE range_from <=  '". $z['tmarks'] ."'
						AND range_to >='". $z['tmarks'] ."'
						AND grading LIKE 'OTHER'";
						$resultmean=DB_query($sqlmean,$db);
						$myrowmean=DB_fetch_row($resultmean);
						$points=$myrowmean[0];
						$subject_points=$subject_points+$points;
					}
					elseif($z['department_id']==4)
					{
						$sqlmean = "SELECT title FROM reportcardgrades
						WHERE range_from <=  '". $z['tmarks'] ."'
						AND range_to >='". $z['tmarks'] ."'
						AND grading LIKE 'SCIENCE'";
						$resultmean=DB_query($sqlmean,$db);
						$myrowmean=DB_fetch_row($resultmean);
						$points=$myrowmean[0];
						$subject_points=$subject_points+$points;
					}
					elseif($z['department_id']==5)
					{
						$sqlmean = "SELECT title FROM reportcardgrades
						WHERE range_from <=  '". $z['tmarks'] ."'
						AND range_to >='". $z['tmarks'] ."'
						AND grading LIKE 'OTHER'";
						$resultmean=DB_query($sqlmean,$db);
						$myrowmean=DB_fetch_row($resultmean);
						$points=$myrowmean[0];
						$subject_points=$subject_points+$points;
					}	
						
						
					$resultroll = DB_query($sqlroll,$db);	
						if(DB_num_rows($resultroll) >0)
						{
						$myrowroll= DB_fetch_array($resultroll);
						$rolled=$myrowroll['rolled'];
						$name=$myrowroll['name'];
						}
						else
						{
						$sql = "INSERT INTO students_subject_marks (student_id,subject_id,period_id,marks,academic_year,class_id)
						VALUES ('" . $st['student_id'] ."','" .$z['id'] ."','" .$_POST['period_id'] ."','" .$z['tmarks'] ."',
						'".$_SESSION['year']."','".$myrowclass['id']."')";
						$result = DB_query($sql,$db);
						}				
				}//end of scheduled subject
			$sqlroll = "SELECT an.rolled,dm.name FROM termly_class_ranks an
			INNER JOIN debtorsmaster dm ON dm.id=an.student_id
			WHERE an.student_id='".$st['student_id']."'
			AND an.period_id='".$_POST['period_id']."'
			AND rolled=1";
			$resultroll = DB_query($sqlroll,$db);	
		
			if(DB_num_rows($resultroll) >0)
			{
				$myrowroll= DB_fetch_array($resultroll);
				$rolled=$myrowroll['rolled'];
				$name=$myrowroll['name'];
			}
			else
			{
				
				if($subjects_taken_by_student==0)
				{
				 $subjects_taken_by_student=1;
				}
				//$mean=$student_total2/$subjects_taken_by_student;
				$mean=$subject_points;
				$meanScore=number_format($mean/11,0);	
				$sql = "INSERT INTO termly_class_ranks (student_id,period_id,class_id,academic_year,marks,mean,no_of_subjects,
				meanScore)
				VALUES ('" . $st['student_id'] ."','" .$_POST['period_id'] ."','" .$myrowclass['id'] ."','".$_SESSION['year']."',
				'$student_total2','$mean',11,'$meanScore')";
				$result = DB_query($sql,$db);
				$msg = _('Student ranks generated successfuly');	
			
				$ranked=0;
				$rank_ties=0;					
				$sqlrank = "SELECT mean,student_id FROM termly_class_ranks 
				WHERE class_id='".$myrowclass['id']."'
				AND period_id='".$_POST['period_id']."'
				ORDER BY mean DESC";
				$resultrank = DB_query($sqlrank,$db);
				$previous_marks='';	
				while ($myrowrank= DB_fetch_array($resultrank))
				{
					if ($myrowrank['mean'] == $previous_marks) 
					{
					 $ranked=$ranked;
					 $rank_ties=$rank_ties+1;
					}
					else
					{
						$ranked=$rank_ties+$ranked+1;
						$rank_ties=0;
					}
					$stude=$myrowrank['student_id'];
					$sqlgenerate = "UPDATE termly_class_ranks
					SET class_rank='" . $ranked . "'
					WHERE student_id='".$stude."'
					AND period_id='".$_POST['period_id']."'
					AND class_id='" .$myrowclass['id'] ."'";
					$generate = DB_query($sqlgenerate,$db);	
					$previous_marks=$myrowrank['mean'];						
				}//end of `while ($myrowrank= DB_fetch_array($resultrank)) 
			}//end of else (DB_num_rows($resultroll) >0)
		}//end of foreach ($bus_report->scheduled_students as $sa => $st
	}//	end of if($bus_report->scheduled_students>0)		
}// end class is lower	
		
		
	
$exams=get_exams($db);
if($myrowclass['lower']==0)
{
	foreach($exams as $exms=>$ex)
	{
	$exam_rank=0;
	$stud_array=mode_marks($myrowclass['id'],$_POST['period_id'] ,$ex['id'],$db);
	if($stud_array>0)
	{
		foreach($stud_array as $studs=>$stds)
		{	
			$language_marks_per_exam = array();
			$science_marks_per_exam = array();
			$mathematics_marks_per_exam = array();
			$technical_marks_per_exam = array();
			$humanity_marks_per_exam = array();
			$subjects_taken_by_student=0;
			$total_per_exam_marks=0;
			$subject_points_per_exam=0;
			$sqlsub = "select sm.actual_marks,sub.department_id from subjects sub INNER JOIN studentsmarks sm ON
			 sm.subject_id=sub.id
			WHERE sm.student_id='".$stds['id']."'
			AND sm.period_id='".$_POST['period_id']."'
			AND sm.exam_mode='".$ex['id']."'";
			$resultsub = DB_query($sqlsub,$db);
			while($rowsub=DB_fetch_array($resultsub))
			{
				if($rowsub['department_id']==1)
				{
				  
				  array_push($mathematics_marks_per_exam, $rowsub['actual_marks']);
				  if(sizeof($mathematics_marks_per_exam)<2)
				  {
					$subjects_taken_by_student=$subjects_taken_by_student+1;				
					
				  }
				}
				else if($rowsub['department_id']==2)
				{
				
				  array_push($language_marks_per_exam, $rowsub['actual_marks']);
				   if(sizeof($language_marks_per_exam)<3)
				  {
					$subjects_taken_by_student=$subjects_taken_by_student+1;
				  }	
				}
				else if($rowsub['department_id']==3){
				  array_push($humanity_marks_per_exam, $rowsub['actual_marks']);
				  if(sizeof($humanity_marks_per_exam)<2)
				  {
					$subjects_taken_by_student=$subjects_taken_by_student+1;
				  }
				}
				else if($rowsub['department_id']==4){
				  array_push($science_marks_per_exam, $rowsub['actual_marks']);
				 if(sizeof($science_marks_per_exam)<3)
				  {
					$subjects_taken_by_student=$subjects_taken_by_student+1;
				  }
				}
				else if($rowsub['department_id']==5){
				 array_push($technical_marks_per_exam, $rowsub['actual_marks']);
				 if(sizeof($technical_marks_per_exam)<2)
				  {
					$subjects_taken_by_student=$subjects_taken_by_student+1;
				  }	
				}
			}//end of while
			rsort($language_marks_per_exam);
			$language_size=sizeof($language_marks_per_exam);
			$total_language_marks=0;			
			for($i=0; $i<$language_size; $i++)
			{
			  if($i<2)
			  {	
				$total_language_marks=$total_language_marks+$language_marks_per_exam[$i];
				
				$sqlmean = "SELECT title FROM reportcardgrades
				WHERE range_from <=  '". $language_marks_per_exam[$i] ."'
				AND range_to >='". $language_marks_per_exam[$i] ."'
				AND grading LIKE 'OTHER'";
				$resultmean=DB_query($sqlmean,$db);
				$myrowmean=DB_fetch_row($resultmean);
				$points=$myrowmean[0];
				$subject_points_per_exam=$subject_points_per_exam+$points;
			  }			   		
			}
			rsort($mathematics_marks_per_exam);
			$mathematics_size=sizeof($mathematics_marks_per_exam);
			$total_mathematics_marks=0;			
			for($i=0; $i<$mathematics_size; $i++)
			{
			  if($i<1)
			  {	
				$total_mathematics_marks=$total_mathematics_marks+$mathematics_marks_per_exam[$i];
				
				$sqlmean = "SELECT title FROM reportcardgrades
				WHERE range_from <=  '". $mathematics_marks_per_exam[$i] ."'
				AND range_to >='". $mathematics_marks_per_exam[$i] ."'
				AND grading LIKE 'MATHS'";
				$resultmean=DB_query($sqlmean,$db);
				$myrowmean=DB_fetch_row($resultmean);
				$points=$myrowmean[0];
				$subject_points_per_exam=$subject_points_per_exam+$points;
			  }			   		
			}
			
			rsort($humanity_marks_per_exam);
			$humanity_size=sizeof($humanity_marks_per_exam);
			$total_humanity_marks=0;			
			for($i=0; $i<$humanity_size; $i++)
			{
			  if($i<1)
			  {	
				$total_humanity_marks=$total_humanity_marks+$humanity_marks_per_exam[$i];
				
				$sqlmean = "SELECT title FROM reportcardgrades
				WHERE range_from <=  '". $humanity_marks_per_exam[$i] ."'
				AND range_to >='". $humanity_marks_per_exam[$i] ."'
				AND grading LIKE 'OTHER'";
				$resultmean=DB_query($sqlmean,$db);
				$myrowmean=DB_fetch_row($resultmean);
				$points=$myrowmean[0];
				$subject_points_per_exam=$subject_points_per_exam+$points;
			  }			   		
			}
			
			
			rsort($science_marks_per_exam);
			$science_size=sizeof($science_marks_per_exam);
			$total_science_marks=0;			
			for($i=0; $i<$science_size; $i++)
			{
			  if($i<2)
			  {	
				$total_science_marks=$total_science_marks+$science_marks_per_exam[$i];
				
				$sqlmean = "SELECT title FROM reportcardgrades
				WHERE range_from <=  '". $science_marks_per_exam[$i] ."'
				AND range_to >='". $science_marks_per_exam[$i] ."'
				AND grading LIKE 'SCIENCE'";
				$resultmean=DB_query($sqlmean,$db);
				$myrowmean=DB_fetch_row($resultmean);
				$points=$myrowmean[0];
				$subject_points_per_exam=$subject_points_per_exam+$points;
			  }			   		
			}
	
			rsort($technical_marks_per_exam);
			$technical_size=sizeof($technical_marks_per_exam);
			$total_technical_marks=0;			
			for($i=0; $i<$technical_size; $i++)
			{
			  if($i<1)
			  {	
				$total_technical_marks=$total_technical_marks+$technical_marks_per_exam[$i];
				
				$sqlmean = "SELECT title FROM reportcardgrades
				WHERE range_from <=  '". $technical_marks_per_exam[$i] ."'
				AND range_to >='". $technical_marks_per_exam[$i] ."'
				AND grading LIKE 'OTHER'";
				$resultmean=DB_query($sqlmean,$db);
				$myrowmean=DB_fetch_row($resultmean);
				$points=$myrowmean[0];
				$subject_points_per_exam=$subject_points_per_exam+$points;
			  }			   		
			}
			
			$total_per_exam_marks=$total_language_marks+$total_mathematics_marks+$total_humanity_marks+$total_science_marks
			+$total_technical_marks;
			$exam_rank=$exam_rank+1;
			$meanScore=number_format($subject_points_per_exam/7,0);
			$sql = "INSERT INTO exam_ranks (student_id,period_id,class_id,marks,academic_year,exam_id,stream_id,
			no_of_subjects,mean,meanScore)
			VALUES ('" . $stds['id']."','" .$_POST['period_id'] ."','" .$myrowclass['id'] ."','".$total_per_exam_marks."',
			'".$_SESSION['year']."','".$ex['id']."','" . $stds['stream']. 
			"',7,'$subject_points_per_exam','$meanScore')";
			$result = DB_query($sql,$db);
		}
		$sql = "SELECT id FROM markingperiods 
		ORDER BY id";
		$result=DB_query($sql,$db);
		while ($myrow=DB_fetch_array($result))
		{					
			$ranked2=0;
			$rank_ties2=0;					
			$sqlrank2 = "SELECT mean,student_id FROM exam_ranks 
			WHERE class_id='".$myrowclass['id']."'
			AND period_id='".$_POST['period_id']."'
			AND exam_id='".$myrow['id']."'
			ORDER BY mean DESC";
			$resultrank2 = DB_query($sqlrank2,$db);
			$previous_marks2='';	
			while ($myrowrank2= DB_fetch_array($resultrank2))
			{
				if ($myrowrank2['mean'] == $previous_marks2)
				{
				 $ranked2=$ranked2;
				 $rank_ties2=$rank_ties2+1;
				}
				else
				{
					$ranked2=$rank_ties2+$ranked2+1;
					$rank_ties2=0;
				}
				$stude2=$myrowrank2['student_id'];
				$sqlgenerate2 = "UPDATE exam_ranks
				SET rank='" . $ranked2 . "'
				WHERE student_id='".$stude2."'
				AND period_id='".$_POST['period_id']."'
				AND class_id='" .$myrowclass['id'] ."'
				AND exam_id='".$myrow['id']."'";
				$generate2 = DB_query($sqlgenerate2,$db);	
				$previous_marks2=$myrowrank2['mean'];	
			}																										
		}					
	}// end of if($stud_array>0)
}//end of foreach($exams as $exms=>$ex)
}//end of if class is lower
//begin class is lower
else
{
	foreach($exams as $exms=>$ex)
	{
	$exam_rank=0;
	$stud_array=mode_marks($myrowclass['id'],$_POST['period_id'] ,$ex['id'],$db);
	if($stud_array>0)
	{
		foreach($stud_array as $studs=>$stds)
		{	
			$subjects_taken_by_student=0;
			$total_per_exam_marks=0;
			$subject_points_per_exam=0;
			$sqlsub = "select sm.actual_marks,sub.department_id from subjects sub INNER JOIN studentsmarks sm ON
			 sm.subject_id=sub.id
			WHERE sm.student_id='".$stds['id']."'
			AND sm.period_id='".$_POST['period_id']."'
			AND sm.exam_mode='".$ex['id']."'";
			$resultsub = DB_query($sqlsub,$db);
			while($rowsub=DB_fetch_array($resultsub))
			{
				$subjects_taken_by_student=$subjects_taken_by_student+1;
				$total_per_exam_marks=$total_per_exam_marks+$rowsub['actual_marks'];
				
				if($rowsub['department_id']==1)
					{
						$sqlmean = "SELECT title FROM reportcardgrades
						WHERE range_from <=  '". $rowsub['actual_marks'] ."'
						AND range_to >='". $rowsub['actual_marks'] ."'
						AND grading LIKE 'MATHS'";
						$resultmean=DB_query($sqlmean,$db);
						$myrowmean=DB_fetch_row($resultmean);
						$points=$myrowmean[0];
						$subject_points_per_exam=$subject_points_per_exam+$points;
					}
					elseif($rowsub['department_id']==2)
					{
					 	$sqlmean = "SELECT title FROM reportcardgrades
						WHERE range_from <=  '". $rowsub['actual_marks'] ."'
						AND range_to >='". $rowsub['actual_marks'] ."'
						AND grading LIKE 'KISWAHILI'";
						$resultmean=DB_query($sqlmean,$db);
						$myrowmean=DB_fetch_row($resultmean);
						$points=$myrowmean[0];
						$subject_points_per_exam=$subject_points_per_exam+$points;
					}
					elseif($rowsub['department_id']==3)
					{
						$sqlmean = "SELECT title FROM reportcardgrades
						WHERE range_from <=  '". $rowsub['actual_marks'] ."'
						AND range_to >='". $rowsub['actual_marks'] ."'
						AND grading LIKE 'OTHER'";
						$resultmean=DB_query($sqlmean,$db);
						$myrowmean=DB_fetch_row($resultmean);
						$points=$myrowmean[0];
						$subject_points_per_exam=$subject_points_per_exam+$points;
					}
					elseif($rowsub['department_id']==4)
					{
						$sqlmean = "SELECT title FROM reportcardgrades
						WHERE range_from <=  '". $rowsub['actual_marks'] ."'
						AND range_to >='". $rowsub['actual_marks'] ."'
						AND grading LIKE 'SCIENCE'";
						$resultmean=DB_query($sqlmean,$db);
						$myrowmean=DB_fetch_row($resultmean);
						$points=$myrowmean[0];
						$subject_points_per_exam=$subject_points_per_exam+$points;
					}
					elseif($rowsub['department_id']==5)
					{
						$sqlmean = "SELECT title FROM reportcardgrades
						WHERE range_from <=  '". $rowsub['actual_marks'] ."'
						AND range_to >='". $rowsub['actual_marks'] ."'
						AND grading LIKE 'OTHER'";
						$resultmean=DB_query($sqlmean,$db);
						$myrowmean=DB_fetch_row($resultmean);
						$points=$myrowmean[0];
						$subject_points_per_exam=$subject_points_per_exam+$points;
					}	
				 
			}//end of while	
	
			$exam_rank=$exam_rank+1;
			$meanScore=number_format($subject_points_per_exam/11,0);
			$sql = "INSERT INTO exam_ranks (student_id,period_id,class_id,marks,academic_year,exam_id,stream_id,
			no_of_subjects,mean,meanScore)
			VALUES ('" . $stds['id']."','" .$_POST['period_id'] ."','" .$myrowclass['id'] ."','".$total_per_exam_marks."',
			'".$_SESSION['year']."','".$ex['id']."','" . $stds['stream']."',11,
			'$subject_points_per_exam','$meanScore')";
			$result = DB_query($sql,$db);
		}//end of foreach($stud_array as $studs=>$stds)
		$sql = "SELECT id FROM markingperiods 
		ORDER BY id";
		$result=DB_query($sql,$db);
		while ($myrow=DB_fetch_array($result))
		{					
			$ranked2=0;
			$rank_ties2=0;					
			$sqlrank2 = "SELECT mean,student_id FROM exam_ranks 
			WHERE class_id='".$myrowclass['id']."'
			AND period_id='".$_POST['period_id']."'
			AND exam_id='".$myrow['id']."'
			ORDER BY mean DESC";
			$resultrank2 = DB_query($sqlrank2,$db);
			$previous_marks2='';	
			while ($myrowrank2= DB_fetch_array($resultrank2))
			{
				if ($myrowrank2['mean'] == $previous_marks2)
				{
				 $ranked2=$ranked2;
				 $rank_ties2=$rank_ties2+1;
				}
				else
				{
					$ranked2=$rank_ties2+$ranked2+1;
					$rank_ties2=0;
				}
				$stude2=$myrowrank2['student_id'];
				$sqlgenerate2 = "UPDATE exam_ranks
				SET rank='" . $ranked2 . "'
				WHERE student_id='".$stude2."'
				AND period_id='".$_POST['period_id']."'
				AND class_id='" .$myrowclass['id'] ."'
				AND exam_id='".$myrow['id']."'";
				$generate2 = DB_query($sqlgenerate2,$db);	
				$previous_marks2=$myrowrank2['mean'];	
			}//end of while ($myrowrank2= DB_fetch_array($resultrank2))																										
		}//end of while ($myrow=DB_fetch_array($result))					
	}// end of if($stud_array>0)
 }//end of foreach($exams as $exms=>$ex)
}//end else class is lower				
}// end class while
prnMsg( _('Classes compiled successfully'),'success');

$sqlstream = "SELECT * FROM classes WHERE grade_level_id='" . $_POST['class_id'] . "'";
$resultstream = DB_query($sqlstream,$db);	
while ($myrowstream= DB_fetch_array($resultstream))
{
	
	$sqllower = "SELECT gl.lower FROM gradelevels gl INNER JOIN classes cl where cl.grade_level_id=gl.id
	AND cl.id='".$myrowstream['id']."'";
	$resultlower = DB_query($sqllower,$db);
	$myrowlower= DB_fetch_row($resultlower);
	$isUpper=$myrowlower[0];
$bus_report_stream = new bus_report_stream($myrowstream['id'],$_POST['period_id'],$db);
$subjects_array = tep_get_subjects_stream($myrowstream['id'],$_POST['period_id'],$db);	
$totalEnglishPoints=0;
$totalKiswahiliPoints=0;
$totalMathPoints=0;
$totalGeographyPoints=0;
$totalHistoryPoints=0;
$totalPhysicsPoints=0;
$totalBusinessStudies=0;
$totalChemistryPoints=0;
$totalBiologyPoints=0;
$totalCREpoints=0;
$totalAgriculturePoints=0;
foreach ($bus_report_stream->scheduled_students as $sa => $st) 
{
	$no_of_students=$no_of_students+1;
	$rank=$rank+1;		
	$scheduled = new scheduled_stream($st['student_id'],$db);
	$subjects_taken_by_student=0;
	$student_total=0;
	$student_total2=0;	
	$scheduled->set_calendar_vars_stream($myrowstream['id'],$st['student_id'],$_POST['period_id'],$st['id'],$db);
	$subject_meangrade_array=0;
	foreach ($scheduled->subject as $y=>$z) 
	{
		$student_total2=$student_total2+$z['tmarks'];
		if(isset($z['tmarks']) && $z['tmarks'] !="")
		{
		     $sql = "SELECT grade,title FROM reportcardgrades
		     WHERE range_from <=  '". $z['tmarks'] ."'
		     AND range_to >='". $z['tmarks']."'
		     AND grading LIKE '".$z['grading']."'";
		     $result=DB_query($sql,$db);
		     $myrow=DB_fetch_row($result);
		     $sub_grade=$myrow[0];
	             $sub_points=$myrow[1];
		}
			
		$addToSubject=0;
		$addToSubject=studentTakesSubject($st['student_id'],$z['id'],$_POST['period_id'],$db);
		if($z['id']==4 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'OTHER'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalEnglishPoints=$totalEnglishPoints+$points;
		}
		if($z['id']==6 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'OTHER'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalKiswahiliPoints=$totalKiswahiliPoints+$points;
		}
		if($z['id']==5 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'MATHS'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalMathPoints=$totalMathPoints+$points;
		}
		if($z['id']==14 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'OTHER'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalGeographyPoints=$totalGeographyPoints+$points;
		}
		if($z['id']==8 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'OTHER'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalHistoryPoints=$totalHistoryPoints+$points;
		}
		if($z['id']==9 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'SCIENCE'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalPhysicsPoints=$totalPhysicsPoints+$points;
		}
		if($z['id']==10 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'OTHER'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalBusinessStudies=$totalBusinessStudies+$points;
		}
		if($z['id']==11 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'SCIENCE'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalChemistryPoints=$totalChemistryPoints+$points;
		}
		if($z['id']==12 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'SCIENCE'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalBiologyPoints=$totalBiologyPoints+$points;
		}
		if($z['id']==13 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'OTHER'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalCREpoints=$totalCREpoints+$points;
		}
		if($z['id']==15 && $addToSubject==1){
		$sqlmean = "SELECT title FROM reportcardgrades
		WHERE range_from <=  '". $z['tmarks'] ."'
		AND range_to >='". $z['tmarks'] ."'
		AND grading LIKE 'OTHER'";
		$resultmean=DB_query($sqlmean,$db);
		$myrowmean=DB_fetch_row($resultmean);
		$points=$myrowmean[0];
		$totalAgriculturePoints=$totalAgriculturePoints+$points;
		}
	}//end of scheduled subject	
	
}
foreach ($subjects_array as $r => $s) 
{	
	$count=0;
	$total_marks=0;
	$total_marks2=0;
	$count=studentsRegisteredForSubject($s['id'],$_POST['period_id'],$myrowstream['id'],$db);
	
		if($s['id']==4){
		$subject_mean=number_format($totalEnglishPoints/$count,3);
		}
		if($s['id']==6){
		$subject_mean=number_format($totalKiswahiliPoints/$count,3);
		}
		if($s['id']==5){
		$subject_mean=number_format($totalMathPoints/$count,3);
		}
		if($s['id']==14){
		$subject_mean=number_format($totalGeographyPoints/$count,3);
		}
		if($s['id']==8){
		$subject_mean=number_format($totalHistoryPoints/$count,3);
		}
		if($s['id']==9){
		$subject_mean=number_format($totalPhysicsPoints/$count,3);
		}
		if($s['id']==10){
		$subject_mean=number_format($totalBusinessStudies/$count,3);
		}
		if($s['id']==11){
		$subject_mean=number_format($totalChemistryPoints/$count,3);
		}
		if($s['id']==12){
		$subject_mean=number_format($totalBiologyPoints/$count,3);
		}
		if($s['id']==13){
		$subject_mean=number_format($totalCREpoints/$count,3);
		}
		if($s['id']==15){
		$subject_mean=number_format($totalAgriculturePoints/$count,3);
		}
		$sql = "INSERT INTO class_subject_mean(subject_id,period_id,class,mean,subject_count)
		VALUES ('" . $s['id']."','" .$_POST['period_id'] ."','" .$myrowstream['id'] ."','".$subject_mean."','".$count."')";
		$result = DB_query($sql,$db);
	//$LeftOvers = $pdf->addTextWrap($XPos3,$YPos-10,300,9,$classSubjectGrade);
}//end of ssubjects array foreach	
if($isUpper==0)
{
     $bus_report_stream = new bus_report_stream($myrowstream['id'],$_POST['period_id'],$db);
	$rank_stream =0;
	if($bus_report_stream->scheduled_students>0)
	{
			
		foreach ($bus_report_stream->scheduled_students as $sa => $st) 
		{
			$total=0;
			$rank_stream=$rank_stream+1;
			$scheduled = new scheduled_stream($st['student_id'],$db);
			$subjects_taken_by_student=0;
			$student_total=0;
			$student_total2=0;
			$language_marks = array();
			$science_marks = array();
			$mathematics_marks = array();
			$technical_marks = array();
			$humanity_marks = array();
			$scheduled->set_calendar_vars_stream($myrowstream['id'],$st['student_id'],$_POST['period_id'],$st['id'],$db);
			$subject_meangrade_array=0;
			foreach ($scheduled->subject as $y=>$z) 
			{					
			if($z['department_id']==1)
			{
			  $engdip=$z['department_id'];
			  array_push($mathematics_marks, $z['tmarks']);
			  if(sizeof($mathematics_marks)<2)
			  {
			  	$subjects_taken_by_student=$subjects_taken_by_student+1;
			  }
			}
			else if($z['department_id']==2)
			{
			
			  array_push($language_marks, $z['tmarks']);
			   if(sizeof($language_marks)<3)
			  {
			  	$subjects_taken_by_student=$subjects_taken_by_student+1;
			  }	
			}
			else if($z['department_id']==3){
			  array_push($humanity_marks, $z['tmarks']);
			  if(sizeof($humanity_marks)<2)
			  {
			  	$subjects_taken_by_student=$subjects_taken_by_student+1;
			  }
			}
			else if($z['department_id']==4){
			  array_push($science_marks, $z['tmarks']);
			 if(sizeof($science_marks)<3)
			  {
			  	$subjects_taken_by_student=$subjects_taken_by_student+1;
			  }
			}
			else if($z['department_id']==5){
			 array_push($technical_marks, $z['tmarks']);
			 if(sizeof($technical_marks)<2)
			  {
			  	$subjects_taken_by_student=$subjects_taken_by_student+1;
			  }	
			}
			}//end of scheduled subject
		
		$sqlroll = "SELECT an.rolled,dm.name FROM termly_student_ranks an
		INNER JOIN debtorsmaster dm ON dm.id=an.student_id
		WHERE an.student_id='".$st['student_id']."'
		AND an.period_id='".$_POST['period_id']."'
		AND rolled=1";
		$resultroll = DB_query($sqlroll,$db);	
	
		if(DB_num_rows($resultroll) >0)
		{
			$myrowroll= DB_fetch_array($resultroll);
			$rolled=$myrowroll['rolled'];
			$name=$myrowroll['name'];
		}
	else
	{
	rsort($language_marks);
	$language_size=sizeof($language_marks);
	$total_language_marks=0;			
	for($i=0; $i<$language_size; $i++)
	{
	  if($i<2)
	  {	
		$total_language_marks=$total_language_marks+$language_marks[$i];
	  }			   		
	}

    rsort($mathematics_marks);
	$mathematics_size=sizeof($mathematics_marks);
	$total_mathematics_marks=0;			
	for($i=0; $i<$mathematics_size; $i++)
	{
	  if($i<1)
	  {	
		$total_mathematics_marks=$total_mathematics_marks+$mathematics_marks[$i];
	  }			   		
	}
	
	
	rsort($humanity_marks);
	$humanity_size=sizeof($humanity_marks);
	$total_humanity_marks=0;			
	for($i=0; $i<$humanity_size; $i++)
	{
	  if($i<1)
	  {	
		$total_humanity_marks=$total_humanity_marks+$humanity_marks[$i];
	  }			   		
	}
	
	
	rsort($science_marks);
	$science_size=sizeof($science_marks);
	$total_science_marks=0;			
	for($i=0; $i<$science_size; $i++)
	{
	  if($i<2)
	  {	
		$total_science_marks=$total_science_marks+$science_marks[$i];
	  }			   		
	}
	
	
	rsort($technical_marks);
	$technical_size=sizeof($technical_marks);
	$total_technical_marks=0;			
	for($i=0; $i<$technical_size; $i++)
	{
	  if($i<1)
	  {	
		$total_technical_marks=$total_technical_marks+$technical_marks[$i];
	  }			   		
	}
	$student_total2=$total_language_marks+$total_mathematics_marks+$total_humanity_marks+$total_science_marks
	+$total_technical_marks;
	
	$sql = "SELECT mean FROM termly_class_ranks  
	WHERE period_id =  '". $_POST['period_id'] ."'
	AND student_id='". $st['student_id'] ."'";
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_row($result);
	$student_stream_mean=$myrow[0];
	
	$meanScore=number_format($student_stream_mean/7,0);	
	$sql = "INSERT INTO termly_student_ranks (student_id,period_id,class_id,marks,academic_year,no_of_subjects,grade_level_id,
	mean,meanScore)
	VALUES ('" . $st['student_id']."','" .$_POST['period_id'] ."','" .$myrowstream['id'] ."','$student_total2',
	'".$_SESSION['year']."',7,'" .$_POST['class_id'] .
	"','$student_stream_mean','$meanScore')";
	$result = DB_query($sql,$db);
	$msg = _('Student ranks generated successfuly');
								
			$ranked=0;
			$ties=0;					
			$sqlrank = "SELECT mean,student_id FROM termly_student_ranks 
			WHERE class_id='".$myrowstream['id']."'
			AND period_id='".$_POST['period_id']."'
			ORDER BY mean DESC";
			$resultrank = DB_query($sqlrank,$db);	
			$prevRow2 = '';
				while ($myrowrank= DB_fetch_array($resultrank))
				{
					if ($myrowrank['mean'] == $prevRow2)
					 {
						 $ranked=$ranked;
						 $ties=$ties+1;
					}
					else
					{
						$ranked=$ties+$ranked+1;
						$ties=0;
					}
					$stude=$myrowrank['student_id'];
					$sqlgenerate = "UPDATE termly_student_ranks
					SET rank='" . $ranked . "'
					WHERE student_id='".$stude."'
					AND period_id='".$_POST['period_id']."'
					AND class_id='" .$myrowstream['id'] ."'";
					$generate = DB_query($sqlgenerate,$db);	
					$prevRow2=$myrowrank['mean'];					
				}//end of while ($myrowrank= DB_fetch_array($resultrank))	fu				
		}//end of else	
	   }//end of foreach ($bus_report_stream->scheduled_students as $sa => $st)	
	}//end of if($bus_report_stream->scheduled_students>0)
}//end if is upper
//begin is lower
else
{
 $bus_report_stream = new bus_report_stream($myrowstream['id'],$_POST['period_id'],$db);
	$rank_stream =0;
	if($bus_report_stream->scheduled_students>0)
	{
			
		foreach ($bus_report_stream->scheduled_students as $sa => $st) 
		{
			$total=0;
			$rank_stream=$rank_stream+1;
			$scheduled = new scheduled_stream($st['student_id'],$db);
			$subjects_taken_by_student=0;
			$student_total=0;
			$student_total2=0;
			$scheduled->set_calendar_vars_stream($myrowstream['id'],$st['student_id'],$_POST['period_id'],$st['id'],$db);
			$subject_meangrade_array=0;
			foreach ($scheduled->subject as $y=>$z) 
			{
			    $student_total2=$student_total2+$z['tmarks'];
			  	$subjects_taken_by_student=$subjects_taken_by_student+1;		
			}//end of scheduled subject
		
		$sqlroll = "SELECT an.rolled,dm.name FROM termly_student_ranks an
		INNER JOIN debtorsmaster dm ON dm.id=an.student_id
		WHERE an.student_id='".$st['student_id']."'
		AND an.period_id='".$_POST['period_id']."'
		AND rolled=1";
		$resultroll = DB_query($sqlroll,$db);	
	
		if(DB_num_rows($resultroll) >0)
		{
			$myrowroll= DB_fetch_array($resultroll);
			$rolled=$myrowroll['rolled'];
			$name=$myrowroll['name'];
		}
	else
	{
	$sql = "SELECT mean FROM termly_class_ranks  
	WHERE period_id =  '". $_POST['period_id'] ."'
	AND student_id='". $st['student_id'] ."'";
	$result=DB_query($sql,$db);
	$myrow=DB_fetch_row($result);
	$student_stream_mean=$myrow[0];
	
	$meanScore=number_format($student_stream_mean/11,0);	
	$sql = "INSERT INTO termly_student_ranks (student_id,period_id,class_id,marks,academic_year,no_of_subjects,mean,meanScore,
	grade_level_id)
	VALUES ('" . $st['student_id']."','" .$_POST['period_id'] ."','" .$myrowstream['id'] ."'
	,'$student_total2','".$_SESSION['year']."',11,'$student_stream_mean',
	'$meanScore','".$_POST['class_id']."')";
	$result = DB_query($sql,$db);
	
								
	$ranked=0;
	$ties=0;					
	$sqlrank = "SELECT mean,student_id FROM termly_student_ranks 
	WHERE class_id='".$myrowstream['id']."'
	AND period_id='".$_POST['period_id']."'
	ORDER BY mean DESC";
	$resultrank = DB_query($sqlrank,$db);	
	$prevRow2 = '';
				while ($myrowrank= DB_fetch_array($resultrank))
				{
					if ($myrowrank['mean'] == $prevRow2)
					 {
						 $ranked=$ranked;
						 $ties=$ties+1;
					}
					else
					{
						$ranked=$ties+$ranked+1;
						$ties=0;
					}
					$stude=$myrowrank['student_id'];
					$sqlgenerate = "UPDATE termly_student_ranks
					SET rank='" . $ranked . "'
					WHERE student_id='".$stude."'
					AND period_id='".$_POST['period_id']."'
					AND class_id='" .$myrowstream['id'] ."'";
					$generate = DB_query($sqlgenerate,$db);	
					$prevRow2=$myrowrank['mean'];					
				}//end of while ($myrowrank= DB_fetch_array($resultrank))	fu				
		}//end of else	
	   }//end of foreach ($bus_report_stream->scheduled_students as $sa => $st)	
	}//end of if($bus_report_stream->scheduled_students>0)
}//end of else is lower

	$subjects_array = tep_get_subjects_stream($myrowstream['id'],$_POST['period_id'],$db);
	if($subjects_array>0)
	{
		foreach ($subjects_array as $r => $s) 
		{
			$bus_report2 = new bus_report2($myrowstream['id'],$_POST['period_id'],$s['id'],$db);
			$count=0;
			$total_marks=0;
			$total_marks2=0;
			foreach ($bus_report2->scheduled_students as $a => $b) {
			$total_marks=total_marks($b['student_id'],$_POST['period_id'],$s['id'],$db);
			$total_marks2=$total_marks2+$total_marks;	
			$count=$count+1;
		}
		if($count > 0)
		{
			$subject_mean=$total_marks2/$count;
		}
		else
		{
			$subject_mean=0;
		}
	
		$sqlroll = "SELECT an.rolled FROM subject_mean an
		WHERE an.period_id='".$_POST['period_id']."'
		AND rolled=1";
		$resultroll = DB_query($sqlroll,$db);	
	
		if(DB_num_rows($resultroll) >0)
		{
			$myrowroll= DB_fetch_array($resultroll);
		}
		else
		{
			$sql="INSERT INTO subject_mean (subject_id,period_id,stream,mean,roll,academic_year,grade_level_id)
			VALUES ('" . $s['id'] ."','" . $_POST['period_id'] ."','" . $myrowstream['id']."',	
			'" .$subject_mean."','" . $count."','".$_SESSION['year']."','".$myrowstream['grade_level_id']."')";
			$result = DB_query($sql,$db,$ErrMsg);
		}//end of else

	}//end of ssubjects array foreach
 }//end of if($subjects_array>0)		
}// end stream while
prnMsg( _('Streams compiled successfully'),'success');
unset($_SESSION['year']);
}
else { 
include('includes/session.inc');
$title = _('Manage Students');
include('includes/header.inc');
echo '<p class="page_title_text">' . ' ' . _('Generate Data For Term') . '';
echo '<FORM METHOD="POST" ACTION="' . $_SERVER['PHP_SELF'] . '?' . SID . '">';
echo '<input type="hidden" name="FormID" value="' . $_SESSION['FormID'] . '" />';
echo '<CENTER><TABLE class="enclosed"><TR><TD>' . _('Period:') . '</TD><TD><SELECT Name="period_id">';
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
	echo '<TR><TD>' . _('Class:') . '</TD><TD><SELECT Name="class_id">';
		$sql="SELECT id,grade_level FROM gradelevels";
		$result=DB_query($sql,$db);
		while ($myrow = DB_fetch_array($result)) {
			if ($myrow['id'] == $_POST['id']) {  
				echo '<OPTION SELECTED VALUE=';
			} else {
				echo '<OPTION VALUE=';
			}
			echo $myrow['id'] . '>'.' '.$myrow['grade_level'];
		} //end while loop
	echo '</SELECT></TD></TR>';
	echo "</TABLE>";
	echo "<P><CENTER><INPUT TYPE='Submit' NAME='generate' VALUE='" . _('Generate') . "'> ";

	include('includes/footer.inc');;
} /*end of else not PrintPDF */

?>