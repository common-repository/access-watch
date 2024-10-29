<?php

require_once( dirname(__FILE__) . '/../../../wp-load.php' );

$access_watch = access_watch_instance();
if ($access_watch) {
  $identity = $access_watch->getIdentity();
  if ($identity) {
    if ($identity->getAgentName() == 'accesswatch' && $identity->isNice()) {
      $access_watch->feedback();
    }
  }
}

echo 'Ok';
