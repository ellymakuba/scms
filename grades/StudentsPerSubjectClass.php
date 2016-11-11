<?php
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
function get_subjects($class,$period,$db) {
	$sql = "select DISTINCT(rs.subject_id),sub.id from registered_students rs
	INNER JOIN subjects sub ON sub.id=rs.subject_id
	WHERE rs.class_id='$class'
	AND rs.period_id='$period'";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$subjects_array[] = array("id" => $row['id'],
				     "subject_id" => $row['subject_id']);
	}
	
	return $subjects_array;
}
function get_streams($gradelevel,$period,$subject,$db) {
	$sql = "SELECT dm.id,dm.debtorno,dm.name FROM students_subject_marks ssm
	INNER JOIN debtorsmaster dm ON dm.id=ssm.student_id
	WHERE ssm.subject_id='$subject'
	AND ssm.period_id='$period'
	AND dm.grade_level_id='$gradelevel'
	ORDER BY sssm.marks DESC";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$streams_array[] = array("id" => $row['id'],
				     "name" => $row['name']);
	}
	
	return $streams_array;
}

function tep_get_exam_mode_marks2($marking_period_id, $calendar_id,$db) {
	$sql = "select marks from studentsmarks where 
	exam_mode='$marking_period_id' and calendar_id='$calendar_id'";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	return $row['marks'];
}

class stream {

var $name;
var $id;

function stream($id,$db) {
	$sql = "select * from classes where id = '$id' limit 1";
	$result = DB_query($sql,$db);
	$row = DB_fetch_array($result);
	$this->id = $id;
	$this->name = $row['class_name'];
}


}

 class scheduled2 extends stream {
	var $calendar_id;
	var $start_date;
	var $exam_mode;			//array containing the number of users in different status.
	var $total_users;
	var $cancelled;

	function scheduled2($id,$db) {
		$this->stream($id,$db);
	}

	

	function set_calendar_vars2($calendar_id,$db) {
		$sql = "select subm.*,cl.class_name from subject_mean subm
		INNER JOIN classes cl ON cl.id=subm.stream
		 where id='$calendar_id' LIMIT 1";
		$result = DB_query($sql,$db);
		while ($row = DB_fetch_array($result)) {
		$this->streams[] = array("mean" => $row['mean'],
								   "roll" => $row['roll'],
								   "class_name" => $row['class_name']);
		}						   
	}

}
class bus_report2 {
	var $student;			//array of courses that are eligible for report
	var $start_date;
	var $end_date;

	var $scheduled_students;			//courses included in $course that was scheduled within the given time


	function bus_report2($class,$period,$subject,$db) {
		$this->scheduled_streams = $this->get_scheduled_streams($class,$period,$subject,$db);
	}

	
	function get_scheduled_streams($class,$period,$subject,$db) {

		$scheduled_streams_array = array();
		$sql = "SELECT dm.debtorno,dm.name,ssm.id,ssm.marks FROM debtorsmaster dm
		INNER JOIN students_subject_marks ssm ON ssm.student_id=dm.id
		WHERE ssm.period_id='$period' 
		AND dm.grade_level_id='$class'";
		//echo $query;
		$result = DB_query($sql,$db);
		if (DB_num_rows($result) > 0) {
			while ($row = DB_fetch_array($result)) {
				$scheduled_streams_array[] = array('id' => $row['id'],
												'debtorno' => $row['debtorno'],
												'name' => $row['name'],
												'marks' => $row['marks']);
			}
			return $scheduled_streams_array;
		}
		else
		{
			
			return $scheduled_streams_array;
		}
	}
	


 }

?>
