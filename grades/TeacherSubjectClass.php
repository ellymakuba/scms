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
	$sql = "SELECT cl.id,cl.class_name FROM subject_mean subm
	INNER JOIN classes cl ON subm.stream=cl.id
	WHERE subm.grade_level_id='$gradelevel'
	AND subm.period_id='$period'
	AND subm.subject_id='$subject'
	ORDER BY subm.mean DESC";
	$result = DB_query($sql,$db);
	while ($row = DB_fetch_array($result)) {
		$streams_array[] = array("id" => $row['id'],
				     "class_name" => $row['class_name']);
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
		$sql = "SELECT subm.subject_id as id,subm.stream,subm.mean,subm.roll,cl.class_name,w.realname FROM subject_mean subm
		INNER JOIN classes cl ON cl.id=subm.stream
		INNER JOIN registered_students rs ON rs.subject_id=subm.subject_id
		INNER JOIN www_users w ON w.userid=rs.teacher
		WHERE subm.subject_id='$subject'
		AND rs.class_id=subm.stream
		AND rs.period_id=subm.period_id
		AND subm.period_id='$period'
		AND subm.stream='$class'
		GROUP BY rs.class_id";
		//echo $query;
		$result = DB_query($sql,$db);
		if (DB_num_rows($result) > 0) {
			while ($row = DB_fetch_array($result)) {
				$scheduled_streams_array[] = array('id' => $row['id'],
												'stream' => $row['stream'],
												'realname' => $row['realname'],
												'class_name' => $row['class_name'],
												'roll' => $row['roll'],
												'mean' => $row['mean']);
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
