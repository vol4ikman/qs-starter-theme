<?php 

/**
 * addressToLatlong()
 *
 * @param mixed $Address [ string ]
 * @return Latitude and Longitude
 */
function addressToLatlong( $Address ){

  $Address = urlencode($Address);
  $request_url = "http://maps.googleapis.com/maps/api/geocode/xml?address=".$Address."&sensor=true";
  $xml = simplexml_load_file($request_url) or die("url not loading");
  $status = $xml->status;

  if ($status=="OK") {
      $Lat = $xml->result->geometry->location->lat;
      $Long = $xml->result->geometry->location->lng;
      $latLong = $Lat . ',' . $Long;
      return $latLong;
  }

}

/*****************************************
**  Get Waze Link From Lat & Long
*****************************************/
/**
 * waze_link()
 *
 * @param mixed $lat_long
 * @return waze link with navigation to location
 */
function waze_link($lat_long){

    $lat_long_array = explode(',', $lat_long );

    $lat = $lat_long_array[0];

    $long = $lat_long_array[1];

    return 'http://waze.to/?ll='.$lat.','.$long.'&navigate=yes';

}

//Get YouTube ID from URL
function getYoutubeIdFromUrl($url) {
    $parts = parse_url($url);
    if(isset($parts['query'])){
        parse_str($parts['query'], $qs);
        if(isset($qs['v'])){
            return $qs['v'];
        }else if(isset($qs['vi'])){
            return $qs['vi'];
        }
    }
    if(isset($parts['path'])){
        $path = explode('/', trim($parts['path'], '/'));
        return $path[count($path)-1];
    }
    return false;
}

function videoType( $url ){
    $newUrl = $url;
    if (strpos($url,'youtube') !== false || strpos($url,'youtu') !== false) {
        $newUrl = 'https://www.youtube.com/embed/'.getYoutubeIdFromUrl($url);
    } elseif (strpos($url,'vimeo') !== false) {
        if(preg_match("/(https?:\/\/)?(www\.)?(player\.)?vimeo\.com\/([a-z]*\/)*([0-9]{6,11})[?]?.*/", $url, $output_array)) {
            $newUrl = 'https://player.vimeo.com/video/'.$output_array[5];
        }
    }

    return $newUrl;
}

/*****************************************
**  Get Image Thumbnail From Video Link
*****************************************/
/**
 * video_image()
 *
 * @param mixed $url ( video full url )
 * @return image thumbnail ( youtube and viemo )
 */
function video_image($url){
    $image_url = parse_url($url);
    if($image_url['host'] == 'www.youtube.com' || $image_url['host'] == 'youtube.com'){
        if( !empty( $image_url['query'] ) ){
            $array = explode("&", $image_url['query']);
            return "http://img.youtube.com/vi/".substr($array[0], 2)."/0.jpg";
        } else{
            $array = end( ( explode('embed/', $image_url['path'] ) ) );
            return "http://img.youtube.com/vi/".$array."/0.jpg";
        }
    } else if($image_url['host'] == 'www.vimeo.com' || $image_url['host'] == 'vimeo.com'){
        $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/".substr($image_url['path'], 1).".php"));
        return $hash[0]["thumbnail_small"];
    }
}


/*****************************************
**  Get Array Of Info From Video Link
*****************************************/
/**
* @param       string  $videoString
* @return      array   An array of video metadata if found
*/
function parseVideos( $videoString = null , $return = null )
{
   // return data
   $videos = array();
   if (!empty($videoString)) {
       // split on line breaks
       $videoString = stripslashes(trim($videoString));
       $videoString = explode("\n", $videoString);
       $videoString = array_filter($videoString, 'trim');
       // check each video for proper formatting
       foreach ($videoString as $video) {
           // check for iframe to get the video url
           if (strpos($video, 'iframe') !== FALSE) {
               // retrieve the video url
               $anchorRegex = '/src="(.*)?"/isU';
               $results = array();
               if (preg_match($anchorRegex, $video, $results)) {
                   $link = trim($results[1]);
               }
           } else {
               // we already have a url
               $link = $video;
           }
           // if we have a URL, parse it down
           if (!empty($link)) {
               // initial values
               $video_id = NULL;
               $videoIdRegex = NULL;
               $results = array();
               // check for type of youtube link
               if (strpos($link, 'youtu') !== FALSE) {
                   if (strpos($link, 'youtube.com') !== FALSE) {
                       // works on:
                       // http://www.youtube.com/embed/VIDEOID
                       // http://www.youtube.com/embed/VIDEOID?modestbranding=1&amp;rel=0
                       // http://www.youtube.com/v/VIDEO-ID?fs=1&amp;hl=en_US
                       $videoIdRegex = "/^(?:http(?:s)?:\/\/)?(?:www.)?(?:m.)?(?:youtu.be\/|youtube.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/";
                   } else if (strpos($link, 'youtu.be') !== FALSE) {
                       // works on:
                       // http://youtu.be/daro6K6mym8
                       $videoIdRegex = '/youtu.be\/([a-zA-Z0-9_]+)\??/i';
                   }
                   if ($videoIdRegex !== NULL) {
                       if (preg_match($videoIdRegex, $link, $results)) {
                           $video_str = 'http://www.youtube.com/v/%s?fs=1&amp;autoplay=1';
                           $thumbnail_str = 'http://img.youtube.com/vi/%s/2.jpg';
                           $fullsize_str = 'http://img.youtube.com/vi/%s/0.jpg';
                           $video_id = $results[1];
                       }
                   }
               }
               // handle vimeo videos
               else if (strpos($video, 'vimeo') !== FALSE) {
                   if (strpos($video, 'player.vimeo.com') !== FALSE) {
                       // works on:
                       // http://player.vimeo.com/video/37985580?title=0&amp;byline=0&amp;portrait=0
                       $videoIdRegex = '/player.vimeo.com\/video\/([0-9]+)\??/i';
                   } else {
                       // works on:
                       // http://vimeo.com/37985580
                       $videoIdRegex = '/vimeo.com\/([0-9]+)\??/i';
                   }
                   if ($videoIdRegex !== NULL) {
                       if (preg_match($videoIdRegex, $link, $results)) {
                           $video_id = $results[1];
                           // get the thumbnail
                           try {
                               $hash = unserialize(file_get_contents("http://vimeo.com/api/v2/video/$video_id.php"));
                               if (!empty($hash) && is_array($hash)) {
                                   $video_str = 'http://vimeo.com/moogaloop.swf?clip_id=%s';
                                   $thumbnail_str = $hash[0]['thumbnail_small'];
                                   $fullsize_str = $hash[0]['thumbnail_large'];
                               } else {
                                   // don't use, couldn't find what we need
                                   unset($video_id);
                               }
                           } catch (Exception $e) {
                               unset($video_id);
                           }
                       }
                   }
               }
               // check if we have a video id, if so, add the video metadata
               if (!empty($video_id)) {
                   // add to return
                   $videos[] = array(
                       'url' => sprintf($video_str, $video_id),
                       'thumbnail' => sprintf($thumbnail_str, $video_id),
                       'fullsize' => sprintf($fullsize_str, $video_id)
                   );
               }
           }
       }
   }
    // return array of parsed videos
    if( $return == 'ID')
        return $video_id;
    if( $return == 'link'){
        if (strpos($video, 'vimeo') !== FALSE)
            return 'https://vimeo.com/'.$video_id;
        else
            return 'https://www.youtube.com/watch?v='.$video_id;
    }
    else
        return $videos;
}

/*****************************************
**  Excerpt by chars.
*****************************************/
/**
 * limit()
 *
 * @param mixed $content
 * @param integer $limit
 * @return return just $limit of your string content
 */
function limit( $content , $limit = 1000 ) {
    $excerpt = explode(' ', $content, $limit);
    if (count($excerpt)>=$limit) {
        array_pop($excerpt);
        $excerpt = implode(" ",$excerpt).'...';
    }
    else {
        $excerpt = implode(" ",$excerpt);
    }
    $excerpt = preg_replace('`\[[^\]]*\]`','',$excerpt);

    return $excerpt;
}

/*****************************************
**  get list of posts types;
*****************************************/
/**
 * getListPostTypes()
 *
 * @param mixed $args
 * @param string $output
 * @param string $returnType
 * @return array of all the posts types in the system
 */
function getListPostTypes( $args = null , $output = 'names' , $returnType = 'full' ){

    $post_types = get_post_types( $args , $output );

    switch( $returnType ){

        case 'ID_VALUE':
            $return = array();

            foreach( $post_types as $key => $type ){
                $return[ $type->labels->name ] = $key;
            }
        break;

        case 'full':
        case 'FULL':
        case '':
        default:
            $return = $post_types;
    }

    return $return;

}

/*****************************************
    Detect User Role and return Type
*****************************************/
/**
 * getUserType()
 *
 * @return the id and the role of the current user
 */
function getUserType(){
    $return         = array();

    if ( isUserLogin() ) {
        global $current_user;
        $return['ID']   = get_current_user_id();
        $user_roles     = $current_user->roles;
        $return['role'] = array_shift($user_roles);

    } else{
        $return['ID'] = '0';
        $return['role'] = 'guest';
    }

    return $return;
}


/**
 * [getTimeAgo description]
 * @method getTimeAgo
 * @param  [type] $datetime [ any date and time format ]
 * @param  [type] $full [ output type - if true the result will be all the time stamp ]
 * @return [type] [ return the time ago ]
 */
function getTimeAgo( $datetime, $full = false ) {
    $now  = new DateTime;
    $ago  = new DateTime( $datetime );
    $diff = $now->diff( $ago );

    $diff->w  = floor( $diff->d / 7 );
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ( $string as $k => &$v ) {
        if ( $diff->$k ) {
            $v = $diff->$k . ' ' . $v . ( $diff->$k > 1 ? 's' : '' );
        } else
            unset( $string[ $k ] );
    }

    if ( !$full ) $string = array_slice( $string, 0, 1 );
    return $string ? implode( ', ', $string ) . ' ago' : 'just now';
}

/*****************************************
    ACTION : INTEGRATION TO RAV MESER
*****************************************/
//add_action('wpcf7_before_send_mail', 'rav_meser_integration_wp_cf7');
function rav_meser_integration_wp_cf7( $cf7 ) {
        $submission = WPCF7_Submission::get_instance();
        $data =& $submission->get_posted_data();
        $data['name'] = $data['first-name'];
        if( !empty( $data['subscribers_name'] ) && !empty( $data['subscribers_email'] ) && $data['form_id'] = get_field('rav_meser_id' , 'options') ){
            $url = 'https://subscribe.responder.co.il';
            $postparams = array(
                'fields[subscribers_email]' => urlencode( $data['subscribers_email'] ),
                'fields[subscribers_name]'  => urlencode( $data['subscribers_name'] ),
                'form_id'                   => urlencode( $data['form_id'] ),
                'encoding'                  => urlencode( 'utf-8' )
            );
            foreach( $postparams as $key=>$value ) { $fields_string .= $key.'='.$value.'&'; }
                rtrim( $fields_string, '&' );

            $ch  = curl_init( $url );

            curl_setopt( $ch, CURLOPT_POSTFIELDS, $fields_string );
            curl_setopt( $ch, CURLOPT_POST, count( $postparams ) );
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1 );
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );

            $output = curl_exec( $ch );
            curl_close( $ch );
        }
}
