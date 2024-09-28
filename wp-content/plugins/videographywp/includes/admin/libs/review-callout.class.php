<?php

class CVWP_Review_Callout{
	/**
	 * Stores option name from database
	 * @var string
	 */
	private $option_name;
	/**
	 * Stores the number of days that must pass 
	 * before showing users the notice
	 * @var interger - number of days
	 */
	private $delay;
	/**
	 * The message object that should be displayed
	 * @var CVWP_Message
	 */
	private $message;
	/**
	 * The minimum user capability that will see the notice
	 * @var string - user capability
	 */
	private $user;
		
	/**
	 * 
	 * @param string $option_name - name of the option that will be stored in DB
	 * @param number $delay
	 */
	public function __construct( $option_name, CVWP_Message $message, CVWP_User $user, $delay = 7 ){
		$this->option_name 	= $option_name;
		$this->delay 		= $delay;
		$this->message 		= $message;
		$this->user 		= $user;
		
		// pass user object to message object
		$this->message->set_user( $this->user );
		
		// add the admin notice
		add_action( 'admin_notices', array( $this, 'show_message' ) );		
	}
	
	/**
	 * 
	 * @param CVWP_Message $message
	 */
	public function show_message(){
		// check if user can see the message
		if( !$this->user->can_see() ){
			return;
		}	
		
		// get plugin option
		$option = $this->get_option();
		
		// check if timer is expired
		if( $this->timer_expired() ){
			$this->message->display();			
		}
	}
	
	/**
	 * Get option from database
	 * @return array - the option
	 */
	private function get_option(){		
		$option = get_option( $this->option_name, false );
		if( !$option ){
			$option = $this->_set_option();
		}
		return $option;
	}
	
	/**
	 * Set option in database
	 * @param string $status not_set: user hasn't had the chance to do anything; 
	 * 						 refused: user refused to review; 
	 * 						 reviewed: user already reviewed; 
	 * 						 later: user will review later.
	 * @return array - the option
	 */
	private function _set_option(){
		$option = array(
			'timestamp' => time()	
		);
		
		update_option( $this->option_name, $option );
		return $option;
	}
	
	/**
	 * Returns days delay in seconds
	 * @return integer - number of seconds
	 */
	private function delay_in_seconds(){
		return DAY_IN_SECONDS * $this->delay;
	}
	
	/**
	 * Check if DB timer is expired
	 * @param boolean $extended - when true, $this->delay will be doubled
	 * @return boolean - timer is expired (true) or not (false)
	 */
	private function timer_expired( $extended = false ){
		$option = $this->get_option();
		$delay = $this->delay_in_seconds();
		if( $extended ){
			$delay *= 2;
		}		
		return ( time() - $option['timestamp'] >= $delay );		
	}
}

class CVWP_Message{
	/**
	 * Store user reference
	 * @var unknown
	 */
	private $user;
	/**
	 * Review page URL
	 * @var string
	 */
	private $review_url;
	/**
	 * Message that will be presented to user
	 * @var string
	 */
	private $message;
	
	/**
	 * Constructor
	 * @param string $message
	 * @param string $review_url
	 */
	public function __construct( $message, $review_url ){
		$this->review_url = $review_url;
		$this->message = $message;
	}	
	
	/**
	 * Saves the CVWP_User instance needed to display message actions
	 * @param CVWP_User $user
	 */
	public function set_user( CVWP_User $user ){
		$this->user = $user;
	}
	
	/**
	 * Displays the message passed to the constructor
	 * including the footer links with user options
	 */
	public function display(){
?>	
<div class="notice notice-success is-dismissible">
	<p><?php echo $this->message;?></p>
	<p><?php $this->note_footer();?></p>
</div>	
<?php 	
	}
	
	/**
	 * Generates the note footer containing links for various actions
	 * @param string $echo
	 */
	private function note_footer( $echo = true ){
		$template = '<a class="" href="%1$s" title="%2$s">%2$s</a>';
		$links = array(
			sprintf( $template, $this->review_url, __( "Sure, I'd love to!", 'videographywp' ) ),	
			sprintf( $template, esc_url( add_query_arg( $this->user->get_query_arg( 'yes' ) ) ), __( 'No, thanks.', 'videographywp' ) ),
			sprintf( $template, esc_url( add_query_arg( $this->user->get_query_arg( 'yes' ) ) ), __( "I've already given a review.", 'videographywp' ) ),
			sprintf( $template, esc_url( add_query_arg( $this->user->get_query_arg( 'later' ) ) ), __( 'Ask me later.', 'videographywp' ) ),
		);
		
		$output = implode( " &middot; " , $links );
		
		if( $echo ){
			echo $output;
		}
		
		return $output;
	}
}

class CVWP_User{
	
	/**
	 * Stores the minimum capability that the user must
	 * have
	 * @var string
	 */
	private $capability;
	/**
	 * Store user meta name that will contain user choices.
	 * @var string
	 */
	private $meta_name;
	
	/**
	 * Constructor
	 * @param string $capability
	 */
	public function __construct( $meta_name = 'pl_ignore_notice_nag', $capability = 'manage_options' ){
		$this->capability = $capability;
		$this->meta_name = $meta_name;
		
		add_action( 'admin_init' , array( $this, 'check_option' ), 9999999 );
	}
	
	/**
	 * Checks if currently logged in user is authorized to view the content
	 * based on his capability
	 * @return boolean - can view (true) or can't view (false) the content
	 */
	public function can_see(){
		if( !is_user_logged_in() || !current_user_can( $this->capability )){
			return false;
		}
		
		$preference = $this->get_user_preference();
		if( $preference ){
			if( 'yes' == $preference['answer'] ){
				return false;
			}else if( 'later' == $preference['answer'] ){
				return ( $preference['time'] + WEEK_IN_SECONDS ) <= time();
			}
		}	
		// if no preference set, user hasn't made a choice so he can view the message
		return true;
	}
	
	/**
	 * Checks if user clicked on an option into the message and stores it
	 */
	public function check_option(){
		if( isset( $_GET[ $this->meta_name ] ) ){
			$answers = array( 'yes', 'later' );
			if( in_array( $_GET[ $this->meta_name ] , $answers ) ){
				$this->set_user_preference( $_GET[ $this->meta_name ] );
			}
		}		
	}
	
	/**
	 * Returns the option chosen by the user from db
	 * @return string
	 */
	private function get_user_preference(){
		return get_user_meta( get_current_user_id(), $this->meta_name, true );		
	}
	
	/**
	 * Sets user preference
	 * @param string $preference
	 */
	private function set_user_preference( $preference ){
		$option = array(
			'answer' 	=> $preference,
			'time'		=> time()	
		);
		update_user_meta( get_current_user_id() , $this->meta_name, $option );		
	}
	
	/**
	 * Returns the paramter that must be set on GET to trigger 
	 * user option storage
	 * @param string $type
	 */
	public function get_query_arg( $type = 'no' ){
		$r = array();
		$r[ $this->meta_name ] = $type;
		return $r;
	}
}