<?php
function getTelegramAvatar($username='')
{
  $username = $username;
  $username = str_replace('@', '', $username);
  $baseURL = 'https://telegram.me/';
  $pageURL = $baseURL . $username;
  $source = file_get_contents($pageURL);
  $dom_obj = new DOMDocument();
  $dom_obj->loadHTML($source);
  $avatar = false;
  foreach($dom_obj->getElementsByTagName('meta') as $meta) {
    if($meta->getAttribute('property')=='og:image'){
        $avatar = $meta->getAttribute('content');
    }
  }
  return $avatar;
}
$img = $_GET['img'];
// open the file in a binary mode
$name = getTelegramAvatar($img);
$fp = fopen($name, 'rb');

// send the right headers
header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
header('Expires: January 01, 2013'); // Date in the past
header('Pragma: no-cache');
header("Content-Type: image/jpg");
/* header("Content-Length: " . filesize($name)); */

// dump the picture and stop the script
fpassthru($fp);
exit;
?>