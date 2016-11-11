<?php
function total_marks($student_id,$period,$subject_id,$db) {
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
function tep_get_students2($class,$period,$db) {
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
function tep_set_students2($debtorno,$period,$db) {
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
function tep_get_exam_mode2($db) {
	$sql = "select * from markingperiods order by id";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$status_array[] = array("id" => $row['id'],
				     "title" => $row['title']);
	}
	return $status_array;
}
function tep_get_exam_mode_marks2($stream,$marking_period_id, $calendar_id,$db) {

		$sql = "select marks from studentsmarks where
		exam_mode='$marking_period_id'
		and calendar_id='$calendar_id'";

	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	return $row['marks'];
}

class student2 {

var $debtorno;
var $name;
var $class_id;
var $course_id;
var $grade_level_id;

function student2($debtorno,$db) {
	$sql = "select * from debtorsmaster where id = '$debtorno' limit 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->debtorno = $debtorno;
	$this->name = $row['name'];
	$this->class_id = $row['class_id'];
	$this->course_id = $row['course_id'];
}


function set_calendar_vars2($stream,$calendar_id,$db) {
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

	function scheduled2($debtorno,$db) {
		$this->student2($debtorno,$db);
	}



	function set_calendar_vars2($stream,$calendar_id,$db) {
		$sql = "select * from registered_students where id='$calendar_id' LIMIT 1";
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		$this->calendar_id = $calendar_id;
		// set status var
		$subjects_array = tep_get_exam_mode2($db);
		foreach ($subjects_array as $r=>$s) {
			$this->exam_mode[] = array("id" => $s['id'],
								   "tmarks" => tep_get_exam_mode_marks2($stream,$s['id'], $this->calendar_id,$db));
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
		// build where clause to exclude courses by previous choices.

		$sql = "select debtorno from debtorsmaster ";
		//echo $query;
		$result = DB_query($sql,$db);
		while ($row = DB_fetch_array($result)) {
			$student_array[] = $row['debtorno'];
		}
		return $student_array;
	}


	function get_scheduled_students2($class,$period,$subject,$db) {


		$scheduled_students_array = array();
		$sql = "select rs.id, rs.student_id from registered_students rs,students_subject_marks ssm,debtorsmaster dm,classes cls
		WHERE rs.period_id='$period'
		AND dm.id=rs.student_id
		AND cls.id=rs.class_id
		AND ssm.student_id=dm.id
		AND ssm.subject_id='$subject'
		AND rs.subject_id='$subject'
		AND cls.id='$class'
		GROUP BY rs.student_id
		ORDER BY ssm.marks DESC";
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
		$sql = "select mean from subject_mean
		WHERE subject_id='$subject_id'
		AND stream='$class'
		AND period_id='$period_id'";
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		return $row['marks'];
		}

function total_marks($student_id,$period_id,$subject_id,$db) {
		$sql = "select marks from students_subject_marks
		WHERE subject_id='$subject_id'
		AND student_id='$student_id'
		AND period_id='$period_id'";
		$result = DB_query($sql,$db);
		$row = DB_fetch_array($result);
		return $row['marks'];
		}

 }

?>
