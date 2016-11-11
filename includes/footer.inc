<?php

	echo '</td></table>';
	echo '</td>';
	echo '</tr>';
	echo '</table>';

	echo '</td></tr>';
/*Do the borders */

	echo '</table>';
	setlocale(LC_ALL,$_SESSION['Language']);
	if (!isset($DefaultClock)) {
		include('config.php');
	}
		
	echo '<table width="100%" id="footer">';

	echo '<tr>';
	echo '<td class="footer">';
 	echo '<br />';

	echo '</td></tr>';

	echo '<tr><td class="footer">'._('Webafriq Solutions'). ' ' . _('Copyright') . ' &copy;  '.date('Y').'</td><td class="footer">'  . ' '. _('We single you,Serve You & Support You.').'</td></tr>';

	

	echo '</table>';

	echo '</body>';
	echo '</html>';

?>