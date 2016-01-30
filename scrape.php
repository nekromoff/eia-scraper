<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
</head>
<body>
<?php
ini_set('max_execution_time', 300);
require('config.php');
require('simple_html_dom.php');
require('rtf2text.php');
require('PHPMailer/PHPMailerAutoload.php');
$ch=curl_init();
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
   $rtfcontent="";
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
      // if screenshot API key exists, make lots of additional effort
      if ($config["screenshot-apikey"])
         {
         $content=$htmlchild->find('text');
         foreach ($content as $text)
            {
            if (strpos($text,'Oznámenie o predložení zámeru:')!==FALSE)
               {
               $infofile=$text->parent->parent->find('li',0)->find('a',0)->href;

               curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
               curl_setopt ($ch, CURLOPT_URL, 'https://www.enviroportal.sk'.$infofile);
               curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
               curl_setopt($ch, CURLOPT_HEADER, 1);
               curl_setopt($ch, CURLOPT_NOBODY, 1);
               $content=curl_exec ($ch);
               $contenttype=curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
               if ($contenttype=='application/rtf')
                  {
                  $rtfcontent=file_get_contents('https://www.enviroportal.sk'.$infofile);
                  break;
                  }
               elseif ($contenttype=='application/zip')
                  {
                  copy('https://www.enviroportal.sk'.$infofile,'tmpfile.zip');
                  $zip=new ZipArchive;
                  $zip->open('tmpfile.zip');
                  $zip->extractTo($config["tempdir"],array($zip->getNameIndex(0)));
                  $rtfcontent=file_get_contents($config["tempdir"].$zip->getNameIndex(0));
                  unlink($config["tempdir"].$zip->getNameIndex(0));
                  $zip->close();
                  break;
                  }
               }
            }
         // basic info RTF exists, extract exact location and pull a screenshot
         if ($rtfcontent)
            {
            $content=rtf2text($rtfcontent);
            $content=str_replace('?','',$content);
            $content=preg_replace_callback("/(&#[0-9]+;)/", function($m) { return mb_convert_encoding($m[1], "UTF-8", "HTML-ENTITIES"); }, $content);
            $content=preg_replace('{(.)\1+}u','$1',$content);
            $parsedtext=preg_split("/\r?\n/",$content);
            foreach ($parsedtext as $key=>$line)
               {
               $parsedtext[$key]=trim($line);
               }
            foreach ($parsedtext as $key=>$line)
               {
               if (!$line) unset($parsedtext[$key]);
               }
            $parsedtext=array_values($parsedtext);
            foreach ($parsedtext as $key=>$line)
               {
               if (stripos($line,'Miesto real')!==FALSE OR stripos($line,'Mesto real')!==FALSE)
                  {
                  break;
                  }
               }
            /*
            if (!$key) echo 'key not found';
            else echo 'key: ',$key,'<br />';
            */
            $location="";
            // loop over the lines following "Miesto realizácie" until the end to find land registry "parcela" number
            for ($i=$key+1;$i<=count($parsedtext);$i++)
               {
               if (strpos($parsedtext[$i],'parc.')!==FALSE OR strpos($parsedtext[$i],'p.')!==FALSE OR strpos($parsedtext[$i],'parcel')!==FALSE OR preg_match('/[0-9]{3,}\/[0-9]{1,}/',$parsedtext[$i],$matches)===1)
                  {
                  if (preg_match('/[0-9]{3,}\/[0-9]{1,}/',$parsedtext[$i],$matches)===1)
                     {
                     $location=$matches[0];
                     $location=trim($location);
                     break;
                     }
                  }
               }
            // loop over the lines following "Miesto realizácie" until the end to identify existing location
            if (!$location)
               {
               $ch=curl_init();
               curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
               curl_setopt($ch, CURLOPT_HEADER, 0);
               curl_setopt($ch, CURLOPT_NOBODY, 0);
               for ($i=$key+1;$i<=count($parsedtext);$i++)
                  {
                  $location=trim($parsedtext[$i]);
                  //geocoding
                  curl_setopt($ch, CURLOPT_URL, 'https://nominatim.openstreetmap.org/search?q='.urlencode($location).'&format=json');
                  $geocode=curl_exec($ch);
                  $json=json_decode($geocode);
                  // enforce slovakia, otherwise skip
                  if (isset($json[0]->lon) AND isset($json[0]->lat) AND (stripos($json[0]->display_name,'Slovakia')!==FALSE OR stripos($json[0]->display_name,'Slovensko')!==FALSE))
                     {
                     $long=$json[0]->lon;
                     $lat=$json[0]->lat;
                     break;
                     }
                  }
               }
            /*
            if (!$long AND !$lat) echo('location not identified');
            echo $location;
            */
            // find coords based on land registry "parcela" number, if not found by geocoding
            if ($location AND !$long AND !$lat)
               {
               $csv=file($config["landgps"]);
               foreach ($csv as $line)
                  {
                  if (strpos($line,$location)!==FALSE)
                     {
                     $parts=explode(';',$line);
                     $long=$parts[1];
                     $lat=$parts[2];
                     break;
                     }
                  }
               }
            $projects[$i]["long"]=$long;
            $projects[$i]["lat"]=$lat;
            }
         }
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
      // include screenshot in the email
      if ($config["screenshot-apikey"] AND isset($project["long"]) AND isset($project["lat"]))
         {
         $mapimage=imagecreatefrompng('http://api.screenshotmachine.com/?key='.$config["screenshot-apikey"].'&size=F&format=PNG&cacheLimit=0&timeout=1000&url=http%3A%2F%2Flabs.strava.com%2Fheatmap%2F%2315%2F'.$long.'%2F'.$lat.'%2Fblue%2Fbike');
         $mapimagenew=imagecreatetruecolor(579,708);
         imagecopy($mapimagenew,$mapimage,0,0,445,60,1024,708);
         imagepng($mapimagenew,$config['tempdir'].$hash.'.png');
         $mail->AddAttachment($config['tempdir'].$hash.'.png');
         imagedestroy($mapimagenew); imagedestroy($mapimage);
         }
      // if templating enabled, generate document
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
      if ($config["screenshot-apikey"] AND isset($project["long"]) AND isset($project["lat"]))
         {
         unlink($config["tempdir"].$filename);
         }
      $mail->clearAttachments();
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