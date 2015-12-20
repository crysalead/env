<?php
namespace Lead\Env;

/**
 * Environment variable container.
 */
class Env extends \Lead\Collection\Collection
{
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
        parent::__construct($normalize ? $this::normalize($env) : $env);
    }

    /**
     * Returns the value at specified offset or `false` if not exists.
     *
     * @param  string $offset The offset to retrieve.
     * @return mixed          The value at offset.
     */
    public function offsetGet($offset)
    {
        if (!array_key_exists($offset, $this->_data)) {
            return false;
        }
        return $this->_data[$offset];
    }

    /**
     * Sets an array of variables.
     *
     * @param string $collection   The key.
     * @param mixed  $value The value.
     * @param self
     */
    public function set($collection, $value = null)
    {
        if (func_num_args() === 1) {
            return parent::merge($collection, true);
        }
        $this->_data[$collection] = $value;
        return $this;
    }

    /**
     * Exports the collection as an array.
     *
     * @return array
     */
    public function data()
    {
        return $this->plain();
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
