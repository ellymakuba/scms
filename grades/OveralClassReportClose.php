<?php
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
function tep_get_subjects($class,$period,$db) {
	$sql = "select sub.* from subjects sub 
	INNER JOIN registered_students rs ON rs.subject_id=sub.id
	INNER JOIN collegeperiods cp ON cp.id=rs.period_id
	INNER JOIN years yr ON yr.id=cp.year
	WHERE rs.class_id='$class'
	AND yr.id='$period'
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
function tep_get_subjects_marks($subject_id,$class,$period,$calendar_id,$db) {
	$sql = "select SUM(sm.marks) as tmarks from studentsmarks sm
	INNER JOIN registered_students rs ON rs.id=sm.calendar_id
	INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
	INNER JOIN collegeperiods cp ON cp.id=sm.period_id
	INNER JOIN years yr ON yr.id=cp.year
	WHERE rs.id='$calendar_id'
	AND mp.title NOT LIKE 'Transcript'
	AND rs.subject_id='$subject_id'
	AND sm.calendar_id='$calendar_id'
	AND rs.class_id='$class'
	AND yr.id='$period'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	return $row['tmarks'];
}

class student {

var $student_id;
var $name;
var $class_id;
var $course_id;
var $grade_level_id;

function student($student_id,$db) {
	$sql = "select * from debtorsmaster where id = '$student_id' limit 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->student_id = $student_id;
	$this->name = $row['name'];
	$this->class_id = $row['class_id'];
	$this->course_id = $row['course_id'];
}


function set_calendar_vars($calendar_id,$db) {
	$sql = "select * from registered_students where id='$calendar_id' LIMIT 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->calendar_id = $calendar_id;
}


}

 class scheduled extends student {
	var $calendar_id;
	var $start_date;
	var $subject;			//array containing the number of users in different status.
	var $total_users;
	var $cancelled;

	function scheduled($student_id,$db) {
		$this->student($student_id,$db);
	}

	

	function set_calendar_vars($class,$period,$calendar_id,$db) {
		$sql = "select  id,total FROM annual_ranks 
		where id='$calendar_id' 
		AND academic_year_id='$period'
		AND class_id='$class' LIMIT 1";
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		$marks=$row['total'];
		return $marks;
	}

}
class bus_report {
	var $student;			//array of courses that are eligible for report
	var $start_date;
	var $end_date;

	var $scheduled_students;			//courses included in $course that was scheduled within the given time


	function bus_report($class,$period, $db) {
		$this->student = $this->get_student($db);

		$this->scheduled_students = $this->get_scheduled_students($class,$period,$db);
	}

	function get_student($db) {
		$student_array = array();
		// build where clause to exclude courses by previous choices.
		
		$sql = "select id from debtorsmaster ";
		//echo $query;
		$result = DB_query($sql,$db);
		while ($row = DB_fetch_array($result)) {
			$student_array[] = $row['id'];
		}
		return $student_array;
	}


	function get_scheduled_students($class,$period,$db) {
		

		$scheduled_students_array = array();
		$sql = "select id,student_id FROM annual_ranks
		WHERE class_id='".$class."'
		AND academic_year_id='".$period."'
		AND total > 249";
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
	
function total_marks($student_id,$period_id,$db) {
		$sql = "select SUM(sm.marks) as smarks from studentsmarks sm
		INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
		INNER JOIN collegeperiods cp ON cp.id=sm.period_id
		INNER JOIN years yr ON yr.id=cp.year
		WHERE sm.student_id='$student_id'
		AND mp.title NOT LIKE 'Transcript'
		AND yr.id='$period_id'";
		//echo $query; 
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		return $row['smarks'];
		}
	
function subject_meangrade($subject_id,$period_id,$class,$db) {
		$sql = "select SUM(sm.marks) as submarks from studentsmarks sm
		INNER JOIN registered_students rs ON rs.id=sm.calendar_id
		INNER JOIN markingperiods mp ON mp.id=sm.exam_mode
		INNER JOIN collegeperiods cp ON cp.id=sm.period_id
		INNER JOIN years yr ON yr.id=cp.year
		WHERE rs.subject_id='$subject_id'
		AND mp.title NOT LIKE 'Transcript'
		AND yr.id='$period_id'
		AND rs.class_id='$class'";
		//echo $query; 
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		return $row['submarks'];
		}

 }

?>
