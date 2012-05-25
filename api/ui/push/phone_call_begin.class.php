<?php
/**
 * phone_call_begin.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package beartooth\ui
 * @filesource
 */

namespace beartooth\ui\push;
use cenozo\lib, cenozo\log, beartooth\util;

/**
 * push: phone_call begin
 *
 * Assigns a participant to a phone call.
 * @package beartooth\ui
 */
class phone_call_begin extends \cenozo\ui\push
{
  /**
   * Constructor.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args Push arguments
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'phone_call', 'begin', $args );
  }

  /**
   * Validate the operation.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @throws exception\notice
   * @access protected
   */
  protected function validate()
  {
    parent::validate();

    $db_assignment = $session->get_current_assignment();

    // make sure that interviewers are calling their current assignment only
    if( 'interviewer' == lib::create( 'business\session' )->get_role()->name )
      if( is_null( $db_assignment ) )
        throw lib::create( 'exception\runtime',
          'Interviewer tried to make call without an assignment.', __METHOD__ );

    // make sure the person being called is the same one who is assigned
    if( !is_null( $db_assignment ) )
    {
      $db_phone = lib::create( 'database\phone', $this->get_argument( 'phone_id' ) );
      if( $db_phone->participant_id != $db_assignment->get_interview()->participant_id )
        throw lib::create( 'exception\runtime',
          'User tried to make call to a different participant than who is currently assigned.',
          __METHOD__ );
    }
  }

  /**
   * This method executes the operation's purpose.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function execute()
  {
    parent::execute();

    $db_phone = lib::create( 'database\phone', $this->get_argument( 'phone_id' ) );
    $db_assignment = lib::create( 'business\session' )->get_current_assignment();

    // connect voip to phone
    lib::create( 'business\voip_manager' )->call( $db_phone );

    if( !is_null( $db_assignment ) )
    { // create a record of the phone call
      $db_phone_call = lib::create( 'database\phone_call' );
      $db_phone_call->assignment_id = $db_assignment->id;
      $db_phone_call->phone_id = $db_phone->id;
      $db_phone_call->save();
    }
  }
}
?>
