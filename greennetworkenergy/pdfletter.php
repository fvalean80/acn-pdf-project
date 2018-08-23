<?php

require_once('pdf_include.php');

function formatHTML($ehtml) {
   $formatHTML = str_replace('&lt;', '<', $ehtml);
   $formatHTML = str_replace('&gt;', '>', $formatHTML);
   $formatHTML = str_replace('border:0px;', 'border: none;', $formatHTML);
   $formatHTML = str_replace('border: 0px;', 'border: none;', $formatHTML);
   $formatHTML = preg_replace('/(\n|\r|\t)/i', '', $formatHTML);
   $formatHTML = preg_replace('/[ ]{2,1000}/i', '', $formatHTML);
   return $formatHTML;
}

switch($_SERVER['HTTP_ORIGIN']) {
       case 'http://cloud.comms.greennetworkenergy.co.uk':
             header('Access-Control-Allow-Origin: '.$_SERVER['HTTP_ORIGIN']);
             header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
             header('Access-Control-Max-Age: 1000');
             header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
             break;
}

// get POST variables
$dk = trim($_POST['d']);
$en = trim($_POST['e']);
$tit = trim($_POST['t']);
if(isset($_POST['n']) && strlen(trim($_POST['n'])) > 0) {
   $n = str_replace(' ', '-', trim($_POST['n']));
} else {
   $n = "PDF-Letter";
}

// get token
$url = 'https://auth.exacttargetapis.com/v1/requestToken';
$payload = array("clientId" => "cz53jbg0ycyj1dvnf78di8q3", "clientSecret" => "wXpWVvBBkKZ2tIWq73R9sRqV");
$ch = curl_init($url);
$postString = http_build_query($payload, '', '&');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);
$accessTokenResult = json_decode($response, true);
$accessToken = $accessTokenResult["accessToken"]; // token
$headers = array("Content-type: application/json", "Authorization:Bearer $accessToken");

// get email legacy id 
$urlEM = 'https://www.exacttargetapis.com/asset/v1/content/assets?$filter=Name%20eq%20\''.str_replace(' ', '%20', $en)."'";
$ch = curl_init($urlEM);
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$emJson = curl_exec($ch);
curl_close($ch);
$emJsonDecoded = json_decode($emJson, true);
$eid = $emJsonDecoded["items"][0]["data"]["email"]["legacy"]["legacyId"];
	
// get all records from data extension
$urlDE = "https://www.exacttargetapis.com/data/v1/customobjectdata/key/$dk/rowset?".'$fields=ContactId';
$ch = curl_init($urlDE);
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$rowset = curl_exec($ch);
curl_close($ch);
$rowsetDecoded = json_decode($rowset, true);
$items = $rowsetDecoded["items"];

if(count($items) > 0 && $eid > 0) {
   foreach($items as $ik) {
           // get email html source code
           $key = $ik["keys"]["contactid"];
		   
		   if($key) {
	header("HTTP/1.1 200 OK");
	echo "KEY: $key";
	exit(0);
} else {
	header("HTTP/1.1 200 OK");
	echo "KEY failure";
	exit(0);
}
		   
           $urlPreview = "https://www.exacttargetapis.com/guide/v1/emails/$eid/dataExtension/key:$dk/contacts/key:$key/preview?kind=html";
           $ch = curl_init($urlPreview);
           curl_setopt($ch, CURLOPT_POST, 1);
           curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
           curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
           $preview = curl_exec($ch);
           curl_close($ch);
           $emailHTMLSource = json_decode($preview, true);
           $emailHTML = trim($emailHTMLSource["message"]["views"][0]["content"]); // email html source
           $emailHTML = formatHTML($emailHTML);
           // generate PDF
           $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
           $pdf->SetCreator(PDF_CREATOR);
           $pdf->SetAuthor('GNE');
           $pdf->SetTitle($tit);
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
           $pdf->deletePage(2);
           $pdfName = $n."-".md5("WildCard$ik")."-".time().".pdf";
           $pdf->Output(getcwd()."/$pdfName", 'F');
           
           // write PDF file to SFTP
           if(file_exists($pdfName)) {
              $f = fopen($pdfName, 'r');
              $conn = ftp_connect("medops.net");
              $login = ftp_login($conn, "florin@medops.net", "Gazeluta2016!");
              ftp_chdir($conn, "PDF-LETTERS");
              ftp_fput($conn, $pdfName, $f, FTP_BINARY);
              ftp_close($conn);
              fclose($f);
              unlink($pdfName);
           }
   }
   header("HTTP/1.1 200 OK");
   echo $rowsetDecoded["count"];
} else {
   header("HTTP/1.1 401 ERROR");
   echo "0";
}

// delete files older than 5 minutes
$scanned_directory = scandir(getcwd());
foreach($scanned_directory as $itemk) {
        if(is_file($itemk) && mime_content_type($itemk) == "application/pdf" && time()-filemtime($itemk) > 300) {
           unlink($itemk);
        }
}
?>