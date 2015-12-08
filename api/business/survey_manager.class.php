<?php
/**
 * survey_manager.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace beartooth\business;
use cenozo\lib, cenozo\log, beartooth\util;

/**
 * survey_manager: record
 */
class survey_manager extends \cenozo\singleton
{
  /**
   * Constructor.
   * 
   * Since this class uses the singleton pattern the constructor is never called directly.  Instead
   * use the {@link singleton} method.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function __construct() {}

  /**
   * Gets the current survey URL.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return string (or false if the survey is not active)
   * @access public
   */
  public function get_survey_url()
  {
    $session = lib::create( 'business\session' );

    // determine the participant
    $db_participant = NULL;
    if( array_key_exists( 'withdrawing_participant', $_COOKIE ) )
    {
      $db_participant = lib::create( 'database\participant', $_COOKIE['withdrawing_participant'] );
    }
    else if( array_key_exists( 'proxying_participant', $_COOKIE ) )
    {
      $db_participant = lib::create( 'database\participant', $_COOKIE['proxying_participant'] );
    }
    else
    {
      // must have an assignment
      $db_assignment = $session->get_current_assignment();
      if( !is_null( $db_assignment ) )
      {
        // the assignment must have an open call
        $modifier = lib::create( 'database\modifier' );
        $modifier->where( 'end_datetime', '=', NULL );
        $call_list = $db_assignment->get_phone_call_list( $modifier );
        if( 0 != count( $call_list ) )
          $db_participant = $db_assignment->get_interview()->get_participant();
      }
    }

    if( !is_null( $db_participant ) )
    {
      $sid = $this->get_current_sid();
      $token = $this->get_current_token();
      if( false !== $sid && false != $token )
      {
        // determine which language to use
        $db_language = $db_participant->get_language();
        if( is_null( $db_language ) ) $db_language = $session->get_service()->get_language();
        return sprintf( '%s/index.php?sid=%s&lang=%s&token=%s&newtest=Y',
                        LIMESURVEY_URL,
                        $sid,
                        $db_language->code,
                        $token );
      }
    }

    // there is currently no active survey
    return false;
  }

  /**
   * This method returns the current SID, or false if all surveys are complete.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return int
   * @access public
   */
  public function get_current_sid()
  {
    if( is_null( $this->current_sid ) ) $this->determine_current_sid_and_token();
    return $this->current_sid;
  }

  /**
   * This method returns the current token, or false if all surveys are complete.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @return string
   * @access public
   */
  public function get_current_token()
  {
    if( is_null( $this->current_token ) ) $this->determine_current_sid_and_token();
    return $this->current_token;
  }

  /**
   * Determines the current SID and token.
   * 
   * This method will first determine whether the participant needs to complete the withdraw
   * script or a questionnaire.  It then determines whether the appropriate script has been
   * completed or not.
   * Note: This method will create tokens in the limesurvey database as necessary.
   * This is also where interviews are marked as complete once all phases are finished.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function determine_current_sid_and_token()
  {
    $this->current_sid = false;
    $this->current_token = false;

    $qnaire_class_name = lib::get_class_name( 'database\qnaire' );
    $tokens_class_name = lib::get_class_name( 'database\limesurvey\tokens' );
    $survey_class_name = lib::get_class_name( 'database\limesurvey\survey' );
    $session = lib::create( 'business\session' );

    if( array_key_exists( 'withdrawing_participant', $_COOKIE ) )
    {
      // get the participant being withdrawn
      $db_participant = lib::create( 'database\participant', $_COOKIE['withdrawing_participant'] );
      if( is_null( $db_participant ) )
      {
        log::warning( 'Tried to determine survey information for an invalid participant.' );
        return false;
      }

      $this->process_withdraw( $db_participant );
    }
    else if( array_key_exists( 'proxying_participant', $_COOKIE ) )
    {
      // get the participant being proxyied
      $db_participant = lib::create( 'database\participant', $_COOKIE['proxying_participant'] );
      if( is_null( $db_participant ) )
      {
        log::warning( 'Tried to determine survey information for an invalid participant.' );
        return false;
      }

      $this->process_proxy( $db_participant );
    }
    else
    {
      $db_assignment = $session->get_current_assignment();
      if( is_null( $db_assignment ) )
      {
        log::warning( 'Tried to determine survey information without an active assignment.' );
        return false;
      }

      // records which we will need
      $db_interview = $db_assignment->get_interview();
      $db_participant = $db_interview->get_participant();
      $db_consent = $db_participant->get_last_consent();

      if( $db_consent && false == $db_consent->accept )
      { // the participant has withdrawn, check to see if the withdraw script is complete
        $this->process_withdraw( $db_participant );
      }
      else
      { // the participant has not withdrawn, check each phase of the interview
        $phase_mod = lib::create( 'database\modifier' );
        $phase_mod->order( 'rank' );

        $phase_list = $db_interview->get_qnaire()->get_phase_list( $phase_mod );
        if( 0 == count( $phase_list ) )
        {
          log::emerg( 'Questionnaire with no phases has been assigned.' );
        }
        else
        {
          foreach( $phase_list as $db_phase )
          {
            // let the tokens record class know which SID we are dealing with
            $tokens_class_name::set_sid( $db_phase->sid );

            $token = $tokens_class_name::determine_token_string(
                       $db_interview,
                       $db_phase->repeated ? $db_assignment : NULL );
            $tokens_mod = lib::create( 'database\modifier' );
            $tokens_mod->where( 'token', '=', $token );
            $db_tokens = current( $tokens_class_name::select( $tokens_mod ) );

            if( false === $db_tokens )
            { // token not found, create it
              $db_tokens = lib::create( 'database\limesurvey\tokens' );
              $db_tokens->token = $token;
              $db_tokens->firstname = $db_participant->honorific.' '.$db_participant->first_name;
              $db_tokens->lastname = $db_participant->last_name;
              $db_tokens->email = $db_participant->email;

              if( 0 < strlen( $db_participant->other_name ) )
                $db_tokens->firstname .= sprintf( ' (%s)', $db_participant->other_name );

              // fill in the attributes
              $db_surveys = lib::create( 'database\limesurvey\surveys', $db_phase->sid );
              foreach( $db_surveys->get_token_attribute_names() as $key => $value )
                $db_tokens->$key = static::get_attribute( $db_participant, $value );

              // TODO: this is temporary code to fix the TOKEN != "NO" problem in limesurvey
              //       for survey 63834
              if( 63834 == $db_phase->sid && is_null( $db_tokens->attribute_9 ) )
                $db_tokens->attribute_9 = "UNKNOWN";

              $db_tokens->save();

              $this->current_sid = $db_phase->sid;
              $this->current_token = $token;
              break;
            }
            else if( 'N' == $db_tokens->completed )
            { // we have found the current phase
              $this->current_sid = $db_phase->sid;
              $this->current_token = $token;
              break;
            }
            // else do not set the current_sid or current_token
          }
        }

        // The interview is not completed here since the interview must be completed by Onyx
        // and Onyx must report back when it is done.
      }
    }
  }

  /**
   * Internal method to handle the withdraw script
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\participant $db_participant
   * @access private
   */
  private function process_withdraw( $db_participant )
  {
    $tokens_class_name = lib::get_class_name( 'database\limesurvey\tokens' );
    $withdraw_manager = lib::create( 'business\withdraw_manager' );

    $withdraw_sid = $withdraw_manager->get_withdraw_sid( $db_participant );
    if( is_null( $withdraw_sid ) )
      throw lib::create( 'exception\runtime',
        sprintf( 'Trying to process withdraw for participant %s without a questionnaire.',
                 $db_participant->uid ),
        __METHOD__ );

    $db_surveys = lib::create( 'database\limesurvey\surveys', $withdraw_sid );

    // get the withdraw token
    $tokens_class_name::set_sid( $withdraw_sid );
    $token = $db_participant->uid;
    $tokens_mod = lib::create( 'database\modifier' );
    $tokens_mod->where( 'token', '=', $token );
    $db_tokens = current( $tokens_class_name::select( $tokens_mod ) );

    if( false === $db_tokens )
    { // token not found, create it
      $db_tokens = lib::create( 'database\limesurvey\tokens' );
      $db_tokens->token = $token;
      $db_tokens->firstname = $db_participant->honorific.' '.$db_participant->first_name;
      $db_tokens->lastname = $db_participant->last_name;
      $db_tokens->email = $db_participant->email;

      if( 0 < strlen( $db_participant->other_name ) )
        $db_tokens->firstname .= sprintf( ' (%s)', $db_participant->other_name );

      // fill in the attributes
      foreach( $db_surveys->get_token_attribute_names() as $key => $value )
        $db_tokens->$key = static::get_attribute( $db_participant, $value );

      $db_tokens->save();

      $this->current_sid = $withdraw_sid;
      $this->current_token = $token;
    }
    else if( 'N' == $db_tokens->completed )
    {
      $this->current_sid = $withdraw_sid;
      $this->current_token = $token;
    }
    else // token is complete, store the survey results
    {
      $withdraw_manager->process( $db_participant );
    }
  }

  /**
   * Internal method to handle the pxoy script
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\participant $db_participant
   * @access private
   */
  private function process_proxy( $db_participant )
  {
    $tokens_class_name = lib::get_class_name( 'database\limesurvey\tokens' );

    $proxy_manager = lib::create( 'business\proxy_manager' );

    // let the tokens record class know which SID we are dealing with by checking if
    // there is a source-specific survey for the participant, and if not falling back
    // on the default proxy survey
    $proxy_sid = $proxy_manager->get_proxy_sid( $db_participant );
    if( is_null( $proxy_sid ) )
      throw lib::create( 'exception\runtime',
        sprintf( 'Trying to proxy participant %s without a proxy survey.',
                 $db_participant->uid ),
        __METHOD__ );
    $db_surveys = lib::create( 'database\limesurvey\surveys', $proxy_sid );

    $tokens_class_name::set_sid( $proxy_sid );
    
    // only generate a new token if there isn't already one in cookies
    $token = array_key_exists( 'proxying_token', $_COOKIE )
           ? $_COOKIE['proxying_token']
           : $proxy_manager->generate_token( $db_participant );
    setcookie( 'proxying_token', $token, 0, COOKIE_PATH );

    $tokens_mod = lib::create( 'database\modifier' );
    $tokens_mod->where( 'token', '=', $token );
    $db_tokens = current( $tokens_class_name::select( $tokens_mod ) );

    if( false === $db_tokens )
    { // token not found, create it
      $db_tokens = lib::create( 'database\limesurvey\tokens' );
      $db_tokens->token = $token;
      $db_tokens->firstname = $db_participant->first_name;
      $db_tokens->lastname = $db_participant->last_name;
      $db_tokens->email = $db_participant->email;

      if( 0 < strlen( $db_participant->other_name ) )
        $db_tokens->firstname .= sprintf( ' (%s)', $db_participant->other_name );

      // fill in the attributes
      foreach( $db_surveys->get_token_attribute_names() as $key => $value )
        $db_tokens->$key = static::get_attribute( $db_participant, $value );

      $db_tokens->save();

      $this->current_sid = $proxy_sid;
      $this->current_token = $token;
    }
    else if( 'N' == $db_tokens->completed )
    {
      $this->current_sid = $proxy_sid;
      $this->current_token = $token;
    }
    // else do not set the current_sid or current_token members!
  }

  /**
   * Determines attributes needed at survey time.
   * TODO: this method contains many reference to CLSA-specific features which
   *       should be made generic
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param database\participant $db_participant
   * @param string $key The name of the attribute to return.
   * @return mixed
   * @access public
   */
  public static function get_attribute( $db_participant, $key )
  {
    $value = NULL;

    if( 'cohort' == $key )
    {
      $value = $db_participant->get_cohort()->name;
    }
    else if( 'uid' == $key )
    {
      $value = $db_participant->uid;
    }
    else if( 'override quota' == $key )
    {
      // override_quota is true if the participant's quota is disabled AND override_quota is true
      $override_quota = '0';
      $value = false === $db_participant->get_quota_enabled() &&
               ( $db_participant->override_quota || $db_participant->get_source()->override_quota )
             ? '1'
             : '0';
    }
    else if( false !== strpos( $key, 'address' ) )
    {
      $db_address = $db_participant->get_primary_address();

      if( 'address street' == $key )
      {
        if( $db_address )
        {
          $value = $db_address->address1;
          if( !is_null( $db_address->address2 ) ) $value .= ' '.$db_address->address2;
        }
        else
        {
          $value = '';
        }
      }
      else if( 'address city' == $key )
      {
        $value = $db_address ? $db_address->city : '';
      }
      else if( 'address province' == $key )
      {
        $value = $db_address ? $db_address->get_region()->name : '';
      }
      else if( 'address postal code' == $key )
      {
        $value = $db_address ? $db_address->postcode : '';
      }
    }
    else if( 'age' == $key )
    {
      $value = strlen( $db_participant->date_of_birth )
                  ? util::get_interval(
                      util::get_datetime_object( $db_participant->date_of_birth ) )->y
                  : "";
    }
    else if( 'written consent received' == $key )
    {
      $consent_mod = lib::create( 'database\modifier' );
      $consent_mod->where( 'written', '=', true );
      $value = 0 < $db_participant->get_consent_count( $consent_mod ) ? '1' : '0';
    }
    else if( 'consented to provide HIN' == $key )
    {
      $db_hin = $db_participant->get_hin();
      if( is_null( $db_hin ) ) $value = -1;
      else $value = 1 == $db_hin->access ? 1 : 0;
    }
    else if( 'HIN recorded' == $key )
    {
      $db_hin = $db_participant->get_hin();
      $value = !is_null( $db_participant->get_hin()->code );
    }
    else if( 'provided data' == $key )
    {
      $event_type_class_name = lib::get_class_name( 'database\event_type' );

      // participants have provided data once their first interview is done
      $event_mod = lib::create( 'database\modifier' );
      $event_mod->where( 'event_type_id', '=',
        $event_type_class_name::get_unique_record( 'name', 'completed (Baseline Home)' )->id );

      $event_list = $db_participant->get_event_list( $event_mod );
      $provided_data = 0 < count( $event_list ) ? 'yes' : 'no';

      $value = $provided_data;
    }
    else if( 'DCS samples' == $key )
    {
      // get data from Opal
      $setting_manager = lib::create( 'business\setting_manager' );
      $opal_url = $setting_manager->get_setting( 'opal', 'server' );
      $opal_manager = lib::create( 'business\opal_manager', $opal_url );

      $value = 0;

      if( $opal_manager->get_enabled() )
      {
        try
        {
          $blood = $opal_manager->get_value(
            'clsa-dcs', 'Phlebotomy', $db_participant, 'AGREE_BS' );
          $urine = $opal_manager->get_value(
            'clsa-dcs', 'Phlebotomy', $db_participant, 'AGREE_URINE' );

          $value = 0 == strcasecmp( 'yes', $blood ) ||
                        0 == strcasecmp( 'yes', $urine )
                      ? 1 : 0;
        }
        catch( \cenozo\exception\base_exception $e )
        {
          // ignore argument exceptions (data not found in Opal) and report the rest
          if( 'argument' != $e->get_type() ) log::warning( $e->get_message() );
        }
      }
    }
    else if( 'INCL_2e' == $key )
    {
      // TODO: This is a custom token attribute which refers to a specific question in the
      // introduction survey.  This code is not generic and needs to eventually be made
      // generic.
      $survey_class_name = lib::get_class_name( 'database\limesurvey\survey' );

      $db_interview = lib::create( 'business\session')->get_current_assignment()->get_interview();
      $phase_mod = lib::create( 'database\modifier' );
      $phase_mod->where( 'rank', '=', 1 );
      $phase_list = $db_interview->get_qnaire()->get_phase_list( $phase_mod );

      // determine the SID of the first phase of the questionnaire (where the question is asked)
      if( 1 == count( $phase_list ) )
      {
        $db_phase = current( $phase_list );
        $survey_class_name::set_sid( $db_phase->sid );

        $survey_mod = lib::create( 'database\modifier' );
        $survey_mod->where( 'token', 'LIKE', $db_interview->id.'_%' );
        $survey_mod->order_desc( 'datestamp' );
        $survey_list = $survey_class_name::select( $survey_mod );

        $found = false;
        foreach( $survey_list as $db_survey )
        { // loop through all surveys until an answer is found
          try
          {
            $value = $db_survey->get_response( $key );
            // match any NON NULL response
            if( !is_null( $value ) ) $found = true;
          }
          catch( \cenozo\exception\runtime $e )
          {
            // ignore the error and continue without setting the attribute
          }

          if( $found ) break;
        }
      }
    }
    else if( 'interviewer first_name' == $key || 'user first_name' == $key )
    {
      $db_user = lib::create( 'business\session' )->get_user();
      $value = $db_user->first_name;
    }
    else if( 'interviewer last_name' == $key || 'user last_name' == $key )
    {
      $db_user = lib::create( 'business\session' )->get_user();
      $value = $db_user->last_name;
    }
    else if( 'participant_source' == $key )
    {
      $db_source = $db_participant->get_source();
      $value = is_null( $db_source ) ? '(none)' : $db_source->name;
    }
    else if( 'last interview date' == $key )
    {
      $event_type_class_name = lib::get_class_name( 'database\event_type' );
      $event_mod = lib::create( 'database\modifier' );
      $event_mod->order_desc( 'datetime' );
      $event_mod->where( 'event_type_id', '=',
        $event_type_class_name::get_unique_record( 'name', 'completed (Baseline Site)' )->id );

      $event_list = $db_participant->get_event_list( $event_mod );
      $db_event = 0 < count( $event_list ) ? current( $event_list ) : NULL;
      $value = is_null( $db_event )
             ? 'DATE UNKNOWN'
             : util::get_formatted_date( $db_event->datetime );
    }
    else if( 'last completed interview' == $key )
    {
      $event_mod = lib::create( 'database\modifier' );
      $event_mod->where( 'event_type.name', 'like', 'completed %' );
      $event_mod->order_desc( 'datetime' );

      $event_list = $db_participant->get_event_list( $event_mod );
      $db_event = 0 < count( $event_list ) ? current( $event_list ) : NULL;
      $value = is_null( $db_event )
             ? 'DATE UNKNOWN'
             : util::get_formatted_date( $db_event->datetime );
    }
    else if( 'dcs phone_number' == $key )
    {
      $db_site = lib::create( 'business\session' )->get_site();
      $value = $db_site->phone_number;
    }
    else if( 'dcs address street' == $key )
    {
      $db_site = lib::create( 'business\session' )->get_site();
      $value = $db_site->address1.( is_null( $db_site->address2 ) ? '' : ', '.$db_site->address2 );
    }
    else if( 'dcs address city' == $key )
    {
      $db_site = lib::create( 'business\session' )->get_site();
      $value = $db_site->city;
    }
    else if( 'dcs address province' == $key )
    {
      $db_site = lib::create( 'business\session' )->get_site();
      $value = $db_site->get_region()->name;
    }
    else if( 'dcs address postal code' == $key )
    {
      $db_site = lib::create( 'business\session' )->get_site();
      $value = $db_site->postcode;
    }
    else if( false !== strpos( $key, 'alternate' ) )
    {
      $alternate_list = $db_participant->get_alternate_list();

      if( 'number of alternate contacts' == $key )
      {
        $value = count( $alternate_list );
      }
      else if(
        preg_match( '/alternate([0-9]+) (first_name|last_name|phone)/', $key, $matches ) )
      {
        $alt_number = intval( $matches[1] );
        $aspect = $matches[2];

        if( count( $alternate_list ) < $alt_number )
        {
          $value = '';
        }
        else
        {
          if( 'phone' == $aspect )
          {
            $phone_list = $alternate_list[$alt_number - 1]->get_phone_list();
            $value = is_array( $phone_list ) ? $phone_list[0]->number : '';
          }
          else
          {
            $value = $alternate_list[$alt_number - 1]->$aspect;
          }
        }
      }
    }
    else if( 'informant.count()' == $key )
    {
      $alternate_mod = lib::create( 'database\modifier' );
      $alternate_mod->where( 'informant', '=', true );
      $value = $db_participant->get_alternate_count( $alternate_mod );
    }
    else if( 'proxy.count()' == $key )
    {
      $alternate_mod = lib::create( 'database\modifier' );
      $alternate_mod->where( 'proxy', '=', true );
      $value = $db_participant->get_alternate_count( $alternate_mod );
    }
    else if( 'previously completed' == $key )
    {
      $tokens_class_name = lib::get_class_name( 'database\limesurvey\tokens' );
      $interview_id = lib::create( 'business\session')->get_current_assignment()->interview_id;

      // no need to set the token sid since it should already be set before calling this method
      $tokens_mod = lib::create( 'database\modifier' );
      $tokens_mod->where( 'token', 'like', $interview_id.'_%' );
      $tokens_mod->where( 'completed', '!=', 'N' );
      $value = $tokens_class_name::count( $tokens_mod );
    }

    return $value;
  }

  /**
   * This assignment's current sid
   * @var int
   * @access private
   */
  private $current_sid = NULL;

  /**
   * This assignment's current token
   * @var string
   * @access private
   */
  private $current_token = NULL;
}
