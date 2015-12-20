<?php
namespace Lead\Env;

/**
 * Holds environment variables.
 */
class Env
{
    /**
     * The environement variables
     *
     * @var array
     */
    protected $_env = [];

    /**
     * The Constructor.
     *
     * It normalizes a couple of well known environment variables.
     *
     * @param array   $env       An environment variables array.
     * @param boolean $normalize Whether or not the variables must be normalized.
     */
    public function __construct($env = [], $normalize = true)
    {
        $this->_env = $normalize ? $this::normalize($env) : $env;
    }

    /**
     * Sets a variable.
     *
     * @param string $key   The key.
     * @param mixed  $value The value.
     * @param self
     */
    public function set($key, $value = null)
    {
        if (func_num_args() === 1) {
            foreach ($key as $k => $val) {
                $this->_env[$k] = $val;
            }
        } else {
            $this->_env[$key] = $value;
        }
        return $this;
    }

    /**
     * Gets a variable.
     *
     * @param  string $key The key.
     * @return mixed       The key's value or `null` if not found.
     */
    public function get($key = null)
    {
        if (!func_num_args()) {
            return $this->_env;
        }
        return $this->has($key) ? $this->_env[$key] : null;
    }

    /**
     * Checks if a key exists.
     *
     * @param  string  $key The key to check existance.
     * @return boolean
     */
    public function has($key)
    {
        return array_key_exists($key, $this->_env);
    }

    /**
     * Removes a variable.
     *
     * @param string $key The key
     * @param self
     */
    public function remove($key)
    {
        unset($this->_env[$key]);
        return $this;
    }

    /**
     * Removes all variables.
     */
    public function clear()
    {
        $this->_env = [];
    }

    /**
     * Normalizes a couple of well known PHP environment variables.
     *
     * @param  array $env Some `$_SERVER` environment variables array.
     * @return            The normalized variables.
     */
    public static function normalize($env)
    {
        $env += ['PHP_SAPI' => PHP_SAPI];

        if (isset($env['SCRIPT_URI'])) {
            $env['HTTPS'] = strpos($env['SCRIPT_URI'], 'https://') === 0;
        } elseif (isset($env['HTTPS'])) {
            $env['HTTPS'] = (!empty($env['HTTPS']) && $env['HTTPS'] !== 'off');
        }

        if (isset($env['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $env['REQUEST_METHOD'] = $env['HTTP_X_HTTP_METHOD_OVERRIDE'];
        }

        if (isset($env['REDIRECT_HTTP_AUTHORIZATION'])) {
            $env['HTTP_AUTHORIZATION'] = $env['REDIRECT_HTTP_AUTHORIZATION'];
        }

        foreach (['HTTP_X_FORWARDED_FOR', 'HTTP_PC_REMOTE_ADDR', 'HTTP_X_REAL_IP'] as $key) {
            if (isset($env[$key])) {
                $addrs = explode(', ', $env[$key]);
                $env['REMOTE_ADDR'] = reset($addrs);
                break;
            }
        }

        if (empty($env['SERVER_ADDR']) && !empty($env['LOCAL_ADDR'])) {
            $env['SERVER_ADDR'] = $env['LOCAL_ADDR'];
        }

        if ($env['PHP_SAPI'] === 'isapi' && isset($env['PATH_TRANSLATED']) && isset($env['SCRIPT_NAME'])) {
            $env['SCRIPT_FILENAME'] = str_replace('\\\\', '\\', $env['PATH_TRANSLATED']);
            $env['DOCUMENT_ROOT'] = substr($env['SCRIPT_FILENAME'], 0, -strlen($env['SCRIPT_NAME']));
        }

        return $env;
    }
}
