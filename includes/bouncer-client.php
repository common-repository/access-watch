<?php

namespace Bouncer\Http;

class WordpressClient
{

    protected $apiKey;

    protected $timeout = 2;

    public function __construct($apiKey = null)
    {
        $this->apiKey = $apiKey;
    }

    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    public function getBaseHeaders()
    {
        global $wp_version;

        $headers = array(
            'Accept' => 'application/json',
            'Access-Watch-Wordpress-Version' => $wp_version,
            'Access-Watch-Wordpress-Plugin-Version' => ACCESS_WATCH__PLUGIN_VERSION,
        );

        if ($this->apiKey) {
            $headers['Api-Key'] = $this->apiKey;
        }

        return $headers;
    }

    public function get($url)
    {
        $headers = $this->getBaseHeaders();

        $response = wp_remote_get($url, array(
            'headers' => $headers,
            'timeout' => $this->timeout,
        ));
        if ( is_wp_error( $response ) ) {
            // error_log(json_encode($response));
            return;
        }
        if (is_array($response)) {
            $response = json_decode($response['body'], true);
            return $response;
        }
    }

    public function post($url, $data = null)
    {
        $headers = $this->getBaseHeaders();
        $headers['Content-Type'] = 'application/json';

        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body'    => json_encode($data),
            'timeout' => $this->timeout,
        ));
        if ( is_wp_error( $response ) ) {
            // error_log(json_encode($response));
            return;
        }
        if (is_array($response)) {
            $response = json_decode($response['body'], true);
            return $response;
        }
    }

}
