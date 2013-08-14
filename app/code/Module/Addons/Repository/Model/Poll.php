<?php
require_once 'Module/Core/Repository/Model/Abstract.php';
class Module_Addons_Repository_Model_Poll extends Module_Core_Repository_Model_Abstract {

  function get_poll($id = null, $enabled_only = true){
    $poll = $this->poll($id,$enabled_only);
    if( empty($poll) ){
      return null;
    }

    $poll_options = $this->options( $poll['id'] );
    if( empty($poll_options) ){
      return null;
    }

    $poll_votes = $this->votes( $poll['id'] );
    return array('poll' => $poll, 'options' => $poll_options, 'votes' => $poll_votes);
  }

  function poll($id=0, $enabled_only = true){
    $select = $this->_db->select()->from(array('p' => 'poll' ));

    if( empty($id) ){
      $select->limit(1)->order( 'p.created DESC');
    }else{
      $select->where( 'p.id = ?', $id )->limit(1);
    }

    if( ! empty($enabled_only) ){
      $select->where( 'p.status = ?', 'enabled');
    }

    $poll = $this->_db->query( $select )->fetch();
    return empty($poll) ? null : $poll;
  }

  function options($id = 0){
    $select = $this->_db->select()
                   ->from(array('po' => 'poll_options'), array('option_id','option') )
                   ->where('po.poll_id = ?', $id);

    return $this->_db->query( $select )->fetchAll();
  }

  function votes($id = 0){
    $select = $this->_db->select()
                   ->from(array('pv' => 'poll_votes' ) , array('votes' => 'COUNT(*)') )
                   ->join(array('po' => 'poll_options'), 'po.option_id = pv.vote', array('po.option_id') )
                   ->where('pv.poll_id = ?', $id)
                   ->group('pv.vote');
    $poll_votes = $this->_db->query( $select )->fetchAll();
    if( empty($poll_votes) ){
      return null;
    }

    $total_votes = 0;
    foreach($poll_votes AS $vote){
      $votes[ $vote['option_id'] ] = $vote['votes'];
      $total_votes +=$vote['votes'];
    }

    return $this->percentage($votes, $total_votes);
  }

  function percentage($votes=null, $total_votes=0){
    if( empty($votes) || empty($total_votes) ){
      return null;
    }

    $total_options = count($votes);
    $percentage = array();
    foreach($votes AS $option => $vote){
      $percentage[$option] = array('vote'=>$vote, 'percentage'=> round(($vote*100)/$total_votes) );
    }
    return $percentage;
  }

  function get_results_chart($id=0, $was_vote_saved = true){
    $results = $this->get_poll($id);
    if ( empty($results) ){
      return 'false';
    }

    $chart_data = array();
    foreach($results['options'] AS $option){
      if( ! empty($results['votes'][$option['option_id'] ]['vote']) ){
        $chart_data[] = array( 'id'         => $option['option_id']
            ,'option'     => $option['option']
            ,'vote'       => $results['votes'][ $option['option_id'] ]['vote']
            ,'percentage' => $results['votes'][ $option['option_id'] ]['percentage']);
      }
    }

    $error_duplicated_vote = 'false';
    if( empty($was_vote_saved) ){
      $error_duplicated_vote = App::xlat('POLL_on_dulpicated');
    }
    $result = array_merge(array("question" => $results['poll']['question'], "duplicated"=>$error_duplicated_vote), array("options" => $chart_data) );
    return App::module('Core')->getModel('Json')->encode($result);
  }

}