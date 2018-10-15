<<<<<<< HEAD
<?php 
// Include the main TCPDF library (search for installation path).
require_once('pdf_include.php');

$html = trim($_POST['html']);

if($html) {
   $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
   $pdf->SetCreator(PDF_CREATOR);
   $pdf->SetAuthor('GNE');
   $pdf->SetTitle('HTML to PDF');
// $pdf->SetHeaderData('', '', '', '');
   $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
   $pdf->SetMargins(PDF_MARGIN_LEFT, 0, PDF_MARGIN_RIGHT);
   $pdf->SetHeaderMargin(0);
   $pdf->SetFooterMargin(0);
// set auto page breaks
// $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
// set image scale factor
   $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
   $pdf->setPrintHeader(false);
   $pdf->setPrintFooter(false);
   $tagvs = array('p' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n'
=> 0)));
   $pdf->setHtmlVSpace($tagvs);
   $pdf->setCellHeightRatio(1.80);
   $pdf->AddPage();
   $pdf->writeHTML($html, false, false, false, false, '');
      //$pdf->deletePage(2);
   $pdf->Output('GNE_Welcome_Pack_Sample.pdf', 'I');
} else {
   echo "ERROR: The HTML code could not be parsed.";
}
=======
<?php 
// Include the main TCPDF library (search for installation path).
require_once('pdf_include.php');

$html = trim($_POST['html']);

if($html) {
   $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
   $pdf->SetCreator(PDF_CREATOR);
   $pdf->SetAuthor('GNE');
   $pdf->SetTitle('HTML to PDF');
// $pdf->SetHeaderData('', '', '', '');
   $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
   $pdf->SetMargins(PDF_MARGIN_LEFT, 0, PDF_MARGIN_RIGHT);
   $pdf->SetHeaderMargin(0);
   $pdf->SetFooterMargin(0);
// set auto page breaks
// $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
// set image scale factor
   $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
   $pdf->setPrintHeader(false);
   $pdf->setPrintFooter(false);
   $tagvs = array('p' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n'
=> 0)));
   $pdf->setHtmlVSpace($tagvs);
   $pdf->setCellHeightRatio(1.80);
   $pdf->AddPage();
   $pdf->writeHTML($html, false, false, false, false, '');
      //$pdf->deletePage(2);
   $pdf->Output('GNE_Welcome_Pack_Sample.pdf', 'I');
} else {
   echo "ERROR: The HTML code could not be parsed.";
}
>>>>>>> a9333d2d88b3d8c5c30ac7b48c1b11db419f1351
?>