<?php
/**
 * queue.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package beartooth\database
 * @filesource
 */

namespace beartooth\database;
use cenozo\lib, cenozo\log, beartooth\util;

/**
 * queue: record
 *
 * @package beartooth\database
 */
class queue extends \cenozo\database\record
{
  /**
   * Constructor
   * 
   * The constructor either creates a new object which can then be insert into the database by
   * calling the {@link save} method, or, if an primary key is provided then the row with the
   * requested primary id will be loaded.
   * This method overrides the parent constructor because of custom sql required by each queue.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param integer $id The primary key for this object.
   * @access public
   */
  public function __construct( $id = NULL )
  {
    parent::__construct( $id );
  }
  
  /**
   * Generates the query list.
   * 
   * This method is called internally by {@link get_participant_list} and
   * {@link get_participant_count} in order to generate the proper SQL to complete those
   * methods.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   * @static
   */
  protected static function generate_query_list()
  {
    // define the SQL for each queue
    $queue_list = array(
      'all',
      'finished',
      'ineligible',
      'inactive',
      'refused consent',
      'sourcing required' );

    // add the participant final status types
    $class_name = lib::get_class_name( 'database\participant' );
    $queue_list = array_merge( $queue_list, $class_name::get_enum_values( 'status' ) );
    
    // finish the queue list
    $queue_list = array_merge( $queue_list, array(
      'eligible',
      'qnaire',
      'restricted',
      'qnaire waiting',
      'appointment',
      'no appointment',
      'new participant',
      'new participant outside calling time',
      'new participant within calling time',
      'new participant always available',
      'new participant available',
      'new participant not available',
      'old participant' ) );
     
    foreach( $queue_list as $queue )
    {
      $parts = self::get_query_parts( $queue );
      
      $from_sql = '';
      $first = true;
      // reverse order to make sure join to participant_for_queue table works
      foreach( array_reverse( $parts['from'] ) as $from )
      {
        $from_sql .= sprintf( $first ? 'FROM %s' : ', %s', $from );
        $first = false;
      }
      
      $join_sql = '';
      foreach( $parts['join'] as $join ) $join_sql .= ' '.$join;
      
      $where_sql = 'WHERE true';
      foreach( $parts['where'] as $where ) $where_sql .= ' AND '.$where;
      
      self::$query_list[$queue] =
        sprintf( 'SELECT <SELECT_PARTICIPANT> %s %s %s',
                 $from_sql,
                 $join_sql,
                 $where_sql );
    }
    
    // now add the sql for each call back status
    $class_name = lib::get_class_name( 'database\phone_call' );
    foreach( $class_name::get_enum_values( 'status' ) as $phone_call_status )
    {
      // ignore statuses which result in deactivating phone numbers
      if( 'disconnected' != $phone_call_status && 'wrong number' != $phone_call_status )
      {
        $queue_list = array(
          'phone call status',
          'phone call status waiting',
          'phone call status ready',
          'phone call status outside calling time',
          'phone call status within calling time',
          'phone call status always available',
          'phone call status not available',
          'phone call status available' );

        foreach( $queue_list as $queue )
        {
          $parts = self::get_query_parts( $queue, $phone_call_status );
          
          $from_sql = '';
          $first = true;
          // reverse order to make sure join to participant_for_queue table works
          foreach( array_reverse( $parts['from'] ) as $from )
          {
            $from_sql .= sprintf( $first ? 'FROM %s' : ', %s', $from );
            $first = false;
          }
          
          $join_sql = '';
          foreach( $parts['join'] as $join ) $join_sql .= ' '.$join;
          
          $where_sql = 'WHERE true';
          foreach( $parts['where'] as $where ) $where_sql .= ' AND '.$where;
          
          $queue_name = str_replace( 'phone call status', $phone_call_status, $queue );
          self::$query_list[$queue_name] =
            sprintf( 'SELECT <SELECT_PARTICIPANT> %s %s %s',
                     $from_sql,
                     $join_sql,
                     $where_sql );
        }
      }
    }
  }

  /**
   * Returns the number of participants currently in the queue.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param modifier $modifier Modifications to the queue.
   * @return int
   * @access public
   */
  public function get_participant_count( $modifier = NULL )
  {
    if( array_key_exists( $this->name, self::$participant_count_cache ) )
      return self::$participant_count_cache[$this->name];

    if( is_null( $modifier ) ) $modifier = lib::create( 'database\modifier' );

    // restrict to the site
    if( !is_null( $this->db_access ) )
    { // restrict to the access
      $modifier->where( 'participant.site_id', '=', $this->db_access->get_site()->id );

      $coverage_class_name = lib::get_class_name( 'database\coverage' );
      $coverage_mod = lib::create( 'database\modifier' );
      $coverage_mod->where( 'access_id', '=', $this->db_access->id );
      $coverage_mod->order( 'CHAR_LENGTH( postcode_mask )' );
      $coverage_list = $coverage_class_name::select( $coverage_mod );
      if( 0 == count( $coverage_list ) )
      {
        // no coverages means no participants
        $modifier->where( 'primary_postcode', '=', NULL );
      }
      else
      {
        $modifier->where_bracket( true );
        foreach( $coverage_class_name::select( $coverage_mod ) as $db_coverage )
        {
          $modifier->where_bracket( true, true );
          // within the coverage
          $modifier->where( 'primary_postcode', 'LIKE', $db_coverage->postcode_mask );
          // but outside other coverages
          $inner_coverage_mod = lib::create( 'database\modifier' );
          $inner_coverage_mod->where( 'access_id', '!=', $this->db_access->id );
          $inner_coverage_mod->where( 'access.site_id', '=', $this->db_access->site_id );
          $inner_coverage_mod->where( 'postcode_mask', 'LIKE', $db_coverage->postcode_mask );
          foreach( $coverage_class_name::select( $inner_coverage_mod ) as $db_inner_coverage )
            $modifier->where(
              'primary_postcode', 'NOT LIKE', $db_inner_coverage->postcode_mask );
          $modifier->where_bracket( false );
        }
        $modifier->where_bracket( false );
      }
    }
    else if( !is_null( $this->db_site ) )
    {
      $modifier->where( 'participant.site_id', '=', $this->db_site->id );
    }
    
    self::$participant_count_cache[$this->name] = (integer) static::db()->get_one(
      sprintf( '%s %s',
               $this->get_sql( 'COUNT( DISTINCT participant.id )' ),
               $modifier->get_sql( true ) ) );
    
    // if the value is 0 then update all child counts with 0 to save processing time
    if( 0 == self::$participant_count_cache[$this->name] )
      static::set_child_count_cache_to_zero( $this );

    return self::$participant_count_cache[$this->name];
  }

  /**
   * A recursive method to set the count cache for all child queues to 0.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\queue $db_queue
   * @static
   * @access private
   */
  private static function set_child_count_cache_to_zero( $db_queue )
  {
    $queue_mod = lib::create( 'database\modifier' );
    $queue_mod->where( 'parent_queue_id', '=', $db_queue->id );
    foreach( static::select( $queue_mod ) as $db_child_queue )
    {
      self::$participant_count_cache[$db_child_queue->name] = 0;
      self::set_child_count_cache_to_zero( $db_child_queue );
    }
  }

  /**
   * Returns a list of participants currently in the queue.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param modifier $modifier Modifications to the queue.
   * @return array( participant )
   * @access public
   */
  public function get_participant_list( $modifier = NULL )
  {
    if( is_null( $modifier ) ) $modifier = lib::create( 'database\modifier' );

    // restrict to the site
    if( !is_null( $this->db_site ) ) $modifier->where( 'participant.site_id', '=', $this->db_site->id );

    $participant_ids = static::db()->get_col(
      sprintf( '%s %s',
               $this->get_sql( 'DISTINCT participant.id' ),
               $modifier->get_sql( true ) ) );

    $participants = array();
    foreach( $participant_ids as $id ) $participants[] = lib::create( 'database\participant', $id );
    return $participants;
  }

  /**
   * The qnaire to restrict the queue to.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param qnaire $db_qnaire
   * @access public
   */
  public function set_qnaire( $db_qnaire = NULL )
  {
    $this->db_qnaire = $db_qnaire;
  }

  /**
   * The site to restrict the queue to.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param site $db_site
   * @access public
   */
  public function set_site( $db_site = NULL )
  {
    $this->db_site = $db_site;
  }
  
  /**
   * The access to restrict the queue to.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param access $db_access
   * @access public
   */
  public function set_access( $db_access = NULL )
  {
    $this->db_access = $db_access;
  }
  
  /**
   * Gets the parts of the query for a particular queue.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return associative array
   * @throws exception\argument
   * @access protected
   * @static
   */
  protected static function get_query_parts( $queue, $phone_call_status = NULL )
  {
    // determine what date/time to view the queues
    if( is_null( self::$viewing_date ) )
    {
      $viewing_date = 'UTC_TIMESTAMP()';
      $check_time = true;
    }
    else
    {
      // put double quotes around the date since it is being inserted into sql below
      $viewing_date = sprintf( '"%s"', self::$viewing_date );
      $check_time = false;
    }

    $class_name = lib::get_class_name( 'database\participant' );
    $participant_status_list = $class_name::get_enum_values( 'status' );

    // first a list of commonly used elements
    $status_where_list = array(
      'participant.active = true',
      '('.
      '  last_consent IS NULL'.
      '  OR last_consent NOT IN( "verbal deny", "written deny", "retract", "withdraw" )'.
      ')',
      'phone_number_count > 0' );
    
    // join to the queue_restriction table based on site, city, region or postcode
    $restriction_join = 
      'LEFT JOIN queue_restriction '.
      'ON queue_restriction.site_id = participant.site_id '.
      'OR queue_restriction.city = participant.city '.
      'OR queue_restriction.region_id = participant.region_id '.
      'OR queue_restriction.postcode = participant.postcode';
    
    $appointment_join =
      'LEFT JOIN appointment '.
      'ON appointment.participant_id = participant.id '.
      'AND '.sprintf( $check_time
               ? '%s > appointment.datetime'
               : 'DATE( %s ) > DATE( appointment.datetime )',
               $viewing_date ).' '.
      'AND '.
      '( '.
      '  ( '.
      '    current_qnaire_type = "home" '.
      '    AND appointment.address_id IS NOT NULL '.
      '  ) '.
      '  OR '.
      '  ( '.
      '    current_qnaire_type = "site" '.
      '    AND appointment.address_id IS NULL '.
      '  ) '.
      ')';

    // checks to see if participant is not restricted
    $check_restriction_sql =
      '('.
      // tests to see if all restrictions are null (meaning, no restriction)
      '  ('.
      '    queue_restriction.site_id IS NULL AND'.
      '    queue_restriction.city IS NULL AND'.
      '    queue_restriction.region_id IS NULL AND'.
      '    queue_restriction.postcode IS NULL'.
      '  )'.
      // tests to see if the site is being restricted but the participant isn't included
      '  OR ('.
      '    queue_restriction.site_id IS NOT NULL AND'.
      '    queue_restriction.site_id != participant.site_id'.
      '  )'.
      // tests to see if the city is being restricted but the participant isn't included
      '  OR ('.
      '    queue_restriction.city IS NOT NULL AND'.
      '    queue_restriction.city != participant.city'.
      '  )'.
      // tests to see if the region is being restricted but the participant isn't included
      '  OR ('.
      '    queue_restriction.region_id IS NOT NULL AND'.
      '    queue_restriction.region_id != participant.region_id'.
      '  )'.
      // tests to see if the postcode is being restricted but the participant isn't included
      '  OR ('.
      '    queue_restriction.postcode IS NOT NULL AND'.
      '    queue_restriction.postcode != participant.postcode'.
      '  )'.
      ')';
    
    // checks a participant's availability
    $check_availability_sql = sprintf(
      '( SELECT MAX( '.
      '    CASE DAYOFWEEK( %s ) '.
      '      WHEN 1 THEN availability.sunday '.
      '      WHEN 2 THEN availability.monday '.
      '      WHEN 3 THEN availability.tuesday '.
      '      WHEN 4 THEN availability.wednesday '.
      '      WHEN 5 THEN availability.thursday '.
      '      WHEN 6 THEN availability.friday '.
      '      WHEN 7 THEN availability.saturday '.
      '      ELSE 0 END ',
      $viewing_date );

    if( $check_time )
    {
      $check_availability_sql .= sprintf(
        '* IF( IF( TIME( %s ) < availability.start_time, '.
        '        24*60*60 + TIME_TO_SEC( TIME( %s ) ), '.
        '        TIME_TO_SEC( TIME( %s ) ) ) >= '.
        '    TIME_TO_SEC( availability.start_time ), 1, 0 ) '.
        '* IF( IF( TIME( %s ) < availability.start_time, '.
        '        24*60*60 + TIME_TO_SEC( TIME( %s ) ), '.
        '        TIME_TO_SEC( TIME( %s ) ) ) < '.
        '    IF( availability.end_time < availability.start_time, '.
        '        24*60*60 + TIME_TO_SEC( availability.end_time ), '.
        '        TIME_TO_SEC( availability.end_time ) ), 1, 0 ) ',
        $viewing_date,
        $viewing_date,
        $viewing_date,
        $viewing_date,
        $viewing_date,
        $viewing_date );
    }
    
    // finish the check availability sql
    $check_availability_sql .=
      ') '.
      'FROM availability '.
      'WHERE availability.participant_id = participant.id )';

    // checks to make sure a participant is within calling time hours
    if( $check_time )
    {
      $localtime = localtime( time(), true );
      $offset = $localtime['tm_isdst']
              ? 'timezone_offset + daylight_savings'
              : 'timezone_offset';
      $calling_time_sql = sprintf(
        '('.
        '  timezone_offset IS NULL OR'.
        '  daylight_savings IS NULL OR'.
        '  ('.
        '    TIME( %s + INTERVAL %s HOUR ) >= "<CALLING_START_TIME>" AND'.
        '    TIME( %s + INTERVAL %s HOUR ) < "<CALLING_END_TIME>"'.
        '  )'.
        ')',
        $viewing_date,
        $offset,
        $viewing_date,
        $offset );
    }

    // now determine the sql parts for the given queue
    if( 'all' == $queue )
    {
      $parts = array(
        'from' => array( 'participant_for_queue AS participant' ),
        'join' => array(),
        'where' => array() );
      return $parts;
    }
    else if( 'finished' == $queue )
    {
      $parts = self::get_query_parts( 'all' );
      // no current_qnaire_id means no qnaires left to complete
      $parts['where'][] = 'current_qnaire_id IS NULL';
      return $parts;
    }
    else if( 'ineligible' == $queue )
    {
      $parts = self::get_query_parts( 'all' );
      // current_qnaire_id is the either the next qnaire to work on or the one in progress
      $parts['where'][] = 'current_qnaire_id IS NOT NULL';
      // ineligible means either inactive or with a "final" status
      $parts['where'][] =
        '('.
        '  participant.active = false'.
        '  OR participant.status IS NOT NULL'.
        '  OR phone_number_count = 0'.
        '  OR last_consent IN( "verbal deny", "written deny", "retract", "withdraw" )'.
        ')';
      return $parts;
    }
    else if( 'inactive' == $queue )
    {
      $parts = self::get_query_parts( 'all' );
      $parts['where'][] = 'participant.active = false';
      return $parts;
    }
    else if( 'refused consent' == $queue )
    {
      $parts = self::get_query_parts( 'all' );
      $parts['where'][] = 'participant.active = true';
      $parts['where'][] =
        'last_consent IN( "verbal deny", "written deny", "retract", "withdraw" )';
      return $parts;
    }
    else if( 'sourcing required' == $queue )
    {
      $parts = self::get_query_parts( 'all' );
      $parts['where'][] = 'participant.active = true';
      $parts['where'][] =
        '('.
        '  last_consent IS NULL'.
        '  OR last_consent NOT IN( "verbal deny", "written deny", "retract", "withdraw" )'.
        ')';
      $parts['where'][] = 'phone_number_count = 0';

      return $parts;
    }
    else if( in_array( $queue, $participant_status_list ) )
    {
      $parts = self::get_query_parts( 'all' );
      $parts['where'] = array_merge( $parts['where'], $status_where_list );
      $parts['where'][] = 'participant.status = "'.$queue.'"'; // queue name is same as status name
      return $parts;
    }
    else if( 'eligible' == $queue )
    {
      $parts = self::get_query_parts( 'all' );
      // current_qnaire_id is the either the next qnaire to work on or the one in progress
      $parts['where'][] = 'current_qnaire_id IS NOT NULL';
      // active participant who does not have a "final" status and has at least one phone number
      $parts['where'][] = 'participant.active = true';
      $parts['where'][] = 'participant.status IS NULL';
      $parts['where'][] = 'phone_number_count > 0';
      $parts['where'][] =
        '('.
        '  last_consent IS NULL OR'.
        '  last_consent NOT IN( "verbal deny", "written deny", "retract", "withdraw" )'.
        ')';
      return $parts;
    }
    else if( 'qnaire' == $queue )
    {
      $parts = self::get_query_parts( 'eligible' );
      $parts['where'][] = 'participant.current_qnaire_id <QNAIRE_TEST>';
      return $parts;
    }
    else if( 'restricted' == $queue )
    {
      $parts = self::get_query_parts( 'qnaire' );
      // make sure to only include participants who are restricted
      $parts['join'][] = $restriction_join;
      $parts['where'][] = 'NOT '.$check_restriction_sql;
      return $parts;
    }
    else if( 'qnaire waiting' == $queue )
    {
      $parts = self::get_query_parts( 'qnaire' );
      // make sure to only include participants who are not restricted
      $parts['join'][] = $restriction_join;
      $parts['where'][] = $check_restriction_sql;
      // the current qnaire cannot start before start_qnaire_date
      $parts['where'][] = 'participant.start_qnaire_date IS NOT NULL';
      $parts['where'][] = sprintf( 'DATE( participant.start_qnaire_date ) > DATE( %s )',
                                   $viewing_date );
      return $parts;
    }
    else if( 'appointment' == $queue )
    {
      $parts = self::get_query_parts( 'qnaire' );
      // make sure to only include participants who are not restricted
      $parts['join'][] = $restriction_join;
      $parts['join'][] = $appointment_join;
      $parts['where'][] = $check_restriction_sql;
      // participants with a future appointment
      $parts['where'][] = 'appointment.id IS NOT NULL';
      return $parts;
    }
    else if( 'no appointment' == $queue )
    {
      $parts = self::get_query_parts( 'qnaire' );
      // make sure to only include participants who are not restricted
      $parts['join'][] = $restriction_join;
      $parts['where'][] = $check_restriction_sql;
      $parts['join'][] = $appointment_join;
      // participants without a future appointment
      $parts['where'][] = 'appointment.id IS NULL';
      return $parts;
    }
    else if( 'new participant' == $queue )
    {
      $parts = self::get_query_parts( 'no appointment' );
      // If there is a start_qnaire_date then the current qnaire has never been started,
      // the exception is for participants who have no appointment
      $parts['where'][] =
        '('.
        '  participant.start_qnaire_date IS NOT NULL OR'.
        '  participant.last_assignment_id IS NULL'.
        ')';
      return $parts;
    }
    else if( 'new participant outside calling time' == $queue )
    {
      $parts = self::get_query_parts( 'new participant' );

      if( self::use_calling_times() && $check_time )
      {
        // Need to join to the address_info database in order to determine the
        // participant's local time
        $parts['join'][] =
          'LEFT JOIN address_info.postcode '.
          'ON postcode.postcode = participant.postcode';
        $parts['where'][] = 'NOT '.$calling_time_sql;
      }
      else
      {
        $parts['where'][] = 'NOT true'; // purposefully a negative tautology
      }
        
      return $parts;
    }
    else if( 'new participant within calling time' == $queue )
    {
      $parts = self::get_query_parts( 'new participant' );

      if( self::use_calling_times() && $check_time )
      {
        // Need to join to the address_info database in order to determine the
        // participant's local time
        $parts['join'][] =
          'LEFT JOIN address_info.postcode '.
          'ON postcode.postcode = participant.postcode';
        $parts['where'][] = $calling_time_sql;
      }
      else
      {
        $parts['where'][] = 'true'; // purposefully a negative tautology
      }
        
      return $parts;
    }
    else if( 'new participant always available' == $queue )
    {
      $parts = self::get_query_parts( 'new participant within calling time' );
      // make sure the participant doesn't specify availability
      $parts['where'][] = $check_availability_sql.' IS NULL';
      return $parts;
    }
    else if( 'new participant available' == $queue )
    {
      $parts = self::get_query_parts( 'new participant within calling time' );
      // make sure the participant has availability and is currently available
      $parts['where'][] = $check_availability_sql.' = true';
      return $parts;
    }
    else if( 'new participant not available' == $queue )
    {
      $parts = self::get_query_parts( 'new participant within calling time' );
      // make sure the participant has availability and is currently not available
      $parts['where'][] = $check_availability_sql.' = false';
      return $parts;
    }
    else if( 'old participant' == $queue )
    {
      $parts = self::get_query_parts( 'no appointment' );
      // add the last phone call's information
      $parts['from'][] = 'phone_call';
      $parts['from'][] = 'assignment_last_phone_call';
      $parts['where'][] =
        'assignment_last_phone_call.assignment_id = participant.last_assignment_id';
      $parts['where'][] =
        'phone_call.id = assignment_last_phone_call.phone_call_id';
      // if there is no start_qnaire_date then the current qnaire has been started
      $parts['where'][] = 'participant.start_qnaire_date IS NULL';
      return $parts;
    }
    else
    {
      // make sure a phone call status has been included (all remaining queues require it)
      if( is_null( $phone_call_status ) )
        throw lib::create( 'exception\argument', 'phone_call_status', $phone_call_status, __METHOD__ );

      if( 'phone call status' == $queue )
      {
        $parts = self::get_query_parts( 'old participant' );
        $parts['where'][] =
          sprintf( 'phone_call.status = "%s"', $phone_call_status );
        return $parts;
      }
      else if( 'phone call status waiting' == $queue )
      {
        $parts = self::get_query_parts(
          'phone call status', $phone_call_status );
        $parts['where'][] = sprintf(
          $check_time ? '%s < phone_call.end_datetime + INTERVAL <CALLBACK_%s> MINUTE' :
                        'DATE( %s ) < '.
                        'DATE( phone_call.end_datetime + INTERVAL <CALLBACK_%s> MINUTE )',
          $viewing_date,
          str_replace( ' ', '_', strtoupper( $phone_call_status ) ) );
        return $parts;
      }
      else if( 'phone call status ready' == $queue )
      {
        $parts = self::get_query_parts(
          'phone call status', $phone_call_status );
        $parts['where'][] = sprintf(
          $check_time ? '%s >= phone_call.end_datetime + INTERVAL <CALLBACK_%s> MINUTE' :
                        'DATE( %s ) >= '.
                        'DATE( phone_call.end_datetime + INTERVAL <CALLBACK_%s> MINUTE )',
          $viewing_date,
          str_replace( ' ', '_', strtoupper( $phone_call_status ) ) );
        return $parts;
      }
      else if( 'phone call status outside calling time' == $queue )
      {
        $parts = self::get_query_parts( 'phone call status ready', $phone_call_status );

        if( self::use_calling_times() && $check_time )
        {
          // Need to join to the address_info database in order to determine the
          // participant's local time
          $parts['join'][] =
            'LEFT JOIN address_info.postcode '.
            'ON postcode.postcode = participant.postcode';
          $parts['where'][] = 'NOT '.$calling_time_sql;
        }
        else
        {
          $parts['where'][] = 'NOT true'; // purposefully a negative tautology
        }
          
        return $parts;
      }
      else if( 'phone call status within calling time' == $queue )
      {
        $parts = self::get_query_parts( 'phone call status ready', $phone_call_status );

        if( self::use_calling_times() && $check_time )
        {
          // Need to join to the address_info database in order to determine the
          // participant's local time
          $parts['join'][] =
            'LEFT JOIN address_info.postcode '.
            'ON postcode.postcode = participant.postcode';
          $parts['where'][] = $calling_time_sql;
        }
        else
        {
          $parts['where'][] = 'true'; // purposefully a tautology
        }
          
        return $parts;
      }
      else if( 'phone call status always available' == $queue )
      {
        $parts = self::get_query_parts(
          'phone call status within calling time', $phone_call_status );
        // make sure the participant doesn't specify availability
        $parts['where'][] = $check_availability_sql.' IS NULL';
        return $parts;
      }
      else if( 'phone call status not available' == $queue )
      {
        $parts = self::get_query_parts(
          'phone call status within calling time', $phone_call_status );
        // make sure the participant has availability and is currently not available
        $parts['where'][] = $check_availability_sql.' = false';
        return $parts;
      }
      else if( 'phone call status available' == $queue )
      {
        $parts = self::get_query_parts(
          'phone call status within calling time', $phone_call_status );
        // make sure the participant has availability and is currently available
        $parts['where'][] = $check_availability_sql.' = true';
        return $parts;
      }
      else // invalid queue name
      {
        throw lib::create( 'exception\argument', 'queue', $queue, __METHOD__ );
      }
    }
  }

  /**
   * Get the query for this queue.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $select_participant_sql The text to put in place of the first occurance of
   *               <SELECT_PARTICIPANT>
   * @return string
   * @access protected
   */
  protected function get_sql( $select_participant_sql )
  {
    // start by making sure the query list has been generated
    if( 0 == count( self::$query_list ) ) self::generate_query_list();

    $sql = self::$query_list[ $this->name ];
    $sql = preg_replace( '/\<SELECT_PARTICIPANT\>/', $select_participant_sql, $sql, 1 );
    $sql = str_replace( '<SELECT_PARTICIPANT>', 'participant.id', $sql );
    $qnaire_test_sql = is_null( $this->db_qnaire ) ? 'IS NOT NULL' : '= '.$this->db_qnaire->id;
    $sql = str_replace( '<QNAIRE_TEST>', $qnaire_test_sql, $sql );

    // fill in the settings
    $setting_manager = lib::create( 'business\setting_manager' );
    $setting = $setting_manager->get_setting( 'calling', 'start time' );
    $sql = str_replace( '<CALLING_START_TIME>', $setting, $sql );
    $setting = $setting_manager->get_setting( 'calling', 'end time' );
    $sql = str_replace( '<CALLING_END_TIME>', $setting, $sql );

    // fill in all callback timing settings
    $setting_mod = lib::create( 'database\modifier' );
    $setting_mod->where( 'category', '=', 'callback timing' );
    $class_name = lib::get_class_name( 'database\setting' );
    foreach( $class_name::select( $setting_mod ) as $db_setting )
    {
      $setting = $setting_manager->get_setting( 'callback timing', $db_setting->name );
      $template = sprintf( '<CALLBACK_%s>',
                           str_replace( ' ', '_', strtoupper( $db_setting->name ) ) );
      $sql = str_replace( $template, $setting, $sql );
    }
    return $sql;
  }
  
  /**
   * The date (YYYY-MM-DD) with respect to check all queue states.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param string $date
   * @access public
   * @static
   */
  public static function set_viewing_date( $date = NULL )
  {
    // validate the input
    $datetime_obj = util::get_datetime_object( $date );
    if( $date != $datetime_obj->format( 'Y-m-d' ) )
      log::err( 'The selected viewing date ('.$date.') may not be valid.' );
    
    self::$viewing_date = $datetime_obj->format( 'Y-m-d' );
  }
  
  /**
   * Determines whether calling times are enabled.
   * 
   * In order to restrict calling times based on participant's local time zones an
   * address_info database which lists timezones for postal and zip codes is needed.
   * This method reports calling times as enabled if that database is found.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return boolean
   * @access protected
   * @static
   */
  protected static function use_calling_times()
  {
    if( is_null( self::$calling_times_enabled ) )
    {
      self::$calling_times_enabled = 0 < static::db()->get_one(
        'SELECT COUNT(*) '.
        'FROM information_schema.schemata '.
        'WHERE schema_name = "address_info"' );
    }

    return self::$calling_times_enabled;
  }

  /**
   * The qnaire to restrict the queue to.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @var qnaire $db_qnaire
   */
  protected $db_qnaire = NULL;

  /**
   * The site to restrict the queue to.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @var site
   */
  protected $db_site = NULL;

  /**
   * The access to restrict the queue to.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @var access
   */
  protected $db_access = NULL;

  /**
   * The date (YYYY-MM-DD) with respect to check all queue states.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @var string
   * @static
   */
  protected static $viewing_date = NULL;

  /**
   * Whether or not calling times are enabled.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @var boolean
   * @static
   */
  protected static $calling_times_enabled = NULL;

  /**
   * The queries for each queue
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @var associative array of strings
   * @static
   */
  protected static $query_list = array();

  /**
   * A cache of participant counts for each queue
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @var associative array of integers
   * @static
   */
  protected static $participant_count_cache = array();
}
?>
