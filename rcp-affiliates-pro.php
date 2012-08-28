<?php
/*
Plugin Name: Restrict Content Pro - Affiliates Pro
Plugin URL: http://pippinsplugins.com/rcp-affiliates-pro
Description: Restrict Content Pro Integration for Affiliates Pro
Version: 1.0
Author: Pippin Williamson
Author URI: http://pippinsplugins.com
Contributors: mordauk
*/

function rcp_award_commission( $payment_id, $payment_data, $amount ) {

  global $rcp_options;

	$user_info = maybe_unserialize( $payment_data['user_info'] );

  $user_link = '<a href="' . admin_url( 'admin.php?page=rcp-members&view_member=' . $payment_data['user_id'] ) . '">' . __('View User', 'rcp') . '</a>';

	$data = array(
      'order_id' => array(
          'title' => __('Payment #', 'rcp'),
          'domain' => 'rcp',
          'value' => esc_sql( $payment_id )
      ),
      'order_total' => array(
          'title' => __('Total', 'rcp'),
          'domain' =>  'rcp',
          'value' => esc_sql( $amount )
      ),
      'referred_user' => array(
      	'title' => __('Referred User', 'rcp'),
      	'domain' => 'rcp',
      	'value' => esc_sql( $user_link )
      )
	);
	
  $description = sprintf( __('Payment #%s', 'rcp'), $payment_id );

  if( class_exists( 'Affiliates_Referral_WordPress' ) ) {

  	/*****************************************
    * Record referral for pro version
    *****************************************/
    $r = new Affiliates_Referral_WordPress();
    $r->evaluate( 0, $description, $data, $amount, null, $rcp_options['currency'], AFFILIATES_REFERRAL_STATUS_ACCEPTED, 'sale', $payment_id );

  } elseif( function_exists( 'affiliates_suggest_referral' ) ) {
    /*****************************************
    * Record referral for free version
    *****************************************/
    $referral_rate = get_option( 'aff_def_ref_calc_value' );
    $amount = round( floatval( $referral_rate ) * floatval( $amount ), AFFILIATES_REFERRAL_AMOUNT_DECIMALS );
    affiliates_suggest_referral( 0, $description, $data, $amount, $rcp_options['currency'] );
  }
}
add_action('rcp_insert_payment', 'rcp_award_commission', 10, 3);