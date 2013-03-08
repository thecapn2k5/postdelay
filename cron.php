<?php
require_once('AppInfo.php');
require_once('sdk/src/facebook.php');
require_once('MySQL.php');

// instantiate facebook class
$facebook = new Facebook(array(
   'appId'  => AppInfo::appID(),
   'secret' => AppInfo::appSecret(),
   'sharedSession' => true,
   'trustForwarded' => true,
   'oauth' => true,
));

try {
   $basic = $facebook->api('/me');
} catch (FacebookApiException $e) {
   if (!$facebook->getUser()) {
      echo "You're not online.<br>";
      //exit();
   }
}

$mysqli = MySQL::connect();

$query = "SELECT 
      `posts`.`id`,
      `posts`.`fb_user_id`,
      `posts`.`time_to_post`,
      `posts`.`content`,
      `timezones`.`timezone` 
 FROM `posts`, `timezones` 
WHERE `posts`.`posted`=0
  AND `timezones`.`fb_user_id` = `posts`.`fb_user_id`
  AND `posts`.`time_to_post` <= DATE_ADD(NOW(), INTERVAL 1 DAY)
";
$result = $mysqli->query($query);

if (!$result) {
   printf("%s\n", $mysqli->error);
   exit();
}

while ($row = $result->fetch_array(MYSQLI_ASSOC)){
   // catch vars
   $id = $row['id'];
   $fb_user_id = $row['fb_user_id'];
   $time_to_post = $row['time_to_post'];
   $content = $row['content'];
   $timezone = substr($row['timezone'], strpos($row['timezone'], " ")+1);

   date_default_timezone_set($timezone);
   if (date('Y-m-d H:i:s') >= $time_to_post) {
      echo "Post '{$content}' to {$fb_user_id}.<br>";
      // command to post this status to their timeline.
      
      /*
      $query = "UPDATE `posts` SET `posted`=1 WHERE `id`=$id";
      $mysqli->query($query);
      $result = $mysqli->query($query);
      */
   }
}
   
$mysqli->close();
echo "Success!";