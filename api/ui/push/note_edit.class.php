<?php
/**
 * note_edit.class.php
 * 
 * @author Patrick Emond <emondpd@mcmaster.ca>
 * @package beartooth\ui
 * @filesource
 */

namespace beartooth\ui\push;
use cenozo\lib, cenozo\log, beartooth\util;

/**
 * Extends the parent class to send machine requests.
 * @package beartooth\ui
 */
class note_edit extends \cenozo\ui\push\note_edit
{
  /**
   * Constructor.
   * @author Patrick Emond <emondpd@mcmaster.ca>
   * @param array $args Push arguments
   * @access public
   */
  public function __construct( $args )
  {
    parent::__construct( $args );

    // only send machine requests for participant notes
    if( 'participant' == $this->get_argument( 'category' ) )
    {
      $this->set_machine_request_enabled( true );
      $this->set_machine_request_url( MASTODON_URL );
    }
  }
}
?>
