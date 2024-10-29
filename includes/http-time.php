<?php

global $access_watch_http_requests;

$access_watch_http_requests = array();

function access_watch_http_request_start( $preempt, $r, $url) {
  global $access_watch_http_requests;
  $access_watch_http_requests[$url] = microtime(true);
  return $preempt;
}

add_filter( 'pre_http_request' , 'access_watch_http_request_start', 10, 3);

function access_watch_http_request_end( $response, $r, $url) {
  global $access_watch_http_requests;
  $access_watch_http_requests[$url] = microtime(true) - $access_watch_http_requests[$url];
  return $response;
}

add_filter( 'http_response' , 'access_watch_http_request_end', 10, 3);

function access_watch_http_init() {
  register_shutdown_function('access_watch_http_time');
}

function access_watch_http_time() {
  global $access_watch_http_requests;
  if (!empty($access_watch_http_requests)) {
    $http_time = 0;
    foreach ($access_watch_http_requests as $http_request_url => $http_request_time) {
      $http_time += $http_request_time;
    }
    $access_watch = access_watch_instance();
    if ($access_watch) {
      $access_watch->addContext('wordpress', array(
        'http_time' => $http_time
      ));
    }
  }
}

add_action( 'init' , 'access_watch_http_init' );
