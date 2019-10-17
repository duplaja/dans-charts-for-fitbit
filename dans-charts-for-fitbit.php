<?php
/*
Plugin Name: Dan's Charts for Fitbit
Plugin URI: https://codeable.io/developers/dan-dulaney/
Description: Display on your site various charts, directly from Fitbit's API
Version: 1.1
Author: Dan Dulaney
Author URI: https://codeable.io/developers/dan-dulaney/
License: GPLv2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

    Copyright 2019 by Dan Dulaney <dan.dulaney07@gmail.com>

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License version 2,
    as published by the Free Software Foundation.

    You may NOT assume that you can use any other version of the GPL.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    The license for this software can likely be found here:
    http://www.gnu.org/licenses/gpl-2.0.html

*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

//Handles checking for Updates from Github
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://github.com/duplaja/dans-charts-for-fitbit',
	__FILE__,
	'dans-charts-for-fitbit'
);
$myUpdateChecker->setBranch('master');


use djchen\OAuth2\Client\Provider\Fitbit;
require_once(dirname( __FILE__ ).'/vendor/autoload.php');

    
if ( ! function_exists( 'dans_fitbit_chart_enqueue_scripts' ) ) {
    /**
     * Enqueues chart script and ChartJS base.
     *
     * @param None
     * @return None
     */
    add_action( 'wp_enqueue_scripts', 'dans_fitbit_chart_enqueue_scripts' );
    function dans_fitbit_chart_enqueue_scripts() {
        
        //Local Stored copy of ChartJS
        wp_register_script( 'chartjs', plugin_dir_url( __FILE__ ) . 'js/Chart.min.js', array(), '1.0' );
        
        //Script to generate Weight Chart, from passed variables via Localize
        wp_register_script( 'generate-fitbit-chart', plugin_dir_url( __FILE__ ) . 'js/generate-chart.js', array('chartjs','jquery'), '0.9' ); 
        
    }
}

/****************************************************************
* Settings page information
****************************************************************/

if ( ! function_exists( 'dans_fitbit_chart_plugin_menu' ) ) {
    /**
     * Creates a menu page with the following settings
     *
     * @param None
     * @return None
     */
    function dans_fitbit_chart_plugin_menu() {
        add_submenu_page('tools.php', 'Fitbit Chart', 'Fitbit Chart', 'manage_options', 'dans-fitbit-chart', 'dans_fitbit_chart_display_settings');
    }
    add_action('admin_menu', 'dans_fitbit_chart_plugin_menu');
}

if ( ! function_exists( 'dans_fitbit_chart_settings' ) ) {
    /**
     * Sets up the following settings (wp options) for the plugin
     *
     * @param None
     * @return None
     */

    function dans_fitbit_chart_settings() {
        register_setting( 'dans_fitbit_chart_settings_group', 'fitbit_app_client_id' ); //client ID
        register_setting( 'dans_fitbit_chart_settings_group', 'fitbit_app_client_secret' ); //client secret
        register_setting( 'dans_fitbit_chart_settings_group', 'fitbit_app_client_callback_url' ); //callback URL
        register_setting( 'dans_fitbit_chart_settings_group', 'fitbit_app_client_token' ); //callback URL
        register_setting( 'dans_fitbit_chart_settings_group', 'fitbit_app_client_refresh_token' ); //callback URL

    }
    add_action( 'admin_init', 'dans_fitbit_chart_settings' );
}


if ( ! function_exists( 'dans_fitbit_chart_display_settings' ) ) {
    /**
     * Code for the settings page. Also handles the initial authorization for Fitbit API
     *
     * @param
     * @return
     */

    function dans_fitbit_chart_display_settings() {

        //form to save api key and calendar settings
        echo "<form method=\"post\" action=\"options.php\">";
        settings_fields( 'dans_fitbit_chart_settings_group' );
        do_settings_sections( 'dans_fitbit_chart_settings_group' );
        echo "<div><h1>Fitbit Charts Settings</h1><h4>Powered by <a href='https://fitbit.com' target='_blank'>Fitbit</a>'s Web API and <a href='https://www.chartjs.org/' target='_blank'>ChartJS</a></h4>
        <p></p>
        
        <table id=\"fitbit-option-settings\" class=\"form-table\">
        <tr><td>Fitbit Client ID</td><td><input type='text' name='fitbit_app_client_id' value='".esc_attr( get_option('fitbit_app_client_id') )."'></td><td>Client ID from Your Fitbit App</td></tr> 
        
        <tr><td>Fitbit Client Secret</td><td><input type='password' name='fitbit_app_client_secret' value='".esc_attr( get_option('fitbit_app_client_secret') )."'></td><td>Client Secret from Your Fitbit App</td></tr> 

        <tr><td>Fitbit Callback URL</td><td><input type='text' name='fitbit_app_client_callback_url' value='".esc_attr( get_option('fitbit_app_client_callback_url') )."'></td><td>Callback URL from Your Fitbit App<br>This should be the url for THIS PAGE</td></tr> 


        </table>";
        submit_button();

        $callback_url = esc_attr( get_option('fitbit_app_client_callback_url') );
        $client_id = esc_attr( get_option('fitbit_app_client_id') );
        $client_secret = esc_attr( get_option('fitbit_app_client_secret') );


        if(!empty($callback_url) && !empty($client_id) && !empty($client_secret)) {

            $provider = new Fitbit([
                'clientId'          => "$client_id",
                'clientSecret'      => "$client_secret",
                'redirectUri'       => "$callback_url"
            ]);

            if(isset($_GET['code'])) {

                try {
            
                    // Try to get an access token using the authorization code grant.
                    $accessToken = $provider->getAccessToken('authorization_code', [
                        'code' => $_GET['code']
                    ]);
            
                    // We have an access token, which we may use in authenticated
                    // requests against the service provider's API.
                    echo 'Website Authenticated';
            
                    update_option( 'fitbit_app_client_token', $accessToken->getToken());

                    update_option( 'fitbit_app_client_refresh_token', $accessToken->getRefreshToken());

                    $request = $provider->getAuthenticatedRequest(
                        Fitbit::METHOD_GET,
                        Fitbit::BASE_FITBIT_API_URL . '/1/user/-/profile.json',
                        $accessToken,
                        ['headers' => [Fitbit::HEADER_ACCEPT_LANG => 'en_US'], [Fitbit::HEADER_ACCEPT_LOCALE => 'en_US']]
        
                    );
                    // Make the authenticated API request and get the parsed response.
                    $response = $provider->getParsedResponse($request);
            
            
                } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
            
                    // Failed to get the access token or user details.
                    exit($e->getMessage());
            
                }
            
            } 
            else {

                $authorization_url = $provider->getAuthorizationUrl();

                echo "<a href='$authorization_url'>Click Here To Authorize / Force Reauthorization</a>";
            }
        }
    }
}


    
if ( ! function_exists( 'dans_fitbit_chart_display' ) ) {
    /**
     * Shortcode function to add markup for chart, and localize script / call from API
     *
     * @param $atts (shortcode attributes, see shortcode_atts array for details)
     * @return $html_return_string (canvas for the graph)
     */
    add_shortcode( 'dans_fitbit_display', 'dans_fitbit_chart_display' );
    function dans_fitbit_chart_display($atts) {

        $a = shortcode_atts( array(
            'start_date' =>  date('Y-m-d'), //Must be in yyyy-mm-dd format
            'end_date' =>  date('Y-m-d'), //Must be in yyyy-mm-dd format
            'chart_title' => 'Daily Steps', //Title at top of chart
            'dataset_label' => 'Daily Steps', //Label for the dataset
            'is_adf' => 'no', //Toggles on an extra legend + alternating color dots
            'type' => 'steps', //valid: steps, weight, distance, calories_in, calories_out (fitbit_lifetime needs to be handled differently)
            'legend_id' => 'legend-'.time().rand(), //gives a generated ID for multiple charts on one page
            'canvas_id' => 'canvas-'.time().rand(), //gives a generated ID for multiple charts on one page
            'graph_type' => 'line',
            'stepped' => false

        ), $atts );

        global $number_of_times;
        static $shortcode_count = 0;
        $shortcode_count++;

        $startdate = $a['start_date'];

        $enddate = $a['end_date'];

        $chart_title = $a['chart_title'];

        $dataset_label = $a['dataset_label'];

        $is_adf = $a['is_adf'];

        $type = $a['type'];

        $graph_type = $a['graph_type'];

        //Pulls data from Fitbit API
        $fitbit_data = dans_fitbit_chart_pull_data($type,$startdate,$enddate);


        $values = array_column($fitbit_data, 'value');
        $dates = array_column($fitbit_data,'dateTime');

        $encoded_values = json_encode($values);
        $encoded_dates = json_encode($dates);

        //Pass values to the chart generation script via localize
        wp_localize_script( 'generate-fitbit-chart', 'passed_data_'.$shortcode_count,
            array( 
                'values' => "$encoded_values",
                'dates' => "$encoded_dates",
                'chart_title' => "$chart_title",
                'dataset_label' => "$dataset_label",
                'is_adf' => "$is_adf",
                'graph_type' => "$graph_type",
                'legend_id' => $a['legend_id'],
                'canvas_id' => $a['canvas_id'],
                'number_of_times' => $number_of_times,
                'stepped' => $a['stepped']
            ) 
        );

        $html_return_string = "
        <div class='container' style='height:80vh;width:100%'>
            <canvas id='".$a['canvas_id']."' width='400px' height='400px'></canvas>";
        
            if($is_adf == 'yes') {
                $html_return_string .= "<div id='".$a['legend_id']."'></div><br><br>";
            }
        $html_return_string .= "</div>";

        //Enque's the script only on the final runthrough / shortcode
        if($shortcode_count == $number_of_times ) {
            wp_enqueue_script('generate-fitbit-chart');
        }

        return $html_return_string;
        
    }
}


    
if ( ! function_exists( 'dans_fitbit_chart_renew_token' ) ) {
    /**
     *  Uses Site Option Stored Renewal Token to Refresh Access Token
     *
     * @param None
     * @return true if success, false if fails
     */

    function dans_fitbit_chart_renew_token() {
        $refresh_token = get_option('fitbit_app_client_refresh_token');
        if(empty($refresh_token)) {
            return false;
        }
        $client_id = get_option('fitbit_app_client_id');
        $client_secret = get_option('fitbit_app_client_secret');
        
        $post_url = 'https://api.fitbit.com/oauth2/token';
        $body = array(
            'refresh_token' => $refresh_token,
            'grant_type'  => 'refresh_token',
        );
        $request  = new WP_Http();
        $headers = array(
            'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $client_secret )
        );
        
        $response = $request->post( $post_url, array('headers' =>$headers, 'body' => $body ) );
        $response_code = wp_remote_retrieve_response_code( $response );

        if($response_code == 200) {
            $body = json_decode(wp_remote_retrieve_body($response));
            $access_token = $body->access_token;
            $refresh_token = $body->refresh_token;
            //$fitbit_user_id = $body->user_id;
            //$fitbit_scope = $body->scope;
            if (!empty($access_token)&& !empty($refresh_token)) {
                update_option( 'fitbit_app_client_token', $access_token);            
                update_option( 'fitbit_app_client_refresh_token', $refresh_token);            
                return true;
            }
        }
        return false;
    }
}

if ( ! function_exists( 'dans_fitbit_chart_pull_data' ) ) {
    /**
     *  Pulls the data from the selected endpoint, for the selected date range
     *
     * @param $key (which endpoint to pull from), $base_date (start date in yyyy-mm-dd), $end_date (in yyyy-mm-dd)
     * @return $returned_data (array of values) if true, false if fails
     */


    function dans_fitbit_chart_pull_data($key,$base_date = '',$end_date = '') {
        if(empty($key)) { 
        return false; 
        }
    
        if(empty($base_date)) {
            $base_date =date('Y-m-d');
        }

        if(empty($end_date)) {
            $end_date =date('Y-m-d');
        }
        $access_token = get_option('fitbit_app_client_token');

        $measurements = 'en_US';


        $url_array = array(
        'steps'=>'https://api.fitbit.com/1/user/-/activities/steps/date/'.$base_date.'/'.$end_date.'.json',
        'distance'=>'https://api.fitbit.com/1/user/-/activities/distance/date/'.$base_date.'/'.$end_date.'.json',
        'calories_out'=>'https://api.fitbit.com/1/user/-/activities/calories/date/'.$base_date.'/'.$end_date.'.json',
            'fitbit_lifetime'=>'https://api.fitbit.com/1/user/-/activities.json',
            'weight' =>'https://api.fitbit.com/1/user/-/body/weight/date/'.$base_date.'/'.$end_date.'.json',
            'calories_in' => 'https://api.fitbit.com/1/user/-/foods/log/caloriesIn/date/'.$base_date.'/'.$end_date.'.json',
        );
    
        $post_url = $url_array[$key];

        $headers = array(
        'Authorization' => 'Bearer ' . $access_token,
        'Accept-Language' => $measurements,
        );
        
        $response = wp_remote_get( $post_url, array('headers'=>$headers) );
        $response_code = wp_remote_retrieve_response_code( $response );


        if ($response_code == 401) {

        if(!dans_fitbit_chart_renew_token()) {
            return false;
        } else {
        
            $access_token = get_option('fitbit_app_client_token');
            $headers = array(
                'Authorization' => 'Bearer ' . $access_token,
            );
            
            $response = wp_remote_get( $post_url, array('headers'=>$headers) );
            $response_code = wp_remote_retrieve_response_code( $response );
        }
        } 
        if($response_code == 200) {
            $body = json_decode(wp_remote_retrieve_body($response));
            
            //var_dump($body);

        if($key == 'steps') {
            $returned_data = $body->{'activities-steps'};                
        }
        elseif($key == 'distance') {
            $returned_data = $body->{'activities-distance'};                
        }
        elseif($key == 'calories_out') {
            $returned_data = $body->{'activities-calories'};                
        }
        elseif($key == 'calories_in') {
            $returned_data = $body->{'foods-log-caloriesIn'};                
        }
        elseif ($key == 'weight') {
            $returned_data = $body->{'body-weight'};
        }
        elseif ($key == 'fitbit_lifetime') {
            $lifetime_steps = $body->lifetime->total->steps;
                $lifetime_distance = $body->lifetime->total->distance;
            
                $returned_data = array('steps'=>$lifetime_steps,'distance'=>$lifetime_distance);
           
            }
        else {
            $returned_data = '';
            }
        
        if (!empty($returned_data)) {
                return $returned_data;
            }
            
        } else {
        
        return false;
        
        }
      
        return false;  
    }
}

if ( ! function_exists( 'dans_fitbit_chart_detect_shortcode' ) ) {
    /**
     *  Checks the_content in a post / page for the appearance of the shortcode
     *  If found, creates a global var that says how many times it was found 
     * 
     * @param None
     * @return true if success, false if fails
     */

    function dans_fitbit_chart_detect_shortcode(){
        global $post;
        $pattern = get_shortcode_regex();

        if (   preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
            && array_key_exists( 2, $matches )
            && in_array( 'dans_fitbit_display', $matches[2] ) )
        {

            // shortcode is being used

            wp_enqueue_script('chartjs');
            $counts = array_count_values($matches[2]);
            global $number_of_times;
            $number_of_times = $counts['dans_fitbit_display'];
        }
    }
    add_action( 'wp', 'dans_fitbit_chart_detect_shortcode' );
}
