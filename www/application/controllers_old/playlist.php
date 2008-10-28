<?php
class Playlist extends Controller 
{

   var $models = array(
            'Playlist'
         );

   function __construct()
   {
      parent::Controller();
      $this->load->helper('url');
      $this->load->helper('html');
      $this->load->helper('form');
      
      foreach( $this->models as $_model ) $this->load->model($_model.'_model',$_model,TRUE);

      $this->output->enable_profiler(TRUE);      
      $this->router =& load_class('Router');
     	require('xml_writer/xmlwriterclass.php');

   }



   function create_playlist()
   {
      $screen_id = $this->router->segments[3];
      $no_of_days = $this->router->segments[4];
      $pl_data = $this->Playlist->create_playlist_for_screen($screen_id, $no_of_days);
      $pl_days =& $pl_data['PlaylistDays'];
      $pl_medias =& $pl_data['PlaylistMedias'];

      $ftp_dirname = "../ftp";

      /*
      *  Iterate through all involved medias.
      */
      $source_dirname = $ftp_dirname . "/originals";
      $target_dirname = $ftp_dirname . "/convert";
      foreach( $pl_medias as $target_name => $source_name )
      {
         copy( $source_dirname . "/" . $source_name, $target_dirname . "/" . $target_name );
      }


      /*
      *  Iterate through all playlist days.
      */
      $dirname = $ftp_dirname . "/screens";
      foreach( $pl_days as $pl_day )
      {
         $playlist = implode( "\n", $pl_day->events );

         $filename = $dirname . "/" . $screen_id . "_" . 
                     $pl_day->CronDate->year . $pl_day->CronDate->month . $pl_day->CronDate->day . '.events';
         file_put_contents( $filename, $playlist );
      }


      $_id = $this->router->segments[3];
      $data['query'] = $this->Screen->find($_id);
      $data['heading'] = 'Screen';

      $InvolvedMedias = $this->Screen->involved_medias($_id);
      $data['query']['Involved medias'] = $InvolvedMedias;

      $data['anchors'][] = array( 'link' => 'playlist/create_playlist/'.$_id.'/5', 'label' => 'Create 5-day playlist' );
      $data['anchors'][] = array( 'link' => 'playlist/create_playlist/'.$_id.'/10', 'label' => 'Create 10-day playlist' );
      $this->load->view('active_view_view', $data);
   }


}
?>