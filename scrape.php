<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
</head>
<body>
<?php
require('config.php');
require('simple_html_dom.php');
require('PHPMailer/PHPMailerAutoload.php');
$hashes=file_get_contents('lasthash.txt');

$mail=new PHPMailer;
$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = $config["smtp"];  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = $config["user"];                 // SMTP username
$mail->Password = $config["password"];                           // SMTP password
$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 465;                                    // TCP port to connect to

$mail->From = $config["from"];
$mail->FromName = $config["fromname"];
$mail->addAddress($config["to"]);     // Add a recipient

$mail->Subject = $config["subject"];

$filtercity=$config["filter"];

/* CURL not used
$ch=curl_init();
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
*/

$html=file_get_html("http://www.enviroportal.sk/sk/eia/print?search[name]=&search[act]=&search[activity]=&search[country]=1&search[district]=101&search[state]=&search[crossborder_country]=&search[_token]=3e7c06fc2c9fd18ea7e969d387da10050707bdc6");

$i=0;
foreach($html->find('tr') as $line)
   {
   $project=$line->find('td',0)->plaintext;
   $link=$line->getElementByTagName('a')->href;
   $city=$line->find('td',2)->plaintext;
   $category=$line->find('td',3)->plaintext;
   $parts=explode("\n",$project);
   $project=$parts[0];
   $status=$parts[2];
   if (!$filtercity OR ($filtercity AND stripos($city,$filtercity)!==FALSE))
      {
      $projects[$i]["desc"]=trim($project);
      $projects[$i]["link"]=trim($link);
      $projects[$i]["city"]=trim(preg_replace('!\s+!', ' ', $city));
      $projects[$i]["category"]=trim($category);
      $projects[$i]["status"]=trim($status);
      $i++;
      }
   }


$mail->Body="";

foreach($projects as $project)
   {
   $hash=md5($project["link"].$project["status"]);
   if (stripos($hashes,$hash)===FALSE)
      {
      file_put_contents('lasthash.txt',$hash."\n",FILE_APPEND);
      $mail->Body.="===================================================================\n";
      $mail->Body.=$project["desc"]."\n";
      $mail->Body.=$project["city"]."; ".$project["category"]."\n";
      $mail->Body.=$project["status"]."\n";
      $mail->Body.="http://www.enviroportal.sk".$project["link"]."\n";
      }
   }

if ($mail->Body)
   {
   if(!$mail->send())
      {
      echo 'Message could not be sent.';
      echo 'Mailer Error: ' . $mail->ErrorInfo;
      }
   else
      {
      echo 'Message has been sent';
      }
   }
else
   {
   echo 'nothing new';
   }

?>
</body>
</html>