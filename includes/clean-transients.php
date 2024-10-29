<?php

/**
* Clean Transients
*
* Functions to clear down transient data
*
* Inspired by:
* https://wordpress.org/plugins/artiss-transient-cleaner/
*
* @package Access Watch
* @version 0.1.8
*/

/**
* Delete Transients
*
* Shared function that will clear down requested transients
*
* @since  0.1.8
*
* @param  string  $expired  TRUE or FALSE, whether to clear all transients or not
*/

function access_watch_transient_delete( $clear_all = false ) {

  global $_wp_using_ext_object_cache;

  if ( !$_wp_using_ext_object_cache ) {

    global $wpdb;

    // Build and execute required SQL

    if ( $clear_all ) {

      $sql = "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_bouncer-identity-%'";
      $wpdb -> query( $sql );

      $sql = "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_bouncer-identity-%'";
      $wpdb -> query( $sql );

      $sql = "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_access_watch_%'";
      $wpdb -> query( $sql );

      $sql = "DELETE FROM $wpdb->options WHERE option_name LIKE '_transient_timeout_access_watch_%'";
      $wpdb -> query( $sql );

    } else {

      $option_name_length = get_option( 'db_version' ) < 34030 ? 64 : 191;

      $sql = "
        DELETE
          a, b
        FROM
          {$wpdb->options} a, {$wpdb->options} b
        WHERE
          a.option_name LIKE '_transient_bouncer-identity-%' AND
          b.option_name = SUBSTRING(CONCAT(
            '_transient_timeout_bouncer-identity-',
            SUBSTRING(
              a.option_name,
              CHAR_LENGTH('_transient_bouncer-identity-') + 1
            )
          ), 1, {$option_name_length})
        AND b.option_value < UNIX_TIMESTAMP()
      ";

      $wpdb -> query( $sql );

      $sql = "
        DELETE
          a, b
        FROM
          {$wpdb->options} a, {$wpdb->options} b
        WHERE
          a.option_name LIKE '_transient_access_watch_%' AND
          b.option_name = SUBSTRING(CONCAT(
            '_transient_timeout_access_watch_',
            SUBSTRING(
              a.option_name,
              CHAR_LENGTH('_transient_access_watch_') + 1
            )
          ), 1, {$option_name_length})
        AND b.option_value < UNIX_TIMESTAMP()
      ";

      $wpdb -> query( $sql );
    }
  }
}
