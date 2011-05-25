<?php
/**
 * appointment.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package sabretooth\database
 * @filesource
 */

namespace sabretooth\database;
use sabretooth\log, sabretooth\util;
use sabretooth\business as bus;
use sabretooth\exception as exc;

/**
 * appointment: record
 *
 * @package sabretooth\database
 */
class appointment extends record
{
  /**
   * Overrides the parent load method.
   * @author Patrick Emond
   * @access public
   */
  public function load()
  {
    parent::load();

    // appointments are not to the second, so remove the :00 at the end of the datetime field
    $this->datetime = substr( $this->datetime, 0, -3 );
  }
  

  /**
   * Determines whether there are operator slots available during this appointment's date/time
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return boolean
   * @throws exception\runtime
   * @access public
   */
  public function validate_date()
  {
    if( is_null( $this->participant_id ) )
      throw new exc\runtime(
        'Cannot validate appointment date, participant id is not set.', __METHOD__ );

    $db_participant = new participant( $this->participant_id );
    $db_site = $db_participant->get_primary_site();
    if( is_null( $db_site ) )
      throw new exc\runtime(
        'Cannot validate an appointment date, participant has no primary location.', __METHOD__ );
    
    $expected_start = intval( preg_replace( '/[^0-9]/', '',
      bus\setting_manager::self()->get_setting( 'appointment', 'start_time' ) ) );
    $expected_end = intval( preg_replace( '/[^0-9]/', '', 
      bus\setting_manager::self()->get_setting( 'appointment', 'end_time' ) ) );

    // test for the start of the appointment
    $date_obj = util::get_datetime_object( $this->datetime );
    $start = intval( preg_replace( '/[^0-9]/', '', $date_obj->format( 'H:i' ) ) );

    // how many slots are open?
    $modifier = new modifier();
    $modifier->where( 'site_id', '=', $db_site->id );
    $modifier->where( 'start_datetime', '<=', $date_obj->format( 'Y-m-d H:i:s' ) );
    $modifier->where( 'end_datetime', '>', $date_obj->format( 'Y-m-d H:i:s' ) );
    
    $open_slots = shift::count( $modifier );
    if( $expected_start <= $start && $start <= $expected_end &&
        $open_slots < $db_site->operators_expected )
    {
      $open_slots = $db_site->operators_expected;
    }
    
    // and how many appointments are during this time?
    $modifier = new modifier();
    $modifier->where( 'datetime', '<=', $date_obj->format( 'Y-m-d H:i:s' ) );
    $modifier->where( 'datetime', '>', $date_obj->format( 'Y-m-d H:i:s' ) );
    $appointments = appointment::count_for_site( $db_site, $modifier );
    $open_slots -= $appointments; 

    if( 0 >= $open_slots ) return false;

    // test for the end of the appointment
    $date_obj->add( new \DateInterval( 'PT1H' ) );
    $end = intval( preg_replace( '/[^0-9]/', '', $date_obj->format( 'H:i' ) ) );

    // how many slots are open?
    $modifier = new modifier();
    $modifier->where( 'site_id', '=', $db_site->id );
    $modifier->where( 'start_datetime', '<=', $date_obj->format( 'Y-m-d H:i:s' ) );
    $modifier->where( 'end_datetime', '>', $date_obj->format( 'Y-m-d H:i:s' ) );
    $open_slots = shift::count( $modifier );
    if( $expected_start <= $start && $start <= $expected_end &&
        $open_slots < $db_site->operators_expected )
    {
      $open_slots = $db_site->operators_expected;
    }
    
    // and how many appointments are during this time?
    $modifier = new modifier();
    $modifier->where( 'datetime', '<=', $date_obj->format( 'Y-m-d H:i:s' ) );
    $modifier->where( 'datetime', '>', $date_obj->format( 'Y-m-d H:i:s' ) );
    $appointments = appointment::count_for_site( $db_site, $modifier );
    $open_slots -= $appointments; 

    if( 0 >= $open_slots ) return false;

    // if we get here then there is at least one available slot
    return true;
  }

  /**
   * Identical to the parent's select method but restrict to a particular site.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param site $db_site The site to restrict the selection to.
   * @param modifier $modifier Modifications to the selection.
   * @param boolean $count If true the total number of records instead of a list
   * @return array( record ) | int
   * @static
   * @access public
   */
  public static function select_for_site( $db_site, $modifier = NULL, $count = false )
  {
    // if there is no site restriction then just use the parent method
    if( is_null( $db_site ) ) return parent::select( $modifier, $count );
    
    $select_tables = 'appointment, participant_primary_location, participant, contact';
    
    // straight join the tables
    if( is_null( $modifier ) ) $modifier = new modifier();
    $modifier->where(
      'appointment.participant_id', '=', 'participant_primary_location.participant_id', false );
    $modifier->where( 'participant_primary_location.contact_id', '=', 'contact.id', false );
    $modifier->where( 'appointment.participant_id', '=', 'participant.id', false );

    $sql = sprintf( ( $count ? 'SELECT COUNT( %s.%s ) ' : 'SELECT %s.%s ' ).
                    'FROM %s '.
                    'WHERE ( participant.site_id = %d '.
                    '  OR contact.province_id IN '.
                    '  ( SELECT id FROM province WHERE site_id = %d ) ) %s',
                    static::get_table_name(),
                    static::get_primary_key_name(),
                    $select_tables,
                    $db_site->id,
                    $db_site->id,
                    $modifier->get_sql( true ) );

    if( $count )
    {
      return intval( static::db()->get_one( $sql ) );
    }
    else
    {
      $id_list = static::db()->get_col( $sql );
      $records = array();
      foreach( $id_list as $id ) $records[] = new static( $id );
      return $records;
    }
  }

  /**
   * Identical to the parent's count method but restrict to a particular site.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param site $db_site The site to restrict the count to.
   * @param modifier $modifier Modifications to the count.
   * @return int
   * @static
   * @access public
   */
  public static function count_for_site( $db_site, $modifier = NULL )
  {
    return static::select_for_site( $db_site, $modifier, true );
  }


  /**
   * Get the status of the appointment as a string (upcoming, missed, completed, in progress or
   * assigned)
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return string
   * @access public
   */
  public function get_state()
  {
    if( is_null( $this->id ) )
    {
      log::warning( 'Tried to determine state for appointment with no id.' );
      return NULL;
    } 
    
    // if the appointment has a status, nothing else matters
    if( !is_null( $this->status ) ) return $this->status;

    $status = 'unknown';
    
    // settings are in minutes, time() is in seconds, so multiply by 60
    $pre_window_time = 60 * bus\setting_manager::self()->get_setting(
                              'appointment', 'call pre-window' );
    $post_window_time = 60 * bus\setting_manager::self()->get_setting(
                               'appointment', 'call post-window' );
    $now = time();
    $appointment = strtotime( $this->datetime );

    // get the status of the appointment
    if( $now < $appointment - $pre_window_time )
    {
      $status = 'upcoming';
    }
    else if( $now < $appointment + $post_window_time )
    {
      $status = 'assignable';
    }
    else
    { // not in the future
      if( is_null( $this->assignment_id ) )
      { // not assigned
        $status = 'missed';
      }
      else // assigned
      {
        $db_assignment = $this->get_assignment();
        if( !is_null( $db_assignment->end_datetime ) )
        { // assignment closed but appointment never completed
          log::crit(
            sprintf( 'Appointment %d has assignment which is closed but no status was set.',
                     $this->id ) );
          $status = 'incomplete';
        }
        else // assignment active
        {
          $modifier = new modifier();
          $modifier->where( 'end_datetime', '=', NULL );
          $open_phone_calls = $db_assignment->get_phone_call_count( $modifier );
          if( 0 < $open_phone_calls )
          { // assignment currently on call
            $status = "in progress";
          }
          else
          { // not on call
            $status = "assigned";
          }
        }
      }
    }

    return $status;
  }
}
?>
