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
$hashes=file('lasthash.txt');
foreach ($hashes as $key=>$hash)
   {
   $hashes[$key]=trim($hash);
   }
$hashes=array_reverse($hashes,TRUE);
if ($config["gdrive-clientid"])
   {
   define('GOOGLE_CLIENT_ID',$config['gdrive-clientid']);
   define('GOOGLE_CLIENT_EMAIL',$config['gdrive-clientemail']);
   define('GOOGLE_APPLICATION_SCOPE','https://www.googleapis.com/auth/drive');
   define('GOOGLE_APPLICATION_NAME','EIA Scraper');
   define('GOOGLE_KEY_FILE',$config['gdrive-keyfile']);
   require("vendor/autoload.php");
   require("helpers.class.php");
   $service=new Google_Service_Drive(getClient());
   $files=$service->files;
   }
if ($config["template"])
   {
   require_once 'PHPWord/src/PhpWord/Autoloader.php';
   \PhpOffice\PhpWord\Autoloader::register();
   $addresses=file($config["addresses"]);
   }

$mail=new PHPMailer;
$mail->isSMTP();                                      // Set mailer to use SMTP
$mail->Host = $config["smtp"];  // Specify main and backup SMTP servers
$mail->SMTPAuth = true;                               // Enable SMTP authentication
$mail->Username = $config["user"];                 // SMTP username
$mail->Password = $config["password"];                           // SMTP password
$mail->SMTPSecure = 'ssl';                            // Enable TLS encryption, `ssl` also accepted
$mail->Port = 465;                                    // TCP port to connect to
$mail->CharSet = 'UTF-8';
$mail->From = $config["from"];
$mail->FromName = $config["fromname"];
$mail->addAddress($config["to"]);     // Add a recipient
$mail->Subject = $config["subject"];

$filtercity=$config["filter"];

$html=file_get_html("http://www.enviroportal.sk/sk/eia/print");

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
      $projects[$i]["name"]=trim($project);
      $projects[$i]["link"]=trim($link);
      $projects[$i]["city"]=trim(preg_replace('!\s+!', ' ', $city));
      $projects[$i]["category"]=trim($category);
      $projects[$i]["status"]=trim($status);
      $htmlchild=file_get_html('http://www.enviroportal.sk'.$link);
      $projects[$i]["institution"]=trim($htmlchild->find('.table-list li',2)->getElementByTagName('span')->plaintext);
      $projects[$i]["proponent"]=trim($htmlchild->find('.table-list li',3)->getElementByTagName('span')->plaintext);
      $projects[$i]["proponentidnumber"]=trim($htmlchild->find('.table-list li',4)->getElementByTagName('span')->plaintext);
      $i++;
      }
   }

$mail->Body="";
$existingprojectstate=0;

foreach($projects as $project)
   {
   $mail->Subject = $config["subject"];
   $hash=trim(strtolower(md5($project["link"].$project["status"])));
   $hashkey=array_search($hash,$hashes);
   // arbitraty set number of existing (3) projects between already found project in the same state
   if ($hashkey AND $hashkey<count($hashes)-3) $existingprojectstate=1;
   //echo $project["name"],' ',$existingproject,' ',$hashkey;
   if (!$hashkey OR $existingprojectstate)
      {
      file_put_contents('lasthash.txt',$hash."\n",FILE_APPEND);
      $mail->Body=$project["name"]."\n";
      $mail->Body.=$project["city"]."; ".$project["category"]."\n";
      $mail->Body.=$project["status"]."\n";
      if ($existingprojectstate) $mail->Body.="Zmena / doplnené dokumenty\n";
      $mail->Body.=$project["institution"]."\n";
      $mail->Body.="http://www.enviroportal.sk".$project["link"]."\n";
      $mail->Subject.=" ".$project["name"];
      if ($config["template"] AND !$existingprojectstate)
         {
         unset($institutionkey);
         $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($config["template"]);
         foreach($addresses as $key=>$value)
            {
            if (strpos(mb_strtolower($value,'UTF-8'),mb_strtolower($project["institution"],'UTF-8'))!==FALSE)
               {
               $institutionkey=$key;
               break;
               }
            }
         if ($institutionkey)
            {
            $addressline=explode("|",$addresses[$institutionkey]);
            $templateProcessor->setValue('urad',$addressline[0]);
            $templateProcessor->setValue('urad-ulica',$addressline[2]);
            $templateProcessor->setValue('urad-mesto',$addressline[4].' '.$addressline[3]);
            }
         $templateProcessor->setValue('projekt',$project["name"]);
         $templateProcessor->setValue('navrhovatel',$project["proponent"]);
         $templateProcessor->setValue('ico',$project["proponentidnumber"]);
         $filename=uniqid().".docx";
         $templateProcessor->saveAs($config["tempdir"].$filename);
         echo 'File generated';
         }
      if ($config["gdrive-clientid"] AND !$existingprojectstate)
         {
         $newfile = new Google_Service_Drive_DriveFile();
         $newfile->setTitle($config["subject"]." ".$project["name"]);
         $newfile->setMimeType('application/vnd.openxmlformats-officedocument.wordprocessingml.document');
         $newfile->setWritersCanShare(true);
         $parent = new Google_Service_Drive_ParentReference();
         $parent->setId($config["gdrive-folderid"]);
         $newfile->setParents(array($parent));
         $additionalparams = array(
                 'data' => file_get_contents($config["tempdir"].$filename),
                 'mimeType' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                 'uploadType' => 'multipart',
                 'convert' => true
             );
         $createdfile=$files->insert($newfile,$additionalparams);
         $createdfileid=$createdfile->getId();
         $options=array('sendNotificationEmails'=>false);
         $newpermission=new Google_Service_Drive_Permission();
         $newpermission->setValue($config["to"]);
         $newpermission->setType("user");
         $newpermission->setRole("writer");
         $service->permissions->insert($createdfileid,$newpermission,$options);
         if (is_array($config["gdrive-users"]))
            {
            foreach($config["gdrive-users"] as $useremail)
               {
               $newpermission->setValue($useremail);
               $newpermission->setType("user");
               $newpermission->setRole("writer");
               $service->permissions->insert($createdfileid,$newpermission,$options);
               }
            }
         $mail->Body.="\nEdit: https://docs.google.com/document/d/".$createdfileid."/edit\n";
         echo ', uploaded to GDrive';
         }
      if ($config["template"] AND !$existingprojectstate)
         {
         unlink($config["tempdir"].$filename);
         }
      if ($mail->send())
         {
         echo ', email '.$mail->Subject.' has been sent';
         }
      echo '<br />';
      }
   }

if (!$mail->Body)
   {
   echo 'nothing new';
   }

?>
</body>
</html>