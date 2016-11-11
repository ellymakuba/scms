// ----------------------------------------------------------------------
// Javascript form validation routines.
// Author: Stephen Poley
//
// Simple routines to quickly pick up obvious typos.
// All validation routines return true if executed by an older browser:
// in this case validation must be left to the server.
//
// Update Jun 2005: discovered that reason IE wasn't setting focus was
// due to an IE timing bug. Added 0.1 sec delay to fix.
//
// Update Oct 2005: minor tidy-up: unused parameter removed
//
// Update Jun 2006: minor improvements to variable names and layout
// ----------------------------------------------------------------------

var nbsp = 160;		// non-breaking space char
var node_text = 3;	// DOM text node-type
var emptyString = /^\s*$/ ;
var global_valfield;	// retain valfield for timer thread
// --------------------------------------------
//                  trim
// Trim leading/trailing whitespace off string
// --------------------------------------------

function trim(str)
{
  return str.replace(/^\s+|\s+$/g, '');
}


// --------------------------------------------
//                  setfocus
// Delayed focus setting to get around IE bug
// --------------------------------------------

function setFocusDelayed()
{
  global_valfield.focus();
}

function setfocus(valfield)
{
  // save valfield in global variable so value retained when routine exits
  global_valfield = valfield;
  setTimeout( 'setFocusDelayed()', 100 );
}


// --------------------------------------------
//                  msg
// Display warn/error message in HTML element.
// commonCheck routine must have previously been called
// --------------------------------------------

function msg(fld,     // id of element to display message in
             msgtype, // class to give element ("warn" or "error")
             message) // string to display
{
  // setting an empty string can give problems if later set to a 
  // non-empty string, so ensure a space present. (For Mozilla and Opera one could 
  // simply use a space, but IE demands something more, like a non-breaking space.)
  var dispmessage;
  if (emptyString.test(message)) 
    dispmessage = String.fromCharCode(nbsp);    
  else  
    dispmessage = message;

  var elem = document.getElementById(fld);
  elem.firstChild.nodeValue = dispmessage;  
  
  elem.className = msgtype;   // set the CSS class to adjust appearance of message
}

// --------------------------------------------
//            commonCheck
// Common code for all validation routines to:
// (a) check for older / less-equipped browsers
// (b) check if empty fields are required
// Returns true (validation passed), 
//         false (validation failed) or 
//         proceed (don't know yet)
// --------------------------------------------

var proceed = 2;  

function commonCheck    (valfield,   // element to be validated
                         infofield,  // id of element to receive info/error msg
                         required)   // true if required
{
  if (!document.getElementById) 
    return true;  // not available on this browser - leave validation to the server
  var elem = document.getElementById(infofield);
  if (!elem.firstChild) return true;  // not available on this browser 
  if (elem.firstChild.nodeType != node_text) return true;  // infofield is wrong type of node  

  if (emptyString.test(valfield.value)) {
    if (required) {
      msg (infofield, "error", "ERROR: required");  
      setfocus(valfield);
      return false;
    }
    else {
      msg (infofield, "warn", "");   // OK
      return true;  
    }
  }
  return proceed;
}

// --------------------------------------------
//            validatePresent
// Validate if something has been entered
// Returns true if so 
// --------------------------------------------

function validatePresent(valfield,   // element to be validated
                         infofield ) // id of element to receive info/error msg
{
  var stat = commonCheck (valfield, infofield, true);
  if (stat != proceed) return stat;

  msg (infofield, "warn", "");  
  return true;
}

// --------------------------------------------
//               validateEmail
// Validate if e-mail address
// Returns true if so (and also if could not be executed because of old browser)
// --------------------------------------------

function validateEmail  (valfield,   // element to be validated
                         infofield,  // id of element to receive info/error msg
                         required)   // true if required
{
  var stat = commonCheck (valfield, infofield, required);
  if (stat != proceed) return stat;

  var tfld = trim(valfield.value);  // value of field with whitespace trimmed off
  var email = /^[^@]+@[^@.]+\.[^@]*\w\w$/  ;
  if (!email.test(tfld)) {
    msg (infofield, "error", "ERROR: not a valid e-mail address");
    setfocus(valfield);
    return false;
  }

  var email2 = /^[A-Za-z][\w.-]+@\w[\w.-]+\.[\w.-]*[A-Za-z][A-Za-z]$/  ;
  if (!email2.test(tfld)) 
    msg (infofield, "warn", "Unusual e-mail address - check if correct");
  else
    msg (infofield, "warn", "");
  return true;
}


// --------------------------------------------
//            validateTelnr
// Validate telephone number
// Returns true if so (and also if could not be executed because of old browser)
// Permits spaces, hyphens, brackets and leading +
// --------------------------------------------

function validateTelnr  (valfield,   // element to be validated
                         infofield,  // id of element to receive info/error msg
                         required)   // true if required
{
  var stat = commonCheck (valfield, infofield, required);
  if (stat != proceed) return stat;

  var tfld = trim(valfield.value);  // value of field with whitespace trimmed off
  var telnr = /^\+?[0-9 ()-]+[0-9]$/  ;
  if (!telnr.test(tfld)) {
    msg (infofield, "error", "ERROR: not a valid telephone number. Characters permitted are digits, space ()- and leading +");
    setfocus(valfield);
    return false;
  }

  var numdigits = 0;
  for (var j=0; j<tfld.length; j++)
    if (tfld.charAt(j)>='0' && tfld.charAt(j)<='9') numdigits++;

  if (numdigits<6) {
    msg (infofield, "error", "ERROR: " + numdigits + " digits - too short");
    setfocus(valfield);
    return false;
  }

  if (numdigits>14)
    msg (infofield, "warn", numdigits + " digits - check if correct");
  else { 
    if (numdigits<10)
      msg (infofield, "warn", "Only " + numdigits + " digits - check if correct");
    else
      msg (infofield, "warn", "");
  }
  return true;
}

// --------------------------------------------
//             validateRange
// Validate range
// Returns true if OK 
// --------------------------------------------

function validateRange    (valfield,   // element to be validated
                         infofield,  // id of element to receive info/error msg
                         required,   // true if required
						 minval,	// allowed minimum value
						 maxval)   // allowed maximum value
{
	var stat = commonCheck (valfield, infofield, required);
	if (stat != proceed) return stat;
		var tfld = trim(valfield.value);
		//var ageRE = /^[0-9\.]{1,4}$/
		var ageRE = /[-+]?([0-9]*\.[0-9]+|[0-9]+)/
	if (!ageRE.test(tfld)) {
    	msg (infofield, "error", "ERROR: not a valid value");
	    setfocus(valfield);
    	return false;
  	}

  if (tfld>maxval){
	  msg (infofield, "warn", "Value above maximum range: "+maxval);
	  setfocus(valfield);
	  return false;
	}else{
		if (tfld<minval){
			msg (infofield, "warn", "Value below minimum range: "+minval);
			setfocus(valfield);
			return false;
		}else{  msg (infofield, "warn", "");
  }
	}
  return true;
}

// -----------------------------------------
//            commonCheck2
// Common code for checkbox validation routines to
// check for older / less-equipped browsers
// Returns true (validation passed) or
//         proceed (don't know yet)
// -----------------------------------------

var proceed = 2;  

function commonCheck2   (vfld,   // element to be validated
                         ifld)   // id of element to receive info/error msg
{
  if (!document.getElementById) 
    return true;  // not available on this browser - leave validation to the server
  var elem = document.getElementById(ifld);
  if (!elem.firstChild)
    return true;  // not available on this browser 
  if (elem.firstChild.nodeType != node_text)
    return true;  // ifld is wrong type of node  

  msg (ifld, "warn", "");  // clear any previous error message
  return proceed;
}


// -----------------------------------------
//            validateCheckbox
// Validate that the correct number of checkboxes has been checked.
// Returns true if valid (and also if could not be executed because 
// of old browser)
// -----------------------------------------

function validateCheckbox  (vfld,   // checkboxes to be validated
                            ifld,   // id of element to receive info/error msg
                            nr,     // number of checkboxes to be checked. >=2
                            cond)   // condition: -1 = less than or equal to nr
                                    //             0 = equal to nr (default)
                                    //             1 = greater than or equal to nr
{
  if (!nr || nr<1) { //modified for option button
    alert('Programming error in validateCheckbox: nr<2'); 
       // for nr=1 use radio buttons or validateConfirm
    return true;
  }
  if (!cond) cond = 0;

  var stat = commonCheck2(vfld, ifld);
  if (stat != proceed) return stat;

  // count how many boxes have been checked by the reader
  var count = 0;
  for (var j=0; j<vfld.length; j++)
     if (vfld[j].checked) count++;

  if (count==nr) return true;
  if (count<nr && cond==-1) return true;
  if (count>nr && cond==1)  return true;

  // if we get here then the validation has failed

  var suffix='';
  if (count>1) suffix='es';

  var errorMsg;

  if (count<nr) errorMsg = 'Only ' + count + ' radio' + suffix + ' checked: ' + nr + ' required';
  if (count>nr) errorMsg = '' + count + ' radio checked: maximum ' + nr + ' allowed';
  if (count==0) errorMsg = 'No option selected: ' + nr + ' required'; //modified for radio buttons

  msg (ifld, "error", errorMsg);
  return false;
}

// -----------------------------------------
//            validateSelect
// Validate that the an option has been selected.
// Returns true if valid (and also if could not be executed because 
// of old browser)
// -----------------------------------------

function validateSelect  (vfld,   // selectbox to be validated
                            ifld,   // id of element to receive info/error msg
                            required)   // condition: -1 = less than or equal to nr
                                    //             0 = equal to nr (default)
                                    //             1 = greater than or equal to nr
{
	var stat = commonCheck (vfld, ifld, required);
	if (stat != proceed) return stat;
//var selectedArray = new Array();
  var selObj = vfld;
  /*
  //for multiple select options
  var i;
  var count = 0;
  for (i=0; i<selObj.options.length; i++) {
    if (selObj.options[i].selected) {
      selectedArray[count] = selObj.options[i].value;
      count++;
    }
  }
  txtSelectedTablesObj.value = selectedArray;*/
	tfld=selObj.value
  if (tfld==""){
		msg (ifld, "warn", "Please select a choice: ");
	  	setfocus(valfield);
	  	return false;
	}else{
		msg (ifld, "warn", "");
		return true;
	}
	
}

// -----------------------------------------
//            validateConfirm 
// Usually one doesn't want to validate if 1 checkbox of a set has been
// checked, because in this case one would use radio buttons instead.
// But sometimes one wants a reader to check a single box to confirm that 
// he or she agrees to something. That is covered by this routine.
//
// Returns true if valid (and also if could not be executed because 
// of old browser)
// -----------------------------------------

function validateConfirm   (vfld,   // checkbox to be validated
                            ifld)   // id of element to receive info/error msg
{
  var stat = commonCheck2(vfld, ifld);
  if (stat != proceed) return stat;

  if (vfld.checked) return true;

  // if we get here then the validation has failed

  var errorMsg = 'Please read the above message and confirm you agree to it';

  msg (ifld, "error", errorMsg);
  return false;
}