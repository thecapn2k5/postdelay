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

$query = "SELECT * FROM `posts` WHERE `posted`=0 AND time_to_post <= NOW()";
$mysqli->query($query);
$result = $mysqli->query($query);

if (!$result) {
   printf("%s\n", $mysqli->error);
   exit();
}

while ($row = $result->fetch_array(MYSQLI_ASSOC)){
   $id = $row['id'];
   $fb_user_id = $row['fb_user_id'];
   $content = $row['content'];

   echo "Post {$content} to {$fb_user_id}.<br>";
   /*
   // command to post this status to their timeline.
   
   $query = "UPDATE `posts` SET `posted`=1 WHERE `id`=$id";
   $mysqli->query($query);
   $result = $mysqli->query($query);
   */
}
   
$mysqli->close();
echo "Success!";