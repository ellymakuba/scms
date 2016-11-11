<?php
$PageNumber++;
	if ($PageNumber>1){
		$pdf->newPage();
	}
$pdf->addJpegFromFile($_SESSION['LogoFile'] ,$XPos+230,$YPos-120,0,80);
$FontSize=24;
$YPos = $Page_Height - $Top_Margin;
$LeftOvers = $pdf->addTextWrap(150,$YPos-($line_height),300,$FontSize, _('AFRICAN INSTITUTE '));
$FontSize=10;
$LeftOvers = $pdf->addTextWrap(200,$YPos-($line_height*2.5),300,$FontSize, _('Of Research and Development Studies '));

$FontSize=8;
$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*5),300,$FontSize,_('Institute Plaza'));
$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*6),300,$FontSize,_('Next to kenya Power'));
$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*7),300,$FontSize,_('emergency office'));
$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*8),300,$FontSize,$_SESSION['CompanyRecord']['regoffice3']);
$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*9),300,$FontSize,$_SESSION['CompanyRecord']['regoffice4']);
$LeftOvers = $pdf->addTextWrap(40,$YPos-($line_height*10),300,$FontSize,$_SESSION['CompanyRecord']['regoffice6']);
$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos-($line_height*5),140,$FontSize, _('Tel').': ' . $_SESSION['CompanyRecord']['regoffice2']);

$LeftOvers = $pdf->addTextWrap($Page_Width-$Right_Margin-150,$YPos-($line_height*6),300,$FontSize, _('website').': ' . $_SESSION['CompanyRecord']['regoffice1']);

$FontSize=16;
$LeftOvers = $pdf->addTextWrap(50,$YPos-($line_height*14),180,$FontSize,_('Course').': '.$course_name);
$LeftOvers = $pdf->addTextWrap(50,$YPos-($line_height*15),180,$FontSize,_('Grade Level').': '.$grade_level);	
$LeftOvers = $pdf->addTextWrap(296,$YPos-($line_height*15),180,$FontSize,_('Period').': '.$period_name);	
 $LeftOvers = $pdf->addTextWrap(250,$YPos-($line_height*12.3),45,$FontSize,'______________________________________________________________________________');

?>