<?php
class MySQL {

   public static function connect() {
      $mysqli = new mysqli(
         "localhost",      // server
         "chris_master",   // username
         "1337jew.",       // password
         "chris_postdelay" // database
      );
      /* check connection */
      if ($mysqli->connect_errno) {
         printf("Connect failed: %s\n", $mysqli->connect_error);
         exit();
      }
      $mysqli->autocommit(TRUE);
      return $mysqli;
   }
   
   public static function getPosts($fb_user_id) {
      $mysqli = MySQL::connect();
      /* Select queries return a resultset */
      $return = "(";
      $fb_user_id = $mysqli->real_escape_string($fb_user_id);
      if ($result = $mysqli->query("SELECT * FROM `posts` WHERE `fb_user_id`=$fb_user_id AND `posted`=0 ORDER BY `time_to_post` ASC", MYSQLI_USE_RESULT)) {
         while ($row = $result->fetch_array(MYSQLI_ASSOC)){
            $return .= "{";
            $return .= "'id':\"{$row['id']}\", ";
            $return .= "'fb_user_id':\"{$row['fb_user_id']}\", ";
            $return .= "'time_to_post':\"{$row['time_to_post']}\", ";
            $return .= "'content':\"{$row['content']}\", ";
            $return .= "}, ";
         }
         /* free result set */
         $result->close();
      }
      $mysqli->close();
      if ($return == "(") {
         return "()";
      }
      
      $return = substr($return, 0, -2) . ")";
      return $return;

   }

   public static function set_token($fb_user_id) {
      $mysqli = MySQL::connect();

      $fb_user_id = $mysqli->real_escape_string($fb_user_id);
      $token = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
      // 32 bits for "time_low" 
      mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), 
      // 16 bits for "time mid"
      mt_rand( 0, 0xffff ), 
      // 16 bits for "time_hi_and_version",
      // four most significant bits holds version number 4
      mt_rand( 0, 0x0fff ) | 0x4000,
      // 16 bits, 8 bits for "clk_seq_hi_res"
      // 8 bits for "clk_seq_low",
      // two most significant bits hold zero and one for variant DCE1.1
      mt_rand( 0, 0x3fff ), 
      // 48 bits for "node"
      mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
      );
      
      $query = "DELETE FROM `tokens` WHERE `fb_user_id` = $fb_user_id";
      $result = $mysqli->query($query);
      if (!$result) {
         printf("%s\n", $mysqli->error);
         exit();
      }

      
      $query = "INSERT INTO `tokens` (`fb_user_id`, `token`) VALUES ($fb_user_id, \"$token\")";
      $result = $mysqli->query($query);

      if (!$result) {
         printf("%s\n", $mysqli->error);
         exit();
      }
   
      $mysqli->close();

      return $token;
   }


   
   public static function check_token($fb_user_id) {
      $mysqli = MySQL::connect();

      $fb_user_id = $mysqli->real_escape_string($fb_user_id);
      $token = $_COOKIE["$fb_user_id"];
      $token = $mysqli->real_escape_string($token);
      
      $query = "SELECT * FROM `tokens` WHERE `fb_user_id`=\"$fb_user_id\" AND `token`=\"$token\"";
      $result = $mysqli->query($query);

      if (!$result) {
         printf("%s\n", $mysqli->error);
         exit();
      } else {
         $row = $result->fetch_array(MYSQLI_ASSOC);
         if ($row["fb_user_id"] == $fb_user_id && $row["token"] == $token){
            $mysqli->close();
            return true;
         }
      }
   
      $mysqli->close();
      return false;
   }
   
   
   
   
   
}