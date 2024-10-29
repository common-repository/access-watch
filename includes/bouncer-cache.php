<?php

namespace Bouncer\Cache;

class WordpressCache extends AbstractCache
{

    protected $prefix = 'access_watch';

    public function get($key)
    {
        return get_transient($key);
    }

    public function set($key, $value, $ttl = 0)
    {
        set_transient($key, $value, $ttl);
    }

    public function delete($key)
    {
        delete_transient($key);
    }

    public function clean()
    {
        access_watch_transient_delete();
    }

    public function flush()
    {
        access_watch_transient_delete(true);
    }

}
