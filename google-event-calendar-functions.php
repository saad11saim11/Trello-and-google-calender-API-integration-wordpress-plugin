<?php 
/*
 * Plugin Name: Google Event Calendar
 * Plugin URI:  https://saimjaved.me/
 * Description: Google Event Calendar Functions
 * Author: Saim.
 * Author URI: https://saimjaved.me/
 * Version: 1.0.0
 * Requires at least: 1.0
 * Tested up to: 4.8
 * Text Domain: wp-event-calendar
 *
 * Copyright (c) 2022 Google event calendar functions
 *
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( !class_exists('google_event_calender_functions') ) {  
	

	 class google_event_calender_functions {

		function __construct() {
			add_action( 'admin_enqueue_scripts',  array($this,'event_calendar_scripts') );
			add_action( 'admin_menu', array($this,'add_menu_submenu'));
			add_action( 'init', array($this,'manual_import'));	

			add_action('init', array( $this, 'wxc_activation' ));
			add_filter( 'cron_schedules', array( $this,'isa_add_every_five_minutes') );
			add_action( 'isa_add_every_five_minutes',  array( $this,'every_five_minutes_event_func') );
		}

       /*Add script */
        function event_calendar_scripts() {
			wp_enqueue_style( 'event_calendar-style', plugins_url('/assets/css/style.css', __FILE__), array() );
		}


		/*Add menu start */
		function add_menu_submenu() {
			
			/*Add menu Google Calendar*/ 
			add_menu_page(
				'Google Event Calendar', 
				'Google Event Calendar',
				'manage_options',
				'google-calendar',
				array( $this, 'google_event_calendar_content' ), 
				'dashicons-calendar-alt', 
				5 
			);
			/*Add sub menu Google Setting*/ 
			add_submenu_page(
					'google-calendar',
					'Google Setting',
					'Google Setting',
					'manage_options',
					'google-setting',
					array( $this, 'google_setting_content' )
			);

			/*Add sub menu Trello Setting*/
			add_submenu_page(
					'google-calendar', 
					'Trello Setting',
					'Trello Setting',  
					'manage_options',
					'trello-setting', 
					array( $this, 'trello_setting_content' )
			);

		}
		/*Add menu end */


		function google_event_calendar_content(){ ?>
         <div class="wrap">
			<h1>Google Event Calendar</h1>
			<?php
			/////*********Google setting */
			$APPLICATION_ID = get_option('google_application_id');
		    $APPLICATION_SECRET = get_option('google_application_secret');
			$APPLICATION_REDIRECT_URL = get_option('google_application_redirect_url');
			
			//////******** Trello setting */
			$key = get_option('trello_application_key');
			$token = get_option('trello_application_token');
			//$boardID = '628b70990bfb6e4ff55e65ae';
			$boardID = get_option('trello_board_id');


			if(isset($_GET['code'])) {
			   update_option('code',$_GET['code']);
			}

			//echo get_option('access_token');
			//if(empty(get_option('access_token'))){

			$url  = 'https://accounts.google.com/o/oauth2/auth?scope=' . urlencode('https://www.googleapis.com/auth/calendar') . '&redirect_uri=' . $APPLICATION_REDIRECT_URL . '&response_type=code&client_id=' . $APPLICATION_ID . '& include_granted_scopes=true&access_type=offline';
			
			echo '<h3><a href="'.$url.'">click here add event from trello with google login</a></h3>';
			//}
			?>
			<?php echo stripslashes(get_option('google_calendar_iframe')); ?>
		 </div>
		<?php }



		function google_setting_content() {
            $success = '';
			if( isset($_POST['google_settings']) && ($_POST['google_settings']=='google_settings') ){
				update_option('google_application_id',$_POST['google_application_id']);
				update_option('google_application_secret',$_POST['google_application_secret']);
				update_option('google_application_redirect_url',$_POST['google_application_redirect_url']);
				update_option('google_calendar_iframe',$_POST['google_calendar_iframe']);

				$success = 1;
			}
			
			?>
			<div class="wrap">

			    <h1>Google Settings</h1>
				
			   <?php if( empty(get_option('google_application_id')) ){ ?>
					<div id="setting-error-settings_updated" class="notice notice-error settings-error is-dismissible"> 
						<p><strong>Application ID required.</strong></p>
					</div>
				<?php }elseif(empty(get_option('google_application_secret')) ){ ?>
					<div id="setting-error-settings_updated" class="notice notice-error settings-error is-dismissible"> 
						<p><strong>Application Secret.</strong></p>
					</div>
				<?php }elseif(  empty(get_option('google_application_redirect_url')) ){ ?>
					<div id="setting-error-settings_updated" class="notice notice-error settings-error is-dismissible"> 
						<p><strong>Application Redirect URL required.</strong></p>
					</div>
				<?php }elseif( empty(get_option('google_calendar_iframe')) ){ ?>
					<div id="setting-error-settings_updated" class="notice notice-error settings-error is-dismissible"> 
						<p><strong>Calendar Iframe required.</strong></p>
					</div>
				<?php }else{ } ?>


				<?php if( !empty($success) ){ ?>
					<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible"> 
						<p><strong>Update successfully.</strong></p>
					</div>
				<?php } ?>
				<form method="post" class="setting" action="">
				   <div class="form-field">
					<label><strong>Application ID</strong></label>
					<input type="text" name="google_application_id"  value="<?php echo get_option('google_application_id'); ?>" required>
				</div>
				<div class="form-field">
					<label><strong>Application Secret</strong></label>
					<input type="text" name="google_application_secret" value="<?php echo get_option('google_application_secret'); ?>" required>
				</div>
				<div class="form-field">
					<label><strong>Application Redirect URL</strong></label>
					<input type="text" name="google_application_redirect_url" value="<?php echo get_option('google_application_redirect_url'); ?>" required>
				</div>
				<div class="form-field">
					<label><strong>Calendar Iframe</strong></label>
					<textarea id='google_calendar_iframe' name='google_calendar_iframe' rows='7' cols='100' required><?php echo stripslashes(get_option('google_calendar_iframe')); ?></textarea>
				</div>
					<input type="hidden" value="google_settings" name="google_settings">
					<p class="submit"><input type="submit" name="submit" class="button button-primary" value="Update"></p>
				</form>
				<a href="https://console.cloud.google.com/" target="_blank">Click here to  google developer console</a>
			</div>
			<?php		
		}


		function trello_setting_content() {
            $success = '';
			if( isset($_POST['trello_settings']) && ($_POST['trello_settings']=='trello_settings') ){
				update_option('trello_application_key',$_POST['trello_application_key']);
				update_option('trello_application_token',$_POST['trello_application_token']);
				update_option('trello_board_id',$_POST['trello_board_id']);
				$success = 1;
			}
			?>
			<div class="wrap">
			   <h1>Trello Settings</h1>
			   <?php if( empty(get_option('trello_application_key')) ){ ?>
					<div id="setting-error-settings_updated" class="notice notice-error settings-error is-dismissible"> 
						<p><strong>Application ID required.</strong></p>
					</div>
				<?php }elseif(empty(get_option('trello_application_token')) ){ ?>
					<div id="setting-error-settings_updated" class="notice notice-error settings-error is-dismissible"> 
						<p><strong>Application token required.</strong></p>
					</div>
				<?php }elseif(empty(get_option('trello_board_id')) ){ ?>
				    <div id="setting-error-settings_updated" class="notice notice-error settings-error is-dismissible"> 
					<p><strong>Board Id required.</strong></p>
				</div>
				<?php }else{ } ?>


				<?php if( !empty($success) ){ ?>
					<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible"> 
						<p><strong>Update successfully.</strong></p>
					</div>
				<?php } ?>
				<form method="post" class="setting" action="">
				   <div class="form-field">
					<label><strong>Application ID</strong></label>
					<input type="text" name="trello_application_key"  value="<?php echo get_option('trello_application_key'); ?>" required>
				</div>
				<div class="form-field">
					<label><strong>Application token</strong></label>
					<input type="text" name="trello_application_token" value="<?php echo get_option('trello_application_token'); ?>" required>
				</div>
				<div class="form-field">
					<label><strong>Board Id</strong></label>
					<input type="text" name="trello_board_id" value="<?php echo get_option('trello_board_id'); ?>" required>
				</div>
					<input type="hidden" value="trello_settings" name="trello_settings">
					<p class="submit"><input type="submit" name="submit" class="button button-primary" value="Update"></p>
				</form>
				<h4><a href="https://trello.com/app-key" target="_blank">Click here to get api key</a></h4>
			</div>
			<?php		
		}
      //////////////*****************Create event api call start  ****************** *//////////////////

	  public function GetAccessTokenRefresh($client_id, $redirect_uri, $client_secret, $code)
    {
        $url = 'https://accounts.google.com/o/oauth2/token';
 
        $curlPost = 'client_id=' . $client_id . '&redirect_uri=' . $redirect_uri . '&client_secret=' . $client_secret . '&code=' . $code . '&grant_type=authorization_code';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code != 200) {
            $data = false;
        }
        return $data;
    }
	public function GetAccessTokenWithRefresh($client_id, $client_secret, $refresh_token)
    {
        $url = 'https://accounts.google.com/o/oauth2/token';
 
        $curlPost = 'client_id=' . $client_id .'&client_secret=' . $client_secret . '&refresh_token=' . $refresh_token . '&grant_type=refresh_token';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code != 200) {
            $data = false;
        }
        return $data;  
    }
    public function GetUserCalendarTimezone($access_token)
    {
        $url_settings = 'https://www.googleapis.com/calendar/v3/users/me/settings/timezone';
 
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_settings);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $access_token));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        $data = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code != 200){
			$data = false;
		}
        // throw new Exception('Error : Failed to get timezone');
        // print_r($data);die;
        return $data['value'];
    }
 
 
    public function CreateCalendarEvent($calendar_id, $summary, $event_desc, $all_day, $event_time, $event_timezone, $access_token)
    {
        $url_events = 'https://www.googleapis.com/calendar/v3/calendars/' . $calendar_id . '/events';
 
        $curlPost = array('summary' => $summary, 'description' => $event_desc);
        if ($all_day == 1) {
            $curlPost['start'] = array('dateTime' => $event_time['event_date'], 'timeZone' => $event_timezone);
            $curlPost['end'] = array('dateTime' => $event_time['event_date'], 'timeZone' => $event_timezone);
        } else {
            $curlPost['start'] = array('dateTime' => $event_time['start_time'], 'timeZone' => $event_timezone);
            $curlPost['end'] = array('dateTime' => $event_time['end_time'], 'timeZone' => $event_timezone);
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url_events);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $access_token, 'Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($curlPost));
        $data = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code != 200)
            throw new Exception('Error : Failed to create event');
 
        return $data['id'];
    }
    public function getTrelloEvent($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_POST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = json_decode(curl_exec($ch), true);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($http_code != 200) {
            throw new Exception('Error : Failed to receieve access token');
        }
        return $data;
    }
	  //////////////*****************Create event api call end  ****************** *//////////////////


	  function manual_import(){
		if($_GET['test']){

			/////*********Google setting */
			$APPLICATION_ID = get_option('google_application_id');
		    $APPLICATION_SECRET = get_option('google_application_secret');
			$APPLICATION_REDIRECT_URL = get_option('google_application_redirect_url');
			
			//////******** Trello setting */
			$key = get_option('trello_application_key');
			$token = get_option('trello_application_token');
			$boardID = get_option('trello_board_id');

			$CODE = get_option('code');

			$data = $this->GetAccessTokenRefresh($APPLICATION_ID, $APPLICATION_REDIRECT_URL, $APPLICATION_SECRET, $CODE);

			if(!empty($data['refresh_token'])){
			   update_option('refresh_token', $data['refresh_token']);	
			}
			$refresh_token = get_option('refresh_token');
			$dataVal = $this->GetAccessTokenWithRefresh($APPLICATION_ID, $APPLICATION_SECRET, $refresh_token);
			
			$access_token = $dataVal['access_token'];

			if( !empty($access_token) ){
			  update_option('access_token', $access_token);
			}


		    $get_access_token =  get_option('access_token');
			$user_timezone = $this->GetUserCalendarTimezone($get_access_token);

            if(empty($user_timezone) ){
				update_option('access_token', "");
				echo "Access token expired! please login again.";die;
			  }
             
			$calendar_id = 'primary';

                ///trello event
				$cardItems = array();
				$existingCardId = get_option('_trello_card_id');
				if(empty($existingCardId)){
					$existingCardId = array();
				}
                
				$boardlisturl = 'https://api.trello.com/1/boards/'.$boardID.'/lists?key='.$key.'&token='.$token;
					$boardList = $this->getTrelloEvent($boardlisturl);
					foreach($boardList as $BoardVal){
					
						$boardListId = $BoardVal['id']; 
						$cardUrl = 'https://api.trello.com/1/lists/'.$boardListId.'/cards?key='.$key.'&token='.$token;
						$cardList = $this->getTrelloEvent($cardUrl);
						foreach($cardList as $cardVal){
							$trellocardId = $cardVal['id'];
							$event_title = $cardVal['name'];
							$event_desc = $cardVal['desc'];
							$endDateArray = explode(".",$cardVal['due']);
							$startDateArray = explode(".",$cardVal['start']);

							if(!in_array($trellocardId,$existingCardId)){

								if(!empty($cardVal['due'])){
									$event_date = $endDateArray[0];
									$full_day_event = 1;
									$event_time = ['event_date' => $event_date];

									// Create event on primary calendar
									$event_id = $this->CreateCalendarEvent($calendar_id, $event_title, $event_desc, $full_day_event, $event_time, $user_timezone, $get_access_token);
									$cardItems[] = $trellocardId;
									
									?>
									<div id="setting-error-settings_updated" class="notice notice-success settings-error is-dismissible"> 
										<p><strong>New event added successfully!. Event name:- <?php echo $event_title?></strong></p>
									</div>
									<?php
									
								}
								
						    }
						}
						
					}
					
					$totalCardIdArray = array_merge($existingCardId, $cardItems);
					update_option('_trello_card_id', $totalCardIdArray);

			die();
		}
	  }





	function isa_add_every_five_minutes( $schedules ) {
		$schedules['every_five_minutes'] = array(
				'interval'  => 300,
				'display'   => __( 'Every 5 Minutes', 'textdomain' )
		);
		return $schedules;
	}
	
	
	function wxc_activation() {
		// Schedule an action if it's not already scheduled
		if ( ! wp_next_scheduled( 'isa_add_every_five_minutes' ) ) {
			wp_schedule_event( time(), 'every_five_minutes', 'isa_add_every_five_minutes' );
		}
	}
	
	
	// Hook into that action that'll fire every five minutes
	function every_five_minutes_event_func() {

		/////*********Google setting */
		$APPLICATION_ID = get_option('google_application_id');
		$APPLICATION_SECRET = get_option('google_application_secret');
		$APPLICATION_REDIRECT_URL = get_option('google_application_redirect_url');
		
		//////******** Trello setting */
		$key = get_option('trello_application_key');
		$token = get_option('trello_application_token');
		$boardID = get_option('trello_board_id');

		$CODE = get_option('code');

		$data = $this->GetAccessTokenRefresh($APPLICATION_ID, $APPLICATION_REDIRECT_URL, $APPLICATION_SECRET, $CODE);

		if(!empty($data['refresh_token'])){
			update_option('refresh_token', $data['refresh_token']);	
		}
		$refresh_token = get_option('refresh_token');
		$dataVal = $this->GetAccessTokenWithRefresh($APPLICATION_ID, $APPLICATION_SECRET, $refresh_token);
		
		$access_token = $dataVal['access_token'];

		if( !empty($access_token) ){
			update_option('access_token', $access_token);
		}


		$get_access_token =  get_option('access_token');
		$user_timezone = $this->GetUserCalendarTimezone($get_access_token);

		if(empty($user_timezone) ){
			update_option('access_token', "");
			echo "Access token expired! please login again.";die;
			}
			
		$calendar_id = 'primary';

		///trello event
		$cardItems = array();
		$existingCardId = get_option('_trello_card_id');
		if(empty($existingCardId)){
			$existingCardId = array();
		}
		
		$boardlisturl = 'https://api.trello.com/1/boards/'.$boardID.'/lists?key='.$key.'&token='.$token;
			$boardList = $this->getTrelloEvent($boardlisturl);
			foreach($boardList as $BoardVal){
			
				$boardListId = $BoardVal['id']; 
				$cardUrl = 'https://api.trello.com/1/lists/'.$boardListId.'/cards?key='.$key.'&token='.$token;
				$cardList = $this->getTrelloEvent($cardUrl);
				foreach($cardList as $cardVal){
					$trellocardId = $cardVal['id'];
					$event_title = $cardVal['name'];
					$event_desc = $cardVal['desc'];
					$endDateArray = explode(".",$cardVal['due']);
					$startDateArray = explode(".",$cardVal['start']);

					if(!in_array($trellocardId,$existingCardId)){

						if(!empty($cardVal['due'])){
							$event_date = $endDateArray[0];
							$full_day_event = 1;
							$event_time = ['event_date' => $event_date];

							// Create event on primary calendar
							$event_id = $this->CreateCalendarEvent($calendar_id, $event_title, $event_desc, $full_day_event, $event_time, $user_timezone, $get_access_token);
							$cardItems[] = $trellocardId;
							
						}
						
					}
				}
				
			}
			
			$totalCardIdArray = array_merge($existingCardId, $cardItems);
			update_option('_trello_card_id', $totalCardIdArray);

	}
	



	}
}
$GLOBALS['google_event_calender_functions'] = new google_event_calender_functions();


