<?php
function total_marks_class($student_id,$period,$subject_id,$db) {
	$sql = "select COUNT(mp.exam_type_id) as no_of_cats from studentsmarks sm
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	WHERE  mp.exam_type_id=1
	AND sm.student_id='$student_id'
	AND rs.subject_id='$subject_id'
	AND rs.period_id='$period'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$num_of_cats=$row[0];
	
	$sql = "select SUM(sm.marks) as cat_marks from studentsmarks sm
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	WHERE  mp.exam_type_id=1
	AND sm.student_id='$student_id'
	AND rs.subject_id='$subject_id'
	AND rs.period_id='$period'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$cat_marks=$row[0];

	if($num_of_cats > 0){
	$average_marks=number_format($cat_marks/$num_of_cats,0);
	}
	else{
	$average_marks='';
	}
	$sql = "select SUM(sm.marks) as exam_marks from studentsmarks sm
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	WHERE  mp.exam_type_id !=1
	AND sm.student_id='$student_id'
	AND rs.subject_id='$subject_id'
	AND rs.period_id='$period'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$exam_marks=$row[0];
	$real_marks=$exam_marks+$average_marks;
	
	if($real_marks > 0){
	$real_marks=number_format($real_marks,0);
	}
	else{
	$real_marks='';
	}
	return $real_marks;	
}
function tep_get_students_class($class,$period,$db) {
	$sql = "select DISTINCT(rs.student_id),SUM(sm.marks) as totalmarks from registered_students rs
	INNER JOIN debtorsmaster dm ON dm.id=rs.student_id
	INNER JOIN studentsmarks sm ON sm.student_id=rs.student_id
	WHERE rs.class_id='$class'
	AND rs.period_id='$period_id'
	GROUP BY sm.student_id
	order by rs.srydent_id ";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$students_array[] = array("id" => $row['id'],
				     "name" => $row['name']);
	}
	return $students_array;
}
function tep_set_students_class($debtorno,$period,$db) {
	$sql = "select id,student_id from registered_students 
	WHERE student_id='$debtorno'
	AND period_id='$period'";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$ids[] = array("id" => $row['id'],
				     "student_id" => $row['student_id']);
	}
	
	return $ids;
}
function tep_get_exam_mode_class($db) {
	$sql = "select * from markingperiods order by id";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$status_array[] = array("id" => $row['id'],
				     "title" => $row['title']);
	}
	return $status_array;
}
function tep_get_exam_mode_marks_class($marking_period_id, $calendar_id,$db) {
	$sql = "select marks from studentsmarks where 
	exam_mode='$marking_period_id' and calendar_id='$calendar_id'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	return $row['marks'];
}

class student_class {

var $debtorno;
var $name;
var $class_id;
var $course_id;
var $grade_level_id;

function student_class($debtorno,$db) {
	$sql = "select * from debtorsmaster where id = '$debtorno' limit 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->debtorno = $debtorno;
	$this->name = $row['name'];
	$this->class_id = $row['class_id'];
	$this->course_id = $row['course_id'];
}


function set_calendar_vars_class($calendar_id,$db) {
	$sql = "select * from registered_students where id='$calendar_id' LIMIT 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->calendar_id = $calendar_id;
}


}

 class scheduled_class extends student_class 
 {
	var $calendar_id;
	var $start_date;
	var $exam_mode;			//array containing the number of users in different status.
	var $total_users;
	var $cancelled;

	function scheduled_class($debtorno,$db) 
	{
		$this->student_class($debtorno,$db);
	}
	function set_calendar_vars_class($calendar_id,$db) 
	{
		$sql = "select * from registered_students where id='$calendar_id' LIMIT 1";
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		$this->calendar_id = $calendar_id;
		// set status var
		$subjects_array = tep_get_exam_mode_class($db);
		foreach ($subjects_array as $r=>$s) {
			$this->exam_mode[] = array("id" => $s['id'],
								   "tmarks" => tep_get_exam_mode_marks_class($s['id'], $this->calendar_id,$db));
		}
	}

}
class bus_report_class {
	var $student;	
	var $cumilative_subject_marks;
	var $scheduled_students;
	function bus_report_class($class,$period,$subject,$db) 
	{
		$this->student = $this->get_student_class($db);
		$this->scheduled_students = $this->get_scheduled_students_class($class,$period,$subject,$db);
	}

	function get_student_class($db) {
		$student_array = array();		
		$sql = "select debtorno from debtorsmaster ";
		$result = DB_query($sql,$db);
		while ($row = DB_fetch_array($result)) 
		{
			$student_array[] = $row['debtorno'];
		}
		return $student_array;
	}


	function get_scheduled_students_class($class,$period,$subject,$db) 
	{	
		$scheduled_students_count;
		$sql = "select COUNT(*) FROM students_subject_marks
		WHERE period_id='$period'
		AND subject_id='$subject'
		AND class_id='$class'";
		$result = DB_query($sql,$db);
		$row = DB_fetch_row($result);
		$scheduled_students_count=$row[0];
		return $scheduled_students_count;			
}	
function total_points_class($class,$period_id,$subject_id,$db) 
{
	$sql = "select department_id FROM subjects
	WHERE id='$subject_id'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$subject_department=$row[0];
	
	$cumilative_subject_marks=0;
	$sql = "select marks FROM students_subject_marks
	WHERE subject_id='$subject_id'
	AND period_id='$period_id'
	AND class_id='$class'";
	$result = DB_query($sql,$db);
	while($row = DB_fetch_array($result))
	{
	if($subject_department==1)
	{
					$sqlmean = "SELECT title FROM reportcardgrades
					WHERE range_from <=  '". $row['marks'] ."'
					AND range_to >='". $row['marks'] ."'
					AND grading LIKE 'MATHS'";
					$resultmean=DB_query($sqlmean,$db);
					$myrowmean=DB_fetch_row($resultmean);
					$points=$myrowmean[0];
					$cumilative_subject_marks=$cumilative_subject_marks+$points;
	}
	else if($subject_department==2)
	{
					$sqlmean = "SELECT title FROM reportcardgrades
					WHERE range_from <=  '". $row['marks'] ."'
					AND range_to >='". $row['marks'] ."'
					AND grading LIKE 'OTHER'";
					$resultmean=DB_query($sqlmean,$db);
					$myrowmean=DB_fetch_row($resultmean);
					$points=$myrowmean[0];
					$cumilative_subject_marks=$cumilative_subject_marks+$points;
	}
	else if($subject_department==3)
	{
					$sqlmean = "SELECT title FROM reportcardgrades
					WHERE range_from <=  '". $row['marks'] ."'
					AND range_to >='". $row['marks'] ."'
					AND grading LIKE 'OTHER'";
					$resultmean=DB_query($sqlmean,$db);
					$myrowmean=DB_fetch_row($resultmean);
					$points=$myrowmean[0];
					$cumilative_subject_marks=$cumilative_subject_marks+$points;
	}
	else if($subject_department==4)
	{
					$sqlmean = "SELECT title FROM reportcardgrades
					WHERE range_from <=  '". $row['marks'] ."'
					AND range_to >='". $row['marks'] ."'
					AND grading LIKE 'SCIENCE'";
					$resultmean=DB_query($sqlmean,$db);
					$myrowmean=DB_fetch_row($resultmean);
					$points=$myrowmean[0];
					$cumilative_subject_marks=$cumilative_subject_marks+$points;
	}
	else if($subject_department==5)
	{
					$sqlmean = "SELECT title FROM reportcardgrades
					WHERE range_from <=  '". $row['marks'] ."'
					AND range_to >='". $row['marks'] ."'
					AND grading LIKE 'OTHER'";
					$resultmean=DB_query($sqlmean,$db);
					$myrowmean=DB_fetch_row($resultmean);
					$points=$myrowmean[0];
					$cumilative_subject_marks=$cumilative_subject_marks+$points;
	}	
	}//end of while
	return $cumilative_subject_marks;	
}

 }

?>
