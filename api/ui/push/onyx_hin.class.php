<?php
/**
 * onyx_hin.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace beartooth\ui\push;
use cenozo\lib, cenozo\log, beartooth\util;

/**
 * push: onyx hin
 * 
 * Allows Onyx to update hin and interview details
 */
class onyx_hin extends \cenozo\ui\push
{
  /**
   * Constructor.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args Push arguments
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'onyx', 'hin', $args );
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

    $participant_class_name = lib::create( 'database\participant' );
    $onyx_instance_class_name = lib::create( 'database\onyx_instance' );
    $mastodon_manager = lib::create( 'business\cenozo_manager', MASTODON_URL );

    $db_onyx_user = lib::create( 'business\session' )->get_user();
    $db_onyx_instance =
      $onyx_instance_class_name::get_unique_record( 'user_id', $db_onyx_user->id );

    // get the user who is sending the request
    // NOTE: if this is a site instance then there is no interviewer, so use the instance itself
    $db_user = $db_onyx_instance->get_interviewer_user();
    if( is_null( $db_user ) ) $db_user = $db_onyx_instance->get_user();

    // get the body of the request
    $body = http_get_request_body();
    $data = util::json_decode( $body );

    if( !is_object( $data ) )
      throw lib::create( 'exception\runtime',
        'Unable to decode request body, received: '.print_r( $body, true ), __METHOD__ );

    // loop through the hin array
    foreach( $data->ConsentHIN as $hin_list )
    {
      foreach( get_object_vars( $hin_list ) as $uid => $hin_data )
      {
        $object_vars = get_object_vars( $hin_data );
        if( 1 >= count( $object_vars ) ) continue;

        $noid = array( 'user.name' => $db_user->name );
        $entry = array();

        $db_participant = $participant_class_name::get_unique_record( 'uid', $uid );
        if( is_null( $db_participant ) )
          throw lib::create( 'exception\runtime',
            sprintf( 'Participant UID "%s" does not exist.', $uid ), __METHOD__ );
        $entry['uid'] = $db_participant->uid;

        // try timeEnd, if null then try timeStart, if null then use today's date
        $var_name = 'timeEnd';
        if( array_key_exists( 'timeEnd', $object_vars ) &&
            0 < strlen( $hin_data->timeEnd ) )
        {
          $date_obj = util::get_datetime_object( $hin_data->timeEnd );
        }
        else if( array_key_exists( 'timeStart', $object_vars ) &&
                 0 < strlen( $hin_data->timeStart ) )
        {
          $date_obj = util::get_datetime_object( $hin_data->timeStart );
        }
        else
        {
          $date_obj = util::get_datetime_object();
        }
        $date = $date_obj->format( 'Y-m-d' );

        // update the HIN details
        $db_hin = $db_participant->get_hin();
        if( is_null( $db_hin ) )
        {
          $db_hin = lib::create( 'database\hin' );
          $db_hin->participant_id = $db_participant->id;
        }

        $var_name = 'ICF_10HIN_COM';
        if( !array_key_exists( $var_name, $object_vars ) )
          throw lib::create( 'exception\argument', $var_name, NULL, __METHOD__ );
        $accept = 1 == preg_match( '/y|yes|true|1/i', $hin_data->$var_name );
        $db_hin->extended_access = $accept;
        $entry['accept'] = $accept;

        // now pass on the data to Mastodon
        $mastodon_manager = lib::create( 'business\cenozo_manager', MASTODON_URL );
        $args = array(
          'columns' => array(
            'from_onyx' => 1,
            'complete' => 0,
            'date' => $date ),
          'entry' => $entry,
          'noid' => $noid );
        if( array_key_exists( 'pdfForm', $object_vars ) )
          $args['form'] = $hin_data->pdfForm;
        $mastodon_manager->push( 'hin_form', 'new', $args );

        // update the hin
        // NOTE: this call needs to happen AFTER the mastodon push operation above, otherwise
        // a database lock will prevent the operation from completing
        $db_hin->save();
      }
    }
  }
}
