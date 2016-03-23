<?php
/**
 * module.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace beartooth\service\participant;
use cenozo\lib, cenozo\log, beartooth\util;

/**
 * Performs operations which effect how this module is used in a service
 */
class module extends \cenozo\service\participant\module
{
  /**
   * Extend parent method
   */
  public function prepare_read( $select, $modifier )
  {
    parent::prepare_read( $select, $modifier );

    if( $this->get_argument( 'assignment', false ) )
    {
      $modifier->join( 'queue_has_participant', 'participant.id', 'queue_has_participant.participant_id' );
      $modifier->join( 'queue', 'queue_has_participant.queue_id', 'queue.id' );
      $modifier->join( 'qnaire', 'queue_has_participant.qnaire_id', 'qnaire.id' );
      $modifier->where( 'participant.active', '=', true );
      $modifier->where( 'queue.rank', '!=', NULL );

      $join_mod = lib::create( 'database\modifier' );
      $join_mod->where( 'queue_has_participant.queue_id', '=', 'queue_state.queue_id', false );
      $join_mod->where( 'queue_has_participant.site_id', '=', 'queue_state.site_id', false );
      $join_mod->where( 'queue_has_participant.qnaire_id', '=', 'queue_state.qnaire_id', false );
      $modifier->join_modifier( 'queue_state', $join_mod, 'left' );
      $modifier->where( 'queue_state.id', '=', NULL );

      // repopulate queue if it is out of date
      $queue_class_name = lib::get_class_name( 'database\queue' );
      $interval = $queue_class_name::get_interval_since_last_repopulate();
      if( is_null( $interval ) || 0 < $interval->days || 22 < $interval->h )
      { // it's been at least 23 hours since the non time-based queues have been repopulated
        $queue_class_name::repopulate();
        $queue_class_name::repopulate_time();
      }
      else
      {
        $interval = $queue_class_name::get_interval_since_last_repopulate_time();
        if( is_null( $interval ) || 0 < $interval->days || 0 < $interval->h || 0 < $interval->i )
        { // it's been at least one minute since the time-based queues have been repopulated
          $queue_class_name::repopulate_time();
        }
      }
    }
    else
    {
      if( $select->has_table_columns( 'queue' ) || $select->has_table_columns( 'qnaire' ) )
      {
        $join_sel = lib::create( 'database\select' );
        $join_sel->from( 'queue_has_participant' );
        $join_sel->add_column( 'participant_id' );
        $join_sel->add_column( 'MAX( queue_id )', 'queue_id', false );

        $join_mod = lib::create( 'database\modifier' );
        $join_mod->group( 'participant_id' );

        $modifier->left_join(
          sprintf( '( %s %s ) AS participant_max_queue',
                   $join_sel->get_sql(),
                   $join_mod->get_sql() ),
          'participant.id',
          'participant_max_queue.participant_id' );

        $join_mod = lib::create( 'database\modifier' );
        $join_mod->where(
          'participant_max_queue.queue_id', '=', 'queue_has_participant.queue_id', false );
        $join_mod->where(
          'participant_max_queue.participant_id', '=', 'queue_has_participant.participant_id', false );

        $modifier->join_modifier( 'queue_has_participant', $join_mod, 'left' );

        if( $select->has_table_columns( 'queue' ) )
          $modifier->left_join( 'queue', 'queue_has_participant.queue_id', 'queue.id' );
        if( $select->has_table_columns( 'qnaire' ) )
        {
          $modifier->left_join( 'qnaire', 'queue_has_participant.qnaire_id', 'qnaire.id' );

          // title is "qnaire.rank: qnaire.name"
          if( $select->has_table_column( 'qnaire', 'title' ) )
            $select->add_table_column( 'qnaire', 'CONCAT( qnaire.rank, ": ", qnaire.name )', 'title', false );

          // fake the qnaire start-date
          if( $select->has_table_column( 'qnaire', 'start_date' ) )
            $select->add_table_column( 'qnaire', 'queue_has_participant.start_qnaire_date', 'start_date', false );
        }
      }
    }
  }
}