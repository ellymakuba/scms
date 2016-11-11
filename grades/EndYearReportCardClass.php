<?php
function get_students_rank($class,$academic_year,$db) {
	$sql = "select student_id,class_id from annual_ranks
	WHERE class_id='$class'
	AND academic_year_id='$academic_year'
	ORDER BY total DESC";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$student_id[] = array("student_id" => $row['student_id'],
						"class_id" => $row['class_id']);
	}
	return $student_id;
}
function get_students_in_class($class,$db) {
	$sql = "select id,name,grade_level_id from debtorsmaster
	WHERE grade_level_id='$class'
	order by id";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$students[] = array("id" => $row['id'],
						"grade_level_id" => $row['grade_level_id'],
						"name" => $row['name']);
	}
	return $students;
}
function get_terms($academic_year,$db) {
	$sql = "select t.id,t.title from terms t
	INNER JOIN collegeperiods cp ON cp.term_id=t.id
	WHERE cp.year='$academic_year'
	order by t.id";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$terms[] = array("id" => $row['id'],
						"title" => $row['title']);
	}
	return $terms;
}
function tep_get_students($class,$db) {
	$sql = "select dm.*,SUM(sm.marks) as totalmarks,dm.id as student_id,dm.debtorno,dm.name from debtorsmaster dm 
	INNER JOIN studentsmarks sm ON sm.student_id=dm.id
	WHERE class_id='$class'
	GROUP BY sm.student_id
	order by debtorno";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$students_array[] = array("id" => $row['id'],
						"debtorno" => $row['debtorno'],
				     "name" => $row['name']);
	}
	return $students_array;
}
function tep_set_students($student_id,$period,$db) {
	$sql = "select rs.id,rs.student_id from registered_students rs
	INNER JOIN collegeperiods cp ON cp.id=rs.period_id
	INNER JOIN years yr ON yr.id=cp.year
	WHERE rs.student_id='$student_id'
	AND yr.id='$period'";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$ids[] = array("id" => $row['id'],
				     "student_id" => $row['student_id']);
	}
	
	return $ids;
}
function tep_get_subjects($student_id,$period,$term_id,$subject,$db) {
	$sql = "select sub.* from subjects sub 
	INNER JOIN registered_students rs ON rs.subject_id=sub.id
	INNER JOIN collegeperiods cp ON cp.id=rs.period_id
	WHERE rs.student_id='$student_id'
	AND rs.academic_year_id='$period'
	AND cp.term_id='$term_id'
	GROUP BY rs.subject_id
	order by rs.period_id,sub.id";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$subjects_array[] = array("id" => $row['id'],
				     "subject_code" => $row['subject_code'],
					 "subject_name" => $row['subject_name']);
	}
	return $subjects_array;
}

function tep_get_subjects_marks($subject_id,$student_id,$period,$term_id,$db) {
	$sql = "select COUNT(mp.exam_type_id)  from studentsmarks sm
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	INNER JOIN collegeperiods cp ON cp.id=sm.period_id
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	AND mp.exam_type_id=1
	AND rs.subject_id='$subject_id'
	AND rs.student_id='$student_id'
	AND cp.term_id='$term_id'
	AND rs.academic_year_id='$period'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$num_of_cats= $row[0];
	
	$sql = "select SUM(sm.marks) as cat_marks from studentsmarks sm
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	INNER JOIN collegeperiods cp ON cp.id=sm.period_id
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	AND mp.exam_type_id=1
	AND rs.subject_id='$subject_id'
	AND rs.student_id='$student_id'
	AND cp.term_id='$term_id'
	AND rs.academic_year_id='$period'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$cat_marks= $row[0];
	
	if($num_of_cats > 0){
	$average_marks=$cat_marks/$num_of_cats;
	}
	else{
	$average_marks='';
	}
	
	$sql = "select SUM(sm.marks) as cat_marks from studentsmarks sm
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	INNER JOIN collegeperiods cp ON cp.id=sm.period_id
	INNER JOIN years yr ON yr.id=cp.year
	AND mp.exam_type_id !=1
	AND rs.subject_id='$subject_id'
	AND rs.student_id='$student_id'
	AND cp.term_id='$term_id'
	AND rs.academic_year_id='$period'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$exam_marks= $row[0];
		
	$real_marks=$exam_marks+$average_marks;
	
	if($real_marks > 0){
	$real_marks=number_format($real_marks,0);
	}
	else{
	$real_marks='';
	}
	
	return $real_marks;
}

class subject {

var $subject_id;
var $subject_code;
var $subject_name;
var $course_id;
var $units;

function subject($subject_id,$db) {
	$sql = "select * from subjects where id = '$subject_id' limit 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->subject_id = $subject_id;
	$this->subject_name = $row['subject_name'];
	$this->subject_code = $row['subject_code'];
	$this->units = $row['units'];
	$this->course_id = $row['course_id'];
}


function set_calendar_vars($calendar_id,$db) {
	$sql = "select * from registered_students where id='$calendar_id' LIMIT 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->calendar_id = $calendar_id;
}


}

 class scheduled extends subject {
	var $calendar_id;
	var $start_date;
	var $subject_name;			//array containing the number of users in different status.
	var $total_users;
	var $subject_id;	
	var $cancelled;
	var $asterik;
	var $total_marks;

	function scheduled($subject_id,$db) {
		$this->subject($subject_id,$db);
	}

	

	function set_calendar_vars($student_id,$period,$subject,$db) {
		$sql = "select * from registered_students  LIMIT 1";
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		$this->calendar_id = $calendar_id;
		$this->asterik = $row['asterik'];
		// set status var
		$terms = get_terms($period,$db);
		
		foreach ($terms as $trms=>$tm) {
			$marks=tep_get_subjects_marks($subject,$student_id,$period,$tm['id'],$db);
			$marks2=$marks2+$marks;
		   
		   
		}
		$this->total_marks = $marks2;
		
	}

}
class bus_report {
	var $subject;			//array of courses that are eligible for report
	var $start_date;
	var $end_date;
	var $consolidated_subjects;
	var $scheduled_subjects;			//courses included in $course that was scheduled within the given time


	function bus_report($student_id,$period, $db) {
		$this->subject = $this->get_subject($db);
		$this->consolidated_subjects = $this->get_consolidated_subjects($student_id,$period,$db);
		$this->scheduled_subjects = $this->get_scheduled_subjects($student_id,$period,$db);
	}

	function get_subject($db) {
		$subject_array = array();
		// build where clause to exclude courses by previous choices.
		
		$sql = "select id from subjects ";
		//echo $query;
		$result = DB_query($sql,$db);
		while ($row = DB_fetch_array($result)) {
			$student_array[] = $row['id'];
		}
		return $subject_array;
	}


	function get_scheduled_subjects($student_id,$period,$db) {
		$scheduled_subjects_array = array();
		$sql = "select rs.id, rs.subject_id from registered_students rs
		INNER JOIN debtorsmaster dm ON dm.id=rs.student_id
		WHERE rs.academic_year_id='$period'
		AND rs.student_id='$student_id'";
		//echo $query;
		$result = DB_query($sql,$db);
		if (DB_num_rows($result) > 0) {
			while ($row = DB_fetch_array($result)) {
				$scheduled_subjects_array[] = array('id' => $row['id'],
												'subject_id' => $row['subject_id']);
			}
			return $scheduled_subjects_array;
		}
		else
		{
			
			return $scheduled_subjects_array;
		}
	}
	function get_consolidated_subjects($student_id,$period,$db) {
		$scheduled_subjects_array = array();
		$sql = "select rs.id, rs.subject_id from registered_students rs
		INNER JOIN debtorsmaster dm ON dm.id=rs.student_id
		WHERE rs.academic_year_id='$period'
		AND rs.student_id='$student_id'
		GROUP BY rs.subject_id";
		//echo $query;
		$result = DB_query($sql,$db);
		if (DB_num_rows($result) > 0) {
			while ($row = DB_fetch_array($result)) {
				$scheduled_subjects_array[] = array('id' => $row['id'],
												'subject_id' => $row['subject_id']);
			}
			return $scheduled_subjects_array;
		}
		else
		{
			
			return $scheduled_subjects_array;
		}
	}
	
function total_marks2($student_id,$subject_id,$period_id,$db) {
$sql = "select COUNT(mp.exam_type_id)  from studentsmarks sm
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	WHERE mp.exam_type_id=1
	AND rs.subject_id='$subject_id'
	AND rs.student_id='$student_id'
	AND rs.academic_year_id='$period_id'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$num_of_cats= $row[0];
	
	
	$sql = "select SUM(sm.marks) as cat_marks from studentsmarks sm
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	WHERE  mp.exam_type_id=1
	AND rs.subject_id='$subject_id'
	AND rs.student_id='$student_id'
	AND rs.academic_year_id='$period_id'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$cat_marks= $row[0];
	
	if($num_of_cats > 0){
	$average_marks=$cat_marks/$num_of_cats;
	}
	else{
	$average_marks='';
	}
	
	
	$sql = "select SUM(sm.marks) as cat_marks from studentsmarks sm
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	WHERE mp.exam_type_id !=1
	AND rs.subject_id='$subject_id'
	AND rs.student_id='$student_id'
	AND rs.academic_year_id='$period_id'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_row($result);
	$exam_marks= $row[0];
		
	$real_marks=$exam_marks+$average_marks;
	
	if($real_marks > 0){
	$real_marks=number_format($real_marks,0);
	}
	else{
	$real_marks='';
	}
	
	return $real_marks;
		}
	
function total_subject_marks($student_id,$period_id,$db) {
		$sql = "select SUM(sm.marks) as smarks from studentsmarks sm
		INNER JOIN registered_students rs ON rs.id=sm.calendar_id
		WHERE sm.student_id='$student_id'
		AND rs.academic_year_id='$period_id'";
		//echo $query; 
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		return $row['smarks'];
		}

 }

?>
