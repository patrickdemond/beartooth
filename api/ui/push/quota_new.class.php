<?php
/**
 * quota_new.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @filesource
 */

namespace beartooth\ui\push;
use cenozo\lib, cenozo\log, beartooth\util;

/**
 * push: quota new
 *
 * Create a new quota.
 */
class quota_new extends \cenozo\ui\push\base_new
{
  /**
   * Constructor.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args Push arguments
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( 'quota', $args );
  }

  /**
   * Processes arguments, preparing them for the operation.
   * 
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @access protected
   */
  protected function prepare()
  {
    parent::prepare();

    $this->set_machine_request_enabled( true );
    $this->set_machine_request_url( MASTODON_URL );
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

    // make sure the population column isn't blank
    $columns = $this->get_argument( 'columns' );
    if( !array_key_exists( 'population', $columns ) || 0 == strlen( $columns['population'] ) )
      throw lib::create( 'exception\notice',
        'The quota\'s population cannot be left blank.', __METHOD__ );
  }

  /**
   * Override the parent method to add the service name to the site key.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args An argument list, usually those passed to the push operation.
   * @return array
   * @access protected
   */
  protected function convert_to_noid( $args )
  {
    $args = parent::convert_to_noid( $args );
    $args['noid']['columns']['site']['service_id'] = array( 'name' => 
      lib::create( 'business\setting_manager' )->get_setting( 'general', 'application_name' ) );
    return $args;
  }
}
?>
