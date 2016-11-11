<?php
/* $Id: api_salesman.php 3237 2009-12-16 13:44:52Z tim_schofield $*/

/* This function returns a list of the stock salesman codes
 * currently setup on AIRADS System
 */

	function GetSalesmanList($user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT salesmancode FROM salesman';
		$result = DB_query($sql, $db);
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			$SalesmanList[$i]=$myrow[0];
			$i++;
		}
		return $SalesmanList;
	}

/* This function takes as a parameter a salesman code
 * and returns an array containing the details of the selected
 * salesman.
 */

	function GetSalesmanDetails($salesman, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT * FROM salesman WHERE salesmancode="'.$salesman.'"';
		$result = DB_query($sql, $db);
		if (DB_num_rows($result)==0) {
			$Errors[0]=NoSuchSalesMan;
			return $Errors;
		} else {
			$Errors[0]=0;
			$Errors[1]=DB_fetch_array($result);
			return $Errors;
		}
	}

/* This function takes as a parameter an array of salesman details
 * to be inserted into AIRADS System.
 */

	function InsertSalesman($SalesmanDetails, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}

		$FieldNames='';
		$FieldValues='';
		foreach ($SalesmanDetails as $key => $value) {
			$FieldNames.=$key.', ';
			$FieldValues.='"'.$value.'", ';
		}
		$sql = 'INSERT INTO salesman ('.substr($FieldNames,0,-2).') '.
		  'VALUES ('.substr($FieldValues,0,-2).') ';
		if (sizeof($Errors)==0) {
			$result = DB_Query($sql, $db);
			if (DB_error_no($db) != 0) {
				$Errors[0] = DatabaseUpdateFailed;
			} else {
				$Errors[0]=0;
			}
		}
		return $Errors;
	}

/* This function takes as a parameter a sales man name
 * and returns an array containing the details of the selected
 * salesman.
 */

	function GetSalesmanDetailsFromName($salesmanname, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT * FROM salesman WHERE salesmanname="'.$salesmanname.'"';
		$result = DB_query($sql, $db);
		if (DB_num_rows($result)==0) {
			$Errors[0]=NoSuchSalesMan;
			return $Errors;
		} else {
			$Errors[0]=0;
			$Errors[1]=DB_fetch_array($result);
			return $Errors;
		}
	}

?>