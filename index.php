<?php
require_once('AppInfo.php');
require_once('utils.php');
require_once('sdk/src/facebook.php');
require_once('MySQL.php');

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1' && $_SERVER['REMOTE_ADDR'] != '::1') {
   header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
   exit();
}

// instantiate facebook class
$facebook = new Facebook(array(
   'appId'  => AppInfo::appID(),
   'secret' => AppInfo::appSecret(),
   'sharedSession' => true,
   'trustForwarded' => true,
   'oauth' => true,
));

// local vars
$user_id = $facebook->getUser();
$chris_user_id = "38414094";
$app_user_id = false;

// check if i'm connected, but only if i'm on my local box
$connected = true;
if ($_SERVER['REMOTE_ADDR'] == '127.0.0.1' || $_SERVER['REMOTE_ADDR'] == '::1') {
   $connected = @fsockopen("www.google.com", 80);
   if ($connected){
      fclose($connected);
   }else{
      $connected = false;
      $app_user_id = $chris_user_id;
   }
}

if ($user_id && $connected) {
   // use the facebook user id as the app user id
   $app_user_id = $user_id;

   try {
      $basic = $facebook->api('/me');
   } catch (FacebookApiException $e) {
      if (!$facebook->getUser()) {
         header('Location: '. AppInfo::getUrl($_SERVER['REQUEST_URI']));
         exit();
      }
   }

   // To see the format of the data you are retrieving, use the "Graph API Explorer" (https://developers.facebook.com/tools/explorer/)
   //$likes = idx($facebook->api('/me/likes?limit=4'), 'data', array());
   //$friends = idx($facebook->api('/me/friends?limit=4'), 'data', array());
   //$photos = idx($facebook->api('/me/photos?limit=16'), 'data', array());
   //$app_using_friends = $facebook->api(array('method' => 'fql.query', 'query' => 'SELECT uid, name FROM user WHERE uid IN(SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1'));
}

if ($connected) {
   $app_info = $facebook->api('/'. AppInfo::appID());
   $app_name = idx($app_info, 'name', '');
} else {
   $app_name = "OFFLINE";
}

// set cookies for auth
$expire = time()+60*60*24; // 1 day
$token = MySQL::set_token($app_user_id);
setcookie($app_user_id, $token, $expire);
?>
<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="en">
   <head>
      <meta charset="utf-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes" />

      <title><?php echo he($app_name); ?></title>
      <link rel="stylesheet" href="stylesheets/jquery-ui.css" media="Screen" type="text/css" />
      <link rel="stylesheet" href="stylesheets/screen.css" media="Screen" type="text/css" />
      <link rel="stylesheet" href="stylesheets/mobile.css" media="handheld, only screen and (max-width: 480px), only screen and (max-device-width: 480px)" type="text/css" />

      <!--[if IEMobile]>
      <link rel="stylesheet" href="mobile.css" media="screen" type="text/css"  />
      <![endif]-->

      <!-- These are Open Graph tags.  They add meta data to your  -->
      <!-- site that facebook uses when your content is shared     -->
      <!-- over facebook.  You should fill these tags in with      -->
      <!-- your data.  To learn more about Open Graph, visit       -->
      <!-- 'https://developers.facebook.com/docs/opengraph/'       -->
      <meta property="og:title" content="<?php echo he($app_name); ?>" />
      <meta property="og:type" content="website" />
      <meta property="og:url" content="<?php echo AppInfo::getUrl(); ?>" />
      <meta property="og:image" content="<?php echo AppInfo::getUrl('images/icon.ico'); ?>" />
      <meta property="og:site_name" content="<?php echo he($app_name); ?>" />
      <meta property="og:description" content="My first app" />
      <meta property="fb:app_id" content="<?php echo AppInfo::appID(); ?>" />

      <script type="text/javascript" src="javascript/jquery-1.7.1.min.js"></script>
      <script type="text/javascript" src="javascript/jquery-ui.js"></script>
      <script type="text/javascript" src="javascript/jquery.autogrowtextarea.js"></script>
      <script type="text/javascript" src="javascript/jquery-ui-timepicker-addon.js"></script>

      <script type="text/javascript">
         function logResponse(response) {
            if (console && console.log) {
               console.log('The response was', response);
            }
         }

         $(function(){
            // Set up so we handle click on the buttons
            $('#postToWall').click(function() {
               FB.ui(
               {
                  method : 'feed',
                  link   : $(this).attr('data-url')
               },
               function (response) {
                  // If response is null the user canceled the dialog
                  if (response != null) {
                     logResponse(response);
                  }
               });
            });

         $('#sendToFriends').click(function() {
            FB.ui(
            {
               method : 'send',
               link   : $(this).attr('data-url')
            },
            function (response) {
               // If response is null the user canceled the dialog
               if (response != null) {
                  logResponse(response);
               }
            });
         });

         $('#sendRequest').click(function() {
            FB.ui(
            {
               method  : 'apprequests',
               message : $(this).attr('data-message')
            },
            function (response) {
               // If response is null the user canceled the dialog
               if (response != null) {
                  logResponse(response);
               }
            });
         });
      });
    </script>

      <!--[if IE]>
         <script type="text/javascript">
            var tags = ['header', 'section'];
            while(tags.length)
               document.createElement(tags.pop());
         </script>
      <![endif]-->
   </head>
   <body>
      <div id="fb-root"></div>
      <script type="text/javascript">
      $(document).ready(function(){
         window.fbAsyncInit = function() {
            FB.init({
               appId      : '<?php echo AppInfo::appID(); ?>', // App ID
               channelUrl : '//<?php echo $_SERVER["HTTP_HOST"]; ?>/channel.html', // Channel File
               status     : true, // check login status
               cookie     : true, // enable cookies to allow the server to access the session
               xfbml      : true // parse XFBML
            });
		
            (function() {
               var e = document.createElement('script');
               e.async = true;
               e.src = document.location.protocol + '//connect.facebook.net/en_US/all.js';
               document.getElementById('fb-root').appendChild(e);
            }());
		
            FB.Event.subscribe('auth.login', function(response) {
               window.location = window.location;
            });

            FB.Canvas.setAutoGrow();
         };
         loadPosts();

         $("#newpost .datetimepicker").datetimepicker({
            dateFormat: 'yy-mm-dd',
         	timeFormat: 'HH:mm',
            stepMinute: 10,
            sliderAccessArgs: { touchonly: false },
         });
            
         // default is this time tomorrow
         var now = new Date();
         now.setDate(now.getDate()+1);
         $("#newpost .datetimepicker").datetimepicker('setDate', now);
         
         // changing timezone
         $('#timezone').change(function() {
            var timezone = "";
            var delta = 0;
            $("#timezone option").each(function(){
               if ($(this).attr('selected')) {
                  timezone = $(this).text();
                  delta = $(this).val();
               }
            });
            $.post('Save.php', {
               'action':'update_timezone',
               'fb_user_id':"<?php echo $app_user_id ?>",
               'timezone':timezone,
               'delta':delta
            });
            
         });
         
         // save function
         $("#savebutton").click(function(){
            // catch the content
            var content = $('#newpost .posttextarea').val();
               
            if (content) {
               // reset the content
               $('#newpost .posttextarea').val("");

               // catch and reset the time_to_post
               var time_to_post = $("#newpost .datetimepicker").val()+":00";
               var now = new Date();
               now.setDate(now.getDate()+1);
               $("#newpost .datetimepicker").datetimepicker('setDate', now);

               // save and reload the posts
               $.post('Save.php', {
                  'action':'add',
                  'fb_user_id':"<?php echo $app_user_id ?>",
                  'time_to_post':time_to_post,
                  'content':content
               }, function(data) {
                  window.location = window.location;
                  // This is just a quick fix so the stupid thing loads.
                  //$("#posts .wrapper").remove();
                  //loadPosts();
               });
            }
         });

      });

      // Load the SDK Asynchronously
      (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
      
      function loadPosts() {
         var posts = new Array 
         <?php
            if ($app_user_id) {
               echo MySQL::getPosts($app_user_id);
            } else {
               echo "()";
            } 
         ?>;
         for (var i = 0; i < posts.length; i++) {
            // get the variables for the loop
            var id = posts[i]['id'];
            var fb_user_id = posts[i]['fb_user_id'];
            var time_to_post = posts[i]['time_to_post'].slice(0,-3);
            var content = posts[i]['content'];

            var post = "<span id='wrapper"+id+"' class='wrapper'>";
            post += "<div class='post' id='post"+id+"'>";
            post += "<textarea class='posttextarea' rows='5' cols='86'></textarea>";
            post += "<div class='postbottom' id='postbottom"+id+"'>";
            post += "<span class='save' id='save"+id+"'>Saved.</span>";
            post += "<span class='time_to_postspan' id='time_to_post"+id+"'>"+time_to_post+"</span>";
            post += "To be posted: <input class='datetimepicker' value='"+time_to_post+"'>";
            post += "<button type='button' class='promptdeletebutton'>Delete</button>";
            post += "</div></div>";
            post += "<div class='post hideme deletepost' id='deletepost"+id+"'>";
            post += "<div class='confirmdelete'>Are you sure you want to premanently delete this post?</div>";
            post += "<div class='postbottom' id='postbottom"+id+"'>";
            post += "<button type='button' class='deletebutton confirmdeletebutton'>Delete</button>";
            post += "<button type='button' class='deletebutton canceldeletebutton'>Cancel</button>";
            post += "</div></div></span>"
            $("#posts").append(post);
            $("#post"+id+" .posttextarea").val(content);
         }
            
         // update the textarea
         $(".post .posttextarea").autoGrow();
         $(".post .posttextarea").trigger("change");
         $(".post .posttextarea").change(function(){
            var parent = $(this).parent();
            var id = $(parent).attr('id').slice(4);
            if (id) {
               saveUpdate(id, "content", $(this).val());
            }
         });

         // update the datepicker
         $(".post .datetimepicker").datetimepicker({
            dateFormat: 'yy-mm-dd',
            timeFormat: 'HH:mm',
            stepMinute: 10,
            sliderAccessArgs: { touchonly: false }
         });
         $(".post .datetimepicker").change(function(){
            var parent = $(this).parent();
            var id = $(parent).attr('id').slice(10);
            if (id && $(this).val() != $("#time_to_post"+id).html()) {
               saveUpdate(id, "time_to_post", $(this).val());
            }
         });

         // prompt delete
         $(".post .promptdeletebutton").click(function(){
            var parent = $(this).parent();
            var id = $(parent).attr('id').slice(10);
            $("#post"+id).addClass("hideme");
            $("#deletepost"+id).removeClass("hideme");
         });

         // confirm delete
         $(".post .confirmdeletebutton").click(function(){
            var parent = $(this).parent();
            var id = $(parent).attr('id').slice(10);
            $.post('Save.php', {
               'action':'delete',
               'fb_user_id':"<?php echo $app_user_id ?>",
               'id':id,
            }, function(data){
               $('#wrapper'+id).remove();
            });
         });

         // cancel delete
         $(".post .canceldeletebutton").click(function(){
            var parent = $(this).parent();
            var id = $(parent).attr('id').slice(10);
            $("#post"+id).removeClass("hideme");
            $("#deletepost"+id).addClass("hideme");
         });
      }

      // save delete
      function saveUpdate(id, column, value){
         if (column == "time_to_post") {
            $("#time_to_post"+id).html(value);
         }
         $("#save"+id).html("Saving.");
         $.post('Save.php', {
            'action':'edit',
            'fb_user_id':"<?php echo $app_user_id ?>",
            'id':id,
            'column':column,
            'value':value
         }, function(data){
            $("#save"+id).html("Saved.");
         });
      };
      
      </script>

      <header class="clearfix">
         <?php if (isset($basic)) { ?>
         <p id="picture" style="background-image: url(https://graph.facebook.com/<?php echo he($user_id); ?>/picture?type=normal)"></p>

         <div>
            <h1>Welcome, <strong><?php echo he(idx($basic, 'name')); ?></strong></h1>
            <p class="tagline">
               <a href="<?php echo he(idx($app_info, 'link'));?>" target="_top"><?php echo he($app_name); ?></a>
            </p>

            <div id="share-app">
               <p>Share this app:</p>
               <ul>
                  <li><a href="#" class="facebook-button" id="postToWall" data-url="<?php echo AppInfo::getUrl(); ?>"><span class="plus">Post to Wall</span></a></li>
                  <li><a href="#" class="facebook-button speech-bubble" id="sendToFriends" data-url="<?php echo AppInfo::getUrl(); ?>"><span class="speech-bubble">Send Message</span></a></li>
                  <li><a href="#" class="facebook-button apprequests" id="sendRequest" data-message="Test this awesome app"><span class="apprequests">Send Requests</span></a></li>
               </ul>
            </div>
         </div>
         <?php } else { ?>
            <div>
               <h1>Welcome.  Sign in to start using Post Delay.</h1>
               <div class="fb-login-button" data-scope="user_likes,user_photos"></div>
            </div>
         <?php } ?>
         Select your time zone:
         <select id="timezone">
            <option value="6.00">Eastern</option>
            <option value="7.00">Central</option>
            <option value="8.00">Mountain</option>
            <option value="9.00">Pacific</option>
         </select>
      </header>

	
      <?php
         if ($user_id && $connected) {
      ?>

      <section id="samples" class="clearfix" style="display:none;">
         <h1>Examples of the Facebook Graph API</h1>

         <!-- Some friends -->
         <div class="list">
            <h3>A few of your friends</h3>
            <ul class="friends">
               <?php
                  foreach ($friends as $friend) {
                  // Extract the pieces of info we need from the requests above
                  $id = idx($friend, 'id');
                  $name = idx($friend, 'name');
               ?>
               <li>
                  <a href="https://www.facebook.com/<?php echo he($id); ?>" target="_top">
                     <img src="https://graph.facebook.com/<?php echo he($id) ?>/picture?type=square" alt="<?php echo he($name); ?>">
                     <?php echo he($name); ?>
                  </a>
               </li>
               <?php } ?>
            </ul>
         </div>

         <!-- Photos -->
         <div class="list inline">
            <h3>Recent photos</h3>
            <ul class="photos">
               <?php
                  $i = 0;
                  foreach ($photos as $photo) {
                     // Extract the pieces of info we need from the requests above
                     $id = idx($photo, 'id');
                     $picture = idx($photo, 'picture');
                     $link = idx($photo, 'link');

                     $class = ($i++ % 4 === 0) ? 'first-column' : '';
               ?>
               <li style="background-image: url(<?php echo he($picture); ?>);" class="<?php echo $class; ?>">
                  <a href="<?php echo he($link); ?>" target="_top"></a>
               </li>
               <?php } ?>
            </ul>
         </div>

         <!-- Likes -->
         <div class="list">
            <h3>Things you like</h3>
            <ul class="things">
               <?php
                  foreach ($likes as $like) {
                     // Extract the pieces of info we need from the requests above
                     $id = idx($like, 'id');
                     $item = idx($like, 'name');

                     // This display's the object that the user liked as a link to that object's page.
               ?>
               <li>
                  <a href="https://www.facebook.com/<?php echo he($id); ?>" target="_top">
                     <img src="https://graph.facebook.com/<?php echo he($id) ?>/picture?type=square" alt="<?php echo he($item); ?>">
                     <?php echo he($item); ?>
                  </a>
               </li>
               <?php } ?>
            </ul>
         </div>

         <!-- Friends using app -->
         <div class="list">
            <h3>Friends using this app</h3>
            <ul class="friends">
               <?php
                  foreach ($app_using_friends as $auf) {
                  // Extract the pieces of info we need from the requests above
                  $id = idx($auf, 'uid');
                  $name = idx($auf, 'name');
               ?>
               <li>
                  <a href="https://www.facebook.com/<?php echo he($id); ?>" target="_top">
                     <img src="https://graph.facebook.com/<?php echo he($id) ?>/picture?type=square" alt="<?php echo he($name); ?>">
                     <?php echo he($name); ?>
                  </a>
               </li>
               <?php } ?>
            </ul>
         </div>

      </section>

      <?php } ?>
      <section id="newpost">
      <div class='post' id='post'>
         <textarea class='posttextarea' rows='5' cols='86'></textarea>
         <div class='postbottom' id='postbottom'>
            To be posted: <input class='datetimepicker' value=''>
            <button id="savebutton" type="button">Save</button>
         </div>
      </div>
      </section>
      
      <section id="posts">
      <hr style="position: relative; top:-15px;">
      Future Posts
      </section>
	
  </body>
</html>
