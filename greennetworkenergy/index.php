<?php 
switch($_SERVER['HTTP_ORIGIN']) {
       case 'http://cloud.comms.greennetworkenergy.co.uk':
             header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
             header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
             header('Access-Control-Max-Age: 1000');
             header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
             break;
}
require_once('pdf_include.php');

$sk = trim($_POST['s']);
$title = trim($_POST['t']);
$html = trim($_POST['h']);
$html = str_replace("&lt;", "<", $html);
$html = str_replace("&gt;", ">", $html);
$html = str_replace("border: 0px;", "border: none;", $html);

if($html) {
   $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
   $pdf->SetCreator(PDF_CREATOR);
   $pdf->SetAuthor('GNE');
   $pdf->SetTitle($title);
// $pdf->SetHeaderData('', '', '', '');
   $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
   $pdf->SetMargins(PDF_MARGIN_LEFT, 0, PDF_MARGIN_RIGHT);
   $pdf->SetHeaderMargin(0);
   $pdf->SetFooterMargin(0);
// set auto page breaks
   $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
// set image scale factor
   $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
   $pdf->setPrintHeader(false);
   $pdf->setPrintFooter(false);
   $tagvs = array('p' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)));
   $pdf->setHtmlVSpace($tagvs);
   $pdf->setCellHeightRatio(1.80);
   $pdf->AddPage();
   $pdf->writeHTML($html, false, false, false, false, '');
      $pdf->deletePage(2);
   $pdfName = "PDF-File-{$sk}-".time().".pdf";
   $pdf->Output(getcwd()."/$pdfName", 'F');
   header("HTTP/1.1 200 OK");
   echo "https://pdf-generator-acn.herokuapp.com/greennetworkenergy/$pdfName";
} else {
   header("HTTP/1.1 400 ERROR");
}

// delete files older than 3 minutes
$scanned_directory = scandir(getcwd());
foreach($scanned_directory as $item) {
        if(is_file($item) && mime_content_type($item) == "application/pdf" && time()-filemtime($item) > 180) {
           unlink($item);
        }
}
?>