<?php

if (!function_exists('http_response_code')) {

  $GLOBALS['access_watch_http_response_code'] = 200;

  function http_response_code( $response_code = null ) {
    if (isset($response_code)) {
      status_header( $response_code );
    }
    return $GLOBALS['access_watch_http_response_code'];
  }

  function access_watch_status_header( $status_header, $code, $description, $protocol ) {
    $GLOBALS['access_watch_http_response_code'] = $code;
    return $status_header;
  }

  add_filter( 'status_header', 'access_watch_status_header', $priority = 10, $accepted_args = 4 );

}
