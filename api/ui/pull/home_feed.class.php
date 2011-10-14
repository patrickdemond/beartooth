<?php
/**
 * home_feed.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package beartooth\ui
 * @filesource
 */

namespace beartooth\ui\pull;
use beartooth\log, beartooth\util;
use beartooth\business as bus;
use beartooth\database as db;
use beartooth\exception as exc;

/**
 * pull: home feed
 * 
 * @package beartooth\ui
 */
class home_feed extends base_feed
{
  /**
   * Constructor
   * 
   * Defines all variables required by the home feed.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args Pull arguments.
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'home', $args );
  }
  
  /**
   * Returns the data provided by this feed.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return array
   * @access public
   */
  public function finish()
  {
    // create a list of home appointments between the feed's start and end time
    $modifier = new db\modifier();
    $modifier->where( 'address_id', '!=', NULL );
    $modifier->where( 'datetime', '>=', $this->start_datetime );
    $modifier->where( 'datetime', '<', $this->end_datetime );

    $event_list = array();
    $db_site = bus\session::self()->get_site();
    foreach( db\appointment::select_for_self( $modifier ) as $db_appointment )
    {
      $start_datetime_obj = util::get_datetime_object( $db_appointment->datetime );
      $end_datetime_obj = clone $start_datetime_obj;
      $end_datetime_obj->modify(
        sprintf( '+%d minute',
        bus\setting_manager::self()->get_setting( 'appointment', 'home duration' ) ) );

      $db_participant = $db_appointment->get_participant();
      $event_list[] = array(
        'id' => $db_appointment->id,
        'title' => is_null( $db_participant->uid ) || 0 == strlen( $db_participant->uid ) ?
          $db_participant->first_name.' '.$db_participant->last_name :
          $db_participant->uid,
        'allDay' => false,
        'start' => $start_datetime_obj->format( \DateTime::ISO8601 ),
        'end' => $end_datetime_obj->format( \DateTime::ISO8601 ) );
    }

    return $event_list;
  }
}
?>
