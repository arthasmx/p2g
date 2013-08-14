<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Video extends Module_Core_Repository_Model_Abstract {

  private $config  = false;
  private $youtube = false;

  function init(){
    $this->config = App::module("Addons")->getConfig("core","video");
  }

  function time_to_show_live_sermon($go_live_now = null){
    if( ! empty( $go_live_now ) ){
      return true;
    }
    if( App::module('Addons')->getConfig('core','video_streaming')=="disabled"  ){
      return null;
    }

    $now    = strtotime( date('h:ia') );
    $day    = date('N'); // 1=monday, 7=sunday
    $dates  = App::module('Core')->getModel('Dates');

    foreach($this->config['hour'] AS $times){
      if( $day == $this->config['day'] && $dates->is_time_between_times($times['start'], $times['end'],$now) ){
        return true;
      }
    }
    return false;
  }


  /*
   * Youtube Methods
   */
  function get_video($id = null, $render_style = "block"){
    if( empty($id) ){
      return null;
    }

    $this->youtube_init();
    try{
      $video = $this->youtube->getVideoEntry( $id );
    }catch(Exception $e){
      return null;
    }

    return $this->video_data($video, $render_style);
  }

  function get_user_videos($user=null){
    if( empty($user) ){
      return null;
    }

    $this->youtube_init();
    $videos     = array();
    $video_list = $this->youtube->getUserUploads($user);
    foreach($video_list AS $video){
      $videos[] = $this->video_data($video);
    }
    return $videos;
  }

  private function youtube_init(){
    require_once 'Zend/Gdata/YouTube.php';
    $this->youtube = new Zend_Gdata_YouTube();
  }

  private function video_data($video=null, $render_style = "block"){
    return ( ! empty($video) && $video->isVideoEmbeddable() ) ? array(
        'id'        => $video->getVideoId()
        ,'title'    => $video->getVideoTitle()
        ,'url'      => $this->get_flash_url( $video->mediaGroup->content )
        ,'page'     => $video->getVideoWatchPageUrl()
        ,'width'    => $this->config['youtube'][$render_style]['width']
        ,'height'   => $this->config['youtube'][$render_style]['height']
        ,'autoplay' => $this->config['youtube']['autoplay']
        ,'thumbs'   => $video->getVideoThumbnails() )
        :
        null;
  }

  private function get_flash_url($uri){
    foreach ($uri as $content) {
      if ($content->type === 'application/x-shockwave-flash') {
        return $content->url;
      }
    }
    return null;
  }

}