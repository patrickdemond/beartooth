<?php
/**
 * operator_assignment.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package sabretooth\ui
 * @filesource
 */

namespace sabretooth\ui;

/**
 * widget operator assignment
 * 
 * @package sabretooth\ui
 */
class operator_assignment extends widget
{
  /**
   * Constructor
   * 
   * Defines all variables which need to be set for the associated template.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args An associative array of arguments to be processed by the widget
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'operator', 'assignment', $args );
    $this->set_heading( 'Current Assignment' );
  }

  /**
   * Finish setting the variables in a widget.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access public
   */
  public function finish()
  {
    parent::finish();
    
    $session = \sabretooth\session::self();

    // see if this user has an open assignment
    $db_assignment = $session->get_current_assignment();

    if( !is_null( $db_assignment ) )
    { // fill out the participant's details
      $db_participant = $db_assignment->get_interview()->get_participant();
      
      $name = sprintf( $db_participant->first_name.' '.$db_participant->last_name );

      $language = 'none';
      if( 'en' == $db_participant->language ) $language = 'english';
      else if( 'fr' == $db_participant->language ) $language = 'french';

      $consent = 'none';
      $db_consent = $db_participant->get_current_consent();
      if( !is_null( $db_consent ) ) $consent = $db_consent->event;
      
      $last_call = 'never called';
      $db_phone_call = $db_participant->get_last_phone_call();
      if( !is_null( $db_phone_call ) )
        $last_call = sprintf( '%s on %s (%s)',
                              \sabretooth\util::get_formatted_time( $db_phone_call->start_time ),
                              \sabretooth\util::get_formatted_date( $db_phone_call->start_time ),
                              $db_phone_call->status ? $db_phone_call->status : 'in progress' );
      
      $modifier = new \sabretooth\database\modifier();
      $modifier->where( 'active', '=', true );
      $modifier->where( 'phone', '!=', NULL );
      $modifier->order( 'rank' );
      $contact_list = $db_participant->get_contact_list( $modifier );
      
      $modifier = new \sabretooth\database\modifier();
      $modifier->where( 'end_time', '!=', NULL );
      $assignment_calls = $db_assignment->get_phone_call_count( $modifier );

      if( 0 == count( $contact_list ) )
      {
        \sabretooth\log::crit(
          'An operator has been assigned a participant with no callable contacts' );
      }
      else
      {
        $contacts = array();
        foreach( $contact_list as $db_contact )
          $contacts[$db_contact->id] =
            sprintf( '%d. %s (%s)', $db_contact->rank, $db_contact->type, $db_contact->phone );
        $this->set_variable( 'contacts', $contacts );
        $this->set_variable( 'statuses', \sabretooth\database\phone_call::get_enum_values( 'status' ) );
      }

      $this->set_variable( 'participant_id', $db_participant->id );
      $this->set_variable( 'participant_name', $name );
      $this->set_variable( 'participant_language', $language );
      $this->set_variable( 'participant_consent', $consent );
      $this->set_variable( 'participant_last_call', $last_call );
      $this->set_variable( 'assignment_calls', $assignment_calls );
      $this->set_variable( 'on_call', !is_null( $session->get_current_phone_call() ) );
    }
  }
}
?>
