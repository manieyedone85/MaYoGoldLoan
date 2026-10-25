<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (! function_exists('inject_csrf_fields')) {
    /**
     * Adds the CSRF hidden input right after every <form ... method="post"> in
     * already-rendered HTML. The admin views write plain <form> tags (no
     * form_open()), so this is what makes `csrf_protection = TRUE` usable
     * without touching ~80 forms; GET forms are left alone, and the token is
     * the same value CI's Security class checks against its cookie.
     */
    function inject_csrf_fields($html)
    {
        $CI =& get_instance();

        if (! $CI->config->item('csrf_protection')) {
            return $html;
        }

        $field = '<input type="hidden" name="' . html_escape($CI->security->get_csrf_token_name())
            . '" value="' . html_escape($CI->security->get_csrf_hash()) . '">';

        return preg_replace_callback('/<form\b[^>]*>/i', function ($m) use ($field) {
            return preg_match('/\bmethod\s*=\s*["\']?post\b/i', $m[0]) ? $m[0] . $field : $m[0];
        }, $html);
    }
}
