<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Tiny fixed-window failure counter for brute-force protection (admin login,
 * API login/MPIN, OTP send/verify). File-backed under application/cache/throttle
 * so it needs no schema change and works on shared hosting; the cache folder is
 * already denied to web access by application/cache/.htaccess.
 *
 *   $this->load->library('throttle');
 *   if ($this->throttle->blocked('login:acct:' . $mobile, 10, 900)) { 429 }
 *   ... on a failed attempt:  $this->throttle->hit('login:acct:' . $mobile, 900);
 *   ... on success:           $this->throttle->clear('login:acct:' . $mobile);
 *
 * Window semantics: the first hit opens a window of $window seconds; the
 * counter resets once it elapses. blocked() is true once $max hits landed
 * inside the current window.
 */
class Throttle
{
    protected $dir;

    public function __construct()
    {
        $this->dir = APPPATH . 'cache/throttle/';
        if (! is_dir($this->dir)) {
            @mkdir($this->dir, 0750, true);
        }

        // Opportunistic cleanup of stale counters (~1% of requests).
        if (mt_rand(1, 100) === 1) {
            $this->gc();
        }
    }

    /** True when $max or more hits were recorded for $key inside the current window. */
    public function blocked($key, $max, $window)
    {
        list($count) = $this->read($key, $window);

        return $count >= $max;
    }

    /** Seconds until the window for $key elapses (0 if none open). */
    public function retry_after($key, $window)
    {
        list($count, $first) = $this->read($key, $window);

        return $count > 0 ? max(1, ($first + $window) - time()) : 0;
    }

    /** Records one failure for $key; returns the new count. */
    public function hit($key, $window)
    {
        $path = $this->path($key);
        $fh = @fopen($path, 'c+');
        if (! $fh) {
            return 0;
        }

        flock($fh, LOCK_EX);
        $raw = stream_get_contents($fh);
        $count = 0;
        $first = time();
        if ($raw !== false && strpos($raw, '|') !== false) {
            list($first_stored, $count_stored) = array_map('intval', explode('|', $raw, 2));
            if ($first_stored + $window > time()) {
                $first = $first_stored;
                $count = $count_stored;
            }
        }
        $count++;

        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, $first . '|' . $count);
        fflush($fh);
        flock($fh, LOCK_UN);
        fclose($fh);

        return $count;
    }

    public function clear($key)
    {
        $path = $this->path($key);
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /** @return array{0:int,1:int} [count, first_hit_timestamp] for the live window. */
    protected function read($key, $window)
    {
        $path = $this->path($key);
        if (! is_file($path)) {
            return array(0, 0);
        }

        $fh = @fopen($path, 'r');
        if (! $fh) {
            return array(0, 0);
        }
        flock($fh, LOCK_SH);
        $raw = stream_get_contents($fh);
        flock($fh, LOCK_UN);
        fclose($fh);

        if ($raw === false || strpos($raw, '|') === false) {
            return array(0, 0);
        }

        list($first, $count) = array_map('intval', explode('|', $raw, 2));

        return ($first + $window > time()) ? array($count, $first) : array(0, 0);
    }

    protected function path($key)
    {
        return $this->dir . hash('sha256', (string) $key) . '.thr';
    }

    protected function gc()
    {
        $cutoff = time() - 86400;
        foreach ((array) glob($this->dir . '*.thr') as $file) {
            if (@filemtime($file) < $cutoff) {
                @unlink($file);
            }
        }
    }
}
