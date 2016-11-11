<?php
/* $Id: api_customertypes.php 3237 2009-12-16 13:44:52Z tim_schofield $*/

/* This function returns a list of the customer types
 * currently setup on AIRADS System
 */

	function GetCustomerTypeList($user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT typeid FROM debtortype';
		$result = DB_query($sql, $db);
		$i=0;
		while ($myrow=DB_fetch_array($result)) {
			$TaxgroupList[$i]=$myrow[0];
			$i++;
		}
		return $TaxgroupList;
	}

/* This function takes as a parameter a customer type id
 * and returns an array containing the details of the selected
 * customer type.
 */

	function GetCustomerTypeDetails($typeid, $user, $password) {
		$Errors = array();
		$db = db($user, $password);
		if (gettype($db)=='integer') {
			$Errors[0]=NoAuthorisation;
			return $Errors;
		}
		$sql = 'SELECT * FROM debtortype WHERE typeid="'.$typeid.'"';
		$result = DB_query($sql, $db);
		return DB_fetch_array($result);
	}
?>