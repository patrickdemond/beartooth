<?php
/**
 * ui.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace beartooth\ui;
use cenozo\lib, cenozo\log, beartooth\util;

/**
 * Application extension to ui class
 */
class ui extends \cenozo\ui\ui
{
  /**
   * Extends the parent method
   */
  protected function get_module_list( $modifier = NULL )
  {
    $module_list = parent::get_module_list( $modifier );

    // add child actions to certain modules
    if( array_key_exists( 'assignment', $module_list ) )
      $module_list['assignment']['children'] = array( 'phone_call' );
    if( array_key_exists( 'interview', $module_list ) )
      $module_list['interview']['children'] = array( 'assignment', 'appointment', 'callback' );
    if( array_key_exists( 'onyx_instance', $module_list ) )
      $module_list['onyx_instance']['children'] = array( 'activity' );
    if( array_key_exists( 'participant', $module_list ) )
      array_unshift( $module_list['participant']['children'], 'interview' );
    if( array_key_exists( 'qnaire', $module_list ) )
    {
      $module_list['qnaire']['children'] = array( 'appointment_type', 'queue_state' );
      $module_list['qnaire']['choosing'] = array( 'event_type', 'quota' );
    }
    if( array_key_exists( 'queue', $module_list ) )
    {
      $module_list['queue']['children'] = array( 'queue_state' );
      $module_list['queue']['choosing'] = array( 'participant' );
    }
    if( array_key_exists( 'site', $module_list ) )
      array_unshift( $module_list['site']['children'], 'queue_state' );

    return $module_list;
  }

  /**
   * Extends the parent method
   */
  protected function get_list_items( $module_list )
  {
    $list = parent::get_list_items( $module_list );
    $db_role = lib::create( 'business\session' )->get_role();

    // add application-specific states to the base list
    if( array_key_exists( 'interview', $module_list ) && $module_list['interview']['list_menu'] )
      $list['Interviews'] = 'interview';
    if( array_key_exists( 'onyx_instance', $module_list ) && $module_list['onyx_instance']['list_menu'] )
      $list['Opal Instances'] = 'onyx_instance';
    if( array_key_exists( 'qnaire', $module_list ) && $module_list['qnaire']['list_menu'] )
      $list['Questionnaires'] = 'qnaire';
    if( array_key_exists( 'queue', $module_list ) && $module_list['queue']['list_menu'] )
      $list['Queues'] = 'queue';

    return $list;
  }

  /**
   * Extends the parent method
   */
  protected function get_utility_items()
  {
    $list = parent::get_utility_items();
    $db_site = lib::create( 'business\session' )->get_site();
    $db_role = lib::create( 'business\session' )->get_role();

    // add application-specific states to the base list
    if( in_array( $db_role->name, array( 'helpline', 'coordinator', 'interviewer' ) ) )
      $list['Assignment Home'] = array( 'subject' => 'assignment', 'action' => 'home' );
    if( 2 <= $db_role->tier )
      $list['Queue Tree'] = array( 'subject' => 'queue', 'action' => 'tree' );
    if( !$db_role->all_sites && 1 < $db_role->tier )
    {
      $list['Site Details'] = array(
        'subject' => 'site',
        'action' => 'view',
        'identifier' => sprintf( 'name=%s', $db_site->name ) );
    }
    if( !$db_role->all_sites || 'helpline' == $db_role->name )
    {
      $list['Home Appointment Calendar'] = array(
        'subject' => 'appointment',
        'action' => 'calendar',
        'identifier' => sprintf( 'name=%s;type=home', $db_site->name ) );
      $list['Site Appointment Calendar'] = array(
        'subject' => 'appointment',
        'action' => 'calendar',
        'identifier' => sprintf( 'name=%s;type=site', $db_site->name ) );
    }

    return $list;
  }
}