<?php
/**
 * participant_tree.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace beartooth\ui\pull;
use cenozo\lib, cenozo\log, beartooth\util;

/**
 * Consent form report data.
 * 
 * @abstract
 */
class participant_tree_report extends \cenozo\ui\pull\base_report
{
  /**
   * Constructor
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $subject The subject to retrieve the primary information from.
   * @param array $args Pull arguments.
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'participant_tree', $args );
  }

  /**
   * Builds the report.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function build()
  {
    $restrict_site_id = $this->get_argument( 'restrict_site_id', 0 );
    $restrict_source_id = $this->get_argument( 'restrict_source_id', 0 );
    $db_qnaire = lib::create( 'database\qnaire', $this->get_argument( 'restrict_qnaire_id' ) );
    $language = $this->get_argument( 'language' );
    
    $site_mod = lib::create( 'database\modifier' );
    if( $restrict_site_id ) $site_mod->where( 'id', '=', $restrict_site_id );
    
    $this->add_title( 'for the '.$db_qnaire->name.' questionnaire' );
    $this->add_title( 'any' == $language ?
      'for all languages' : 'restricted to the language "'.$language.'"' );

    $contents = array();

    // The following code is very similar to the participant_tree widget
    // We loop through every queue to get the number of participants waiting in it
    $queue_class_name = lib::get_class_name( 'database\queue' );
    $site_class_name = lib::get_class_name( 'database\site' );
    foreach( $queue_class_name::select() as $db_queue )
    {
      $row = array( $db_queue->title );

      foreach( $site_class_name::select( $site_mod ) as $db_site )
      {
        // restrict by site and source, if necessary
        // Note that queue modifiers have to be created for each iteration of the loop since
        // they are modified in the process of getting the participant count
        $queue_mod = lib::create( 'database\modifier' );
        if( 0 < $restrict_source_id )
          $queue_mod->where( 'participant.source_id', '=', $restrict_source_id );

        // restrict by language
        if( 'any' != $language )
        {
          // english is default, so if the language is english allow null values
          if( 'en' == $language )
          {
            $queue_mod->where_bracket( true );
            $queue_mod->where( 'participant.language', '=', $language );
            $queue_mod->or_where( 'participant.language', '=', NULL );
            $queue_mod->where_bracket( false );
          }
          else $queue_mod->where( 'participant.language', '=', $language );
        }

        $db_queue->set_site( $db_site );
        $db_queue->set_qnaire( $db_qnaire );
        $row[] = $db_queue->get_participant_count( $queue_mod );
      }

      // add the grand total if we are not restricting by site
      if( !$restrict_site_id )
      {
        // restrict by source, if necessary
        // Note that queue modifiers have to be created for each iteration of the loop since
        // they are modified in the process of getting the participant count
        $queue_mod = lib::create( 'database\modifier' );
        if( 0 < $restrict_source_id )
          $queue_mod->where( 'participant.source_id', '=', $restrict_source_id );

        // restrict by language
        if( 'any' != $language )
        {
          // english is default, so if the language is english allow null values
          if( 'en' == $language )
          {
            $queue_mod->where_bracket( true );
            $queue_mod->where( 'participant.language', '=', $language );
            $queue_mod->or_where( 'participant.language', '=', NULL );
            $queue_mod->where_bracket( false );
          }
          else $queue_mod->where( 'participant.language', '=', $language );
        }

        $db_queue->set_site( NULL );
        $row[] = $db_queue->get_participant_count( $queue_mod );
      }

      $contents[] = $row;
    }
    
    if( $restrict_site_id )
    {
      $header = array( 'Queue', 'Total' );
    }
    else
    {
      $header = array( 'Queue' );
      foreach( $site_class_name::select( $site_mod ) as $db_site ) $header[] = $db_site->name;
      $header[] = 'Total';
    }

    $this->add_table( NULL, $header, $contents, NULL );
  }
}
?>
