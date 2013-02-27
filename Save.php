<?php
require_once('AppInfo.php');
require_once('MySQL.php');

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1' && $_SERVER['REMOTE_ADDR'] != '::1') {
   header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
   exit();
}

if (isset($_GET['action'])) {
   $action = $_GET['action'];
   $vars = $_GET;
} elseif (isset($_POST['action'])) {
   $action = $_POST['action'];
   $vars = $_POST;
} else {
   $action = false;
   $vars = false;
}

if ($action == 'add') {
   $mysqli = MySQL::connect();

   if (!(MySQL::check_token($vars['fb_user_id']))) {
      exit();
   }

   $fb_user_id = $mysqli->real_escape_string($vars['fb_user_id']);
   $content = $mysqli->real_escape_string($vars['content']);
   $time_to_post = $mysqli->real_escape_string($vars['time_to_post']);
   
   $query = "INSERT INTO `posts` (`fb_user_id`, `content`, `time_to_post`) VALUES ($fb_user_id, \"$content\", \"$time_to_post\")";
   $result = $mysqli->query($query);

   if (!$result) {
      printf("%s\n", $mysqli->error);
      exit();
   }

   echo $mysqli->insert_id;
   $mysqli->close();
   
} elseif ($action == 'edit') {
   $mysqli = MySQL::connect();

   if (!(MySQL::check_token($vars['fb_user_id']))) {
      exit();
   }

   $id = $mysqli->real_escape_string($vars['id']);
   $fb_user_id = $mysqli->real_escape_string($vars['fb_user_id']);
   $column = $mysqli->real_escape_string($vars['column']);
   $value = $mysqli->real_escape_string($vars['value']);

   $query = "UPDATE `posts` SET `$column`=\"$value\" WHERE `fb_user_id`=$fb_user_id AND `id`=$id";
   $mysqli->query($query);
   $result = $mysqli->query($query);

   if (!$result) {
      printf("%s\n", $mysqli->error);
      exit();
   }
   $mysqli->close();
   echo "edit successful!";
   
} elseif ($action == 'delete') {
   $mysqli = MySQL::connect();

   if (!(MySQL::check_token($vars['fb_user_id']))) {
      exit();
   }

   $id = $mysqli->real_escape_string($vars['id']);
   $fb_user_id = $mysqli->real_escape_string($vars['fb_user_id']);

   $query = "DELETE FROM `posts` WHERE `fb_user_id`=$fb_user_id AND `id`=$id";
   $result = $mysqli->query($query);

   if (!$result) {
      printf("%s\n", $mysqli->error);
      exit();
   }
   
   $mysqli->close();
   echo "delete successful!";
   
} elseif ($action == 'update_timezone') {
   $mysqli = MySQL::connect();
   
   if (!(MySQL::check_token($vars['fb_user_id']))) {
      exit();
   }

   $fb_user_id = $mysqli->real_escape_string($vars['fb_user_id']);
   $timezone = $mysqli->real_escape_string($vars['timezone']);
   $delta = $mysqli->real_escape_string($vars['delta']);

   // delete any possible old ones
   $query = "DELETE FROM `timezones` WHERE `fb_user_id`=$fb_user_id";
   $mysqli->query($query);
   $result = $mysqli->query($query);
   if (!$result) {
      printf("%s\n", $mysqli->error);
      exit();
   }
   
   // add the time zone
   $query = "INSERT INTO `timezones` (`fb_user_id`, `timezone`, `delta`) VALUES ($fb_user_id, \"$timezone\", \"$delta\")";
   $result = $mysqli->query($query);
   if (!$result) {
      printf("%s\n", $mysqli->error);
      exit();
   }
   
   $mysqli->close();
   echo "update successful!";
   
}