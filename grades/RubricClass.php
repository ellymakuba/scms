<?php

function get_no_of_students($academic_year_id,$remark_id,$db) {
		$no_of_students_array = array();
		// build where clause to exclude courses by previous choices.
		
		$sql = "select COUNT(dm.debtorno) FROM academic_year_remarks ayr
		INNER JOIN debtorsmaster dm ON dm.id=ayr.student_id
		WHERE ayr.academic_year_id='$academic_year_id'
		AND ayr.comment_id='$remark_id'";
		$result = DB_query($sql,$db);
		$row = DB_fetch_row($result);
		$num_of_students=$row[0];
		return $num_of_students;
	}
function tep_get_Programs($db) {
	$sql = "select * FROM courses";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$programs_array[] = array("id" => $row['id'],
						"course_name" => $row['course_name'],
				     "course_code" => $row['course_code']);
	}
	return $programs_array;
}
function tep_get_students($class,$db) {
	$sql = "select dm.* from debtorsmaster dm 
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

class subject {

var $subject_id;
var $subject_code;
var $subject_name;
var $course_id;
var $units;

function program($program_id,$db) {
	$sql = "select * from courses where id = '$program_id' limit 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->id = $id;
	$this->course_name = $row['course_name'];
	$this->course_code = $row['course_code'];
	$this->department_id = $row['department_id'];
}


function set_calendar_vars($calendar_id,$db) {
	$sql = "select * from registered_students where id='$calendar_id' LIMIT 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->calendar_id = $calendar_id;
}


}

class student {

var $id;
var $student_reg_no;
var $student_name;

function student($student_id,$db) {
	$sql = "select * from debtorsmaster where id = '$student_id' limit 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->id = $row['id'];
	$this->student_name = $row['name'];
	$this->student_reg_no = $row['debtorno'];
}

}

 class students_in_program extends student {
	var $program_id;
	var $failed_courses;

	function students_in_program($student_id,$db) {
		$this->student($student_id,$db);
	}

	

	function tep_get_subjects($program,$period,$student_id,$db) {
	$sql = "select sub.* from subjects sub 
	INNER JOIN fails f ON f.course_id=sub.id
	INNER JOIN courses c ON c.id=sub.course_id
	WHERE f.student_id='$student_id'
	AND f.period_id='$period'
	AND sub.course_id='$program'";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$failed_courses[] = array("id" => $row['id'],
						"units" => $row['units'],
				     "subject_code" => $row['subject_code'],
					 "subject_name" => $row['subject_name']);
	}
	return $failed_courses;
}

}
class bus_report {
	var $no_of_students;
	var $students_in_program;			//courses included in $course that was scheduled within the given time


	function bus_report($program,$academic_year_id,$remark_id,$db) {

		$this->students_in_program = $this->get_students_in_program($program,$academic_year_id,$remark_id,$db);
	}

	


	function get_students_in_program($program,$academic_year_id,$remark_id,$db) {
		$students_in_program = array();
		$sql = "select dm.* FROM academic_year_remarks ayr
		INNER JOIN debtorsmaster dm ON dm.id=ayr.student_id
		WHERE dm.course_id='$program'
		AND ayr.academic_year_id='$academic_year_id'
		AND ayr.comment_id='$remark_id'";
		//echo $query;
		$result = DB_query($sql,$db);
		if (DB_num_rows($result) > 0) {
			while ($row = DB_fetch_array($result)) {
				$students_in_program[] = array('id' => $row['id'],
												'name' => $row['name'],
												'debtorno' => $row['debtorno']);
			}
			return $students_in_program;
		}
		else
		{
			
			return $students_in_program;
		}
	}
	


 }

?>
