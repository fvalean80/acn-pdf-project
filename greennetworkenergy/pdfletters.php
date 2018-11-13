<?php
error_reporting(0);
ini_set('display_errors', 'off');
session_start();

require_once('pdf_include.php');

$dk = "TestPDF_de"; // data extension external key
$eid = 8338; // email id
$batchSize = 2; // to return 2 records per query
$batchToken = 0; // start with the first record ordered by row number

switch($_SERVER['HTTP_ORIGIN']) {
       case 'http://cloud.comms.greennetworkenergy.co.uk':
             header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
             header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
             header('Access-Control-Max-Age: 1000');
             header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
             break;
}

// function to generate pdf files
function generatePDF($contactkey) {
   $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
   $pdf->SetCreator(PDF_CREATOR);
   $pdf->SetAuthor('GNE');
   $pdf->SetTitle("PDF Letter");
   $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
   $pdf->SetMargins(PDF_MARGIN_LEFT, 0, PDF_MARGIN_RIGHT);
   $pdf->SetHeaderMargin(0);
   $pdf->SetFooterMargin(0);
   $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
   $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
   $pdf->setPrintHeader(false);
   $pdf->setPrintFooter(false);
   $tagvs = array('p' => array(0 => array('h' => 0, 'n' => 0), 1 => array('h' => 0, 'n' => 0)));
   $pdf->setHtmlVSpace($tagvs);
   $pdf->setCellHeightRatio(1.80);
   $pdf->AddPage();
   $pdf->writeHTML($emailHTML, true, false, true, false, '');
   $pdfName = "PDF-Letter-$contactKey-".time().".pdf";
   // $pdf->Output(getcwd()."/$pdfName", 'F');   
}

$url = 'https://auth.exacttargetapis.com/v1/requestToken'; // get OAuth token to connect to Salesforce Marketing Cloud
$payload = array("clientId" => "cz53jbg0ycyj1dvnf78di8q3", "clientSecret" => "wXpWVvBBkKZ2tIWq73R9sRqV"); // client ID and client secret have been setup in Salesforce Marketing Cloud
$ch = curl_init($url);
$postString = http_build_query($payload, '', '&');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$accessTokenResult = json_decode($response, true);
$accessToken = $accessTokenResult["accessToken"]; // token valid for 1h
$headers = array("Content-type: application/json", "Authorization:Bearer $accessToken");

$count = 0;
$iteration = true;

while($iteration) {

      // get all records from data extension
      $urlDE = "https://www.exacttargetapis.com/data/v1/customobjectdata/key/$dk/rowset?".'$fields=ContactId,RowNumber&$filter=RowNumber%20gt%20'.$batchToken.'&$page=1&$pagesize='.$batchSize.'&$orderBy=RowNumber';
      $ch = curl_init($urlDE);
      curl_setopt($ch, CURLOPT_POST, 0);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $rowset = curl_exec($ch);
      curl_close($ch);
      $rowsetDecoded = json_decode($rowset, true);
      $items = $rowsetDecoded["items"];

      if(count($items) > 0) {
         foreach($items as $ik) {
                 // get email html source code
                 $key = $ik["keys"]["contactid"];
                 $batchToken = $ik["values"]["rownumber"];
                 $urlPreview = "https://www.exacttargetapis.com/guide/v1/emails/$eid/dataExtension/key:$dk/contacts/key:$key/preview?kind=html";
                 $ch = curl_init($urlPreview);
                 curl_setopt($ch, CURLOPT_POST, 1);
                 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                 $preview = curl_exec($ch);
                 curl_close($ch);
                 $emailHTMLSource = json_decode($preview, true);
                 $emailHTML = trim($emailHTMLSource["message"]["views"][0]["content"]); // email html source
                 
                 // generatePDF($key);
                 // pdf output is disabled to avoid writing files on Heroku
                 
                 $count++;  
         } 
      } else {
         $iteration = false;
      }
}

echo $count;
?>