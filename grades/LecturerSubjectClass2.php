<?php
function tep_get_students2($class,$db) {
	$sql = "select SUM(sm.marks) as totalmarks,dm.id as student_id,dm.debtorno,dm.dm.name from debtorsmaster dm 
	INNER JOIN studentsmarks sm ON sm.student_id=dm.id
	WHERE class_id='$class'
	GROUP BY sm.student_id
	order by dm.id ";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$students_array[] = array("id" => $row['id'],
					"debtorno" => $row['debtorno'],
				     "name" => $row['name']);
	}
	return $students_array;
}
function tep_set_students2($student_id,$period,$db) {
	$sql = "select id,student_id from registered_students 
	WHERE student_id='$student_id'
	AND period_id='$period'";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$ids[] = array("id" => $row['id'],
				     "student_id" => $row['student_id']);
	}
	
	return $ids;
}
function tep_get_exam_mode2($db) {
	$sql = "select * from markingperiods order by id";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$status_array[] = array("id" => $row['id'],
				     "title" => $row['title']);
	}
	return $status_array;
}
function tep_get_exam_mode_marks2($marking_period_id, $calendar_id,$db) {
	$sql = "select marks from studentsmarks where 
	exam_mode='$marking_period_id' and calendar_id='$calendar_id'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	return $row['marks'];
}

class student2 {

var $student_id;
var $admission_no;
var $name;
var $class_id;
var $course_id;
var $grade_level_id;

function student2($student_id,$db) {
	$sql = "select * from debtorsmaster where id = '$student_id' limit 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->student_id = $student_id;
	$this->admission_no= $row['debtorno'];
	$this->name = $row['name'];
	$this->class_id = $row['class_id'];
	$this->course_id = $row['course_id'];
}


function set_calendar_vars2($calendar_id,$db) {
	$sql = "select * from registered_students where id='$calendar_id' LIMIT 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->calendar_id = $calendar_id;
}


}

 class scheduled2 extends student2 {
	var $calendar_id;
	var $start_date;
	var $exam_mode;			//array containing the number of users in different status.
	var $total_users;
	var $cancelled;

	function scheduled2($student_id,$db) {
		$this->student2($student_id,$db);
	}

	

	function set_calendar_vars2($calendar_id,$db) {
		$sql = "select * from registered_students where id='$calendar_id' LIMIT 1";
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		$this->calendar_id = $calendar_id;
		// set status var
		$subjects_array = tep_get_exam_mode2($db);
		foreach ($subjects_array as $r=>$s) {
			$this->exam_mode[] = array("id" => $s['id'],
								   "tmarks" => tep_get_exam_mode_marks2($s['id'], $this->calendar_id,$db));
		}
	}

}
class bus_report2 {
	var $student;			//array of courses that are eligible for report
	var $start_date;
	var $end_date;

	var $scheduled_students;			//courses included in $course that was scheduled within the given time


	function bus_report2($class,$period,$subject,$db) {
		$this->student = $this->get_student2($db);

		$this->scheduled_students = $this->get_scheduled_students2($class,$period,$subject,$db);
	}

	function get_student2($db) {
		$student_array = array();
		
		
		$sql = "select id from debtorsmaster ";
		//echo $query;
		$result = DB_query($sql,$db);
		while ($row = DB_fetch_array($result)) {
			$student_array[] = $row['id'];
		}
		return $student_array;
	}


	function get_scheduled_students2($class,$period,$subject,$db) {
		

		$scheduled_students_array = array();
		$sql = "select rs.id, rs.student_id from registered_students rs
		INNER JOIN debtorsmaster dm ON dm.id=rs.student_id
		INNER JOIN classes cls ON cls.id=dm.class_id 
		WHERE period_id='$period'
		AND rs.subject_id='$subject'
		AND cls.id='$class'
		ORDER BY dm.debtorno";
		//echo $query;
		$result = DB_query($sql,$db);
		if (DB_num_rows($result) > 0) {
			while ($row = DB_fetch_array($result)) {
				$scheduled_students_array[] = array('id' => $row['id'],
												'student_id' => $row['student_id']);
			}
			return $scheduled_students_array;
		}
		else
		{
			
			return $scheduled_students_array;
		}
	}
	
function subject_meangrade2($subject_id,$period_id,$class,$db) {
		$sql = "select SUM(sm.marks) as submarks from studentsmarks sm
		INNER JOIN registered_students rs ON rs.id=sm.calendar_id
		INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
		WHERE rs.subject_id='$subject_id'
		AND mp.title NOT LIKE 'Transcript'
		AND rs.period_id='$period_id'
		AND rs.class_id='$class'";
		//echo $query; 
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		return $row['submarks'];
		}
	
function total_marks($student_id,$period_id,$subject_id,$db) {
		$sql = "select COUNT(sm.id) from studentsmarks sm
		INNER JOIN registered_students rs ON rs.id=sm.calendar_id
		INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
		WHERE rs.student_id='$student_id'
		AND mp.exam_type_id=1
		AND rs.period_id='$period_id'
		AND rs.subject_id='$subject_id'";
		//echo $query; 
		$result = DB_query($sql,$db);
		$myrow = DB_fetch_row($result);
		$no_of_cats =$myrow[0];
		
		
		
		$sql = "select SUM(sm.marks) as smarks from studentsmarks sm
		INNER JOIN registered_students rs ON rs.id=sm.calendar_id
		INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
		WHERE rs.student_id='$student_id'
		AND mp.exam_type_id=1
		AND rs.period_id='$period_id'
		AND rs.subject_id='$subject_id'";
		//echo $query; 
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		$cat_marks=$row['smarks'];
		
		$cat_average_marks=$cat_marks/$no_of_cats;
		
		$sql = "select SUM(sm.marks) as smarks from studentsmarks sm
		INNER JOIN registered_students rs ON rs.id=sm.calendar_id
		INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
		WHERE rs.student_id='$student_id'
		AND mp.exam_type_id !=1
		AND rs.period_id='$period_id'
		AND rs.subject_id='$subject_id'";
		//echo $query; 
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		$end_term_marks=$row['smarks'];
		
		$real_marks=$end_term_marks+$cat_average_marks;
		
		return number_format($real_marks,0);
		}

 }

?>
