<?php
class Numbers_Words
{
   
    function toWords($num, $locale) {
		global $rootpath;
		if ($locale=='') {
			$locale='en_US';
		}
//Phil mods to ensure correct translation used.		
		if (!file_exists('Numbers/Words/lang.' . $locale . '.php')){
        	//try to drop off the .utf8 suffix then recheck
        	if (strpos($locale,'.utf8')){
				$locale = substr($locale, 0,strpos($locale,'.utf8'));
			}
			if (!file_exists('Numbers/Words/lang.' . $locale . '.php')){
				$locale = substr($locale,0,2);
			}
			if (!file_exists('Numbers/Words/lang.' . $locale . '.php')){
				$locale = 'en_US';
			}
        }
       
		include_once("Numbers/Words/lang.$locale.php");
        $classname = "Numbers_Words_$locale";

        if (!class_exists($classname)) {
            return Numbers_Words::raiseError("Unable to include the Numbers/Words/lang.$locale.php file");
        }

        $methods = get_class_methods($classname);

        if (!in_array('toWords', $methods) && !in_array('towords', $methods)) {
            return Numbers_Words::raiseError("Unable to find toWords method in '$classname' class");
        }

        @$obj =new $classname;

        return trim($obj->toWords($num));
    }
    // }}}
    // {{{ toCurrency()
    /**
     * Converts a currency value to word representation (1.02 => one dollar two cents)
     * If the number has not any fraction part, the "cents" number is omitted. 
     *
     * @param  float   $num   A float/integer/string number representing currency value
     *
     * @param  string  $locale Language name abbreviation. Optional. Defaults to en_US.
     *
     * @param  string  $int_curr International currency symbol
     *                 as defined by the ISO 4217 standard (three characters).
     *                 E.g. 'EUR', 'USD', 'PLN'. Optional.
     *                 Defaults to $def_currency defined in the language class.
     *
     * @return string  The corresponding word representation
     *
     * @access public
     * @author Piotr Klaban <makler@man.torun.pl>
     * @since  PHP 4.2.3
     */
    function toCurrency($num, $locale = 'en_US', $int_curr = '') {
        $ret = $num;

        @include_once("Numbers/Words/lang.${locale}.php");

        $classname = "Numbers_Words_${locale}";

        if (!class_exists($classname)) {
            return Numbers_Words::raiseError("Unable to include the Numbers/Words/lang.${locale}.php file");
        }

        $methods = get_class_methods($classname);

        if (!in_array('toCurrencyWords', $methods) && !in_array('tocurrencywords', $methods)) {
            return Numbers_Words::raiseError("Unable to find toCurrencyWords method in '$classname' class");
        }

        @$obj =new $classname;

        if (strpos($num, '.') === false)
        {
          $ret      = trim($obj->toCurrencyWords($int_curr, $num));
        } else {
            $currency = explode('.', $num, 2);
            /* add leading zero */
            if (strlen($currency[1]) == 1) {
                $currency[1] .= '0';
            }
            $ret      = trim($obj->toCurrencyWords($int_curr, $currency[0], $currency[1]));
        }
        return $ret;
    }
    // }}}
    // {{{ getLocales()
    /**
     * Lists available locales for Numbers_Words
     *
     * @param  string  $int_curr International currency symbol
     * @param  mixed   string/array of strings $locale
     *                 Optional searched language name abbreviation.
     *                 Default: all available locales.
     *
     * @return array   The available locales (optionaly only the requested ones)
     * @author Piotr Klaban <makler@man.torun.pl>
     * @author Bertrand Gugger, bertrand at toggg dot com
     *
     * @access public
     * @static
     */
    function getLocales($locale = null) {
        $ret = array();
       	if (isset($locale) && is_string($locale)) {
       	    $locale = array($locale);
        }
        $dname = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'Words' . DIRECTORY_SEPARATOR;
        $dh=opendir($dname);
        if ($dh) {
            while ($fname = readdir($dh)) {
                if (preg_match('#^lang\.([a-z_]+)\.php$#i', $fname, $matches)) {
                    if (is_file($dname . $fname) && is_readable($dname . $fname) &&
                        (!isset($locale) || in_array($matches[1], $locale))) {
                        $ret[] = $matches[1];
                    }
                }
            }
            closedir($dh);
            sort($ret);
        }
        return $ret;
    }
    // }}}
    // {{{ raiseError()
   /**
    * Trigger a PEAR error
    *
    * To improve performances, the PEAR.php file is included dynamically.
    *
    * @param string error message
    */
    function raiseError($msg)
    {
        prnMsg($msg,'error');
    }
    // }}}
}

// }}}
?>
