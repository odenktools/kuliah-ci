<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * CodeIgniter Redis
 *
 * A CodeIgniter library to interact with Redis
 *
 * @package            CodeIgniter
 * @category        Libraries
 * @author            Jo�l Cox
 * @link            https://github.com/joelcox/codeigniter-redis
 * @link            http://joelcox.nl
 * @license         http://www.opensource.org/licenses/mit-license.html
 *
 * Copyright (c) 2011 Jo�l Cox and contributers
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
class Redis
{

    /**
     * CI
     *
     * CodeIgniter instance
     * @var    object
     */
    private $_ci;                // CodeIgniter instance

    /**
     * Connection
     *
     * Socket handle to the Redis server
     * @var        handle
     */
    private $_connection;        // Connection handle

    /**
     * Debug
     *
     * Whether we're in debug mode
     * @var        bool
     */
    public $debug = FALSE;

    /**
     * Constructor
     */
    function __construct()
    {

        log_message('debug', 'Redis Class Initialized');

        // Get a CI instance
        $this->_ci =& get_instance();

        // Load config
        $this->_ci->load->config('redis');

        // Connect to Redis
        $this->_connection = @fsockopen($this->_ci->config->item('redis_host'), $this->_ci->config->item('redis_port'), $errno, $errstr, 3);

        // Display an error message if connection failed
        if (!$this->_connection) {
            show_error('Could not connect to Redis at ' . $this->host . ':' . $this->port);

        }

        // Authenticate when needed
        $this->_auth();

    }

    /**
     * Call
     *
     * Catches all undefined methods
     * @param    string    command to be run
     * @param    mixed    arguments to be passed
     * @return    mixed
     */
    public function __call($method, $arguments)
    {
        $cmd = strtoupper($method);

        // Add all arguments
        for ($i = 0; $i < count($arguments); $i++) {
            $cmd .= ' ' . $this->_input_to_string($arguments[$i]);
        }

        return $this->command($cmd);
    }

    /**
     * Command
     *
     * Generic command function, just like redis-cli
     * @param    string    command to be executed
     * @return    mixed
     */
    public function command($cmd)
    {
        $request = $this->_encode_request($cmd);
        return $this->_write_request($request);
    }

    /**
     * Key commands
     */

    /**
     * Sets key $key with value $value
     * @param    string    name of the key
     * @param    string    contents for the key
     * @return    string
     */
    public function set($key, $value)
    {

        $request = $this->_encode_request('SET ' . $key . ' ' . $value);
        return $this->_write_request($request);

    }

    /**
     * Gets key $key
     * @param    string    name of the key
     * @return    string    value of the key
     */
    public function get($key)
    {
        $request = $this->_encode_request('GET ' . $key);
        return $this->_write_request($request);

    }

    /**
     * Delete key(s)
     * @param    mixed    keys to be deleted (array or string)
     * @return    int        amount of keys deleted
     */
    public function del($keys)
    {
        // Make sure we're dealing with a string that seperates keys by a space
        $keys = $this->_input_to_string($keys);

        $request = $this->_encode_request('DEL ' . $keys);
        return $this->_write_request($request);

    }

    /**
     * Finds all keys that match $pattern
     * @param    string    pattern to be matched
     * @return    array
     */
    public function keys($pattern)
    {
        $request = $this->_encode_request('KEYS ' . $pattern);
        return $this->_write_request($request);

    }

    /**
     * Add member $member into set with key $key
     * @param    string    key of the set
     * @param    string    member to add
     * @return    string
     */
    public function sadd($key, $member)
    {

        $request = $this->_encode_request('SADD ' . $key . ' ' . $member);
        return $this->_write_request($request);

    }

    /**
     * Gets members stored in set with key $key
     * @param    string    key of the set
     * @return    array    members of the set
     */
    public function smembers($key)
    {
        $request = $this->_encode_request('SMEMBERS ' . $key);
        return $this->_write_request($request);

    }

    /**
     * Remove member for set $key
     * @param    string    key of the set
     * @param    string    member to remove
     * @return    string
     */
    public function srem($key, $member)
    {
        $request = $this->_encode_request('SREM ' . $key . ' ' . $member);
        return $this->_write_request($request);

    }

    /**
     * Get total number of members in set $key
     * @param    string    key of the set
     * @return    string    number of members of the set
     */
    public function scard($key)
    {
        $request = $this->_encode_request('SCARD ' . $key);
        return $this->_write_request($request);

    }

    /**
     * Check if $member is a member for set $key
     * @param    string    key of the set
     * @param    string    member to check
     * @return    string
     */
    public function sismember($key, $member)
    {
        $request = $this->_encode_request('SISMEMBER ' . $key . ' ' . $member);
        return $this->_write_request($request);

    }


    /**
     * Connection, reading and writing
     */

    /**
     * Auth
     *
     * Runs the AUTH command when password is set
     * @return    void
     */
    private function _auth()
    {

        $password = $this->_ci->config->item('redis_password');

        // Authenticate when password is set
        if (!empty($password)) {

            // Sent auth command to the server
            $request = $this->_encode_request('AUTH ' . $password);

            // See if we authenticated successfully
            if (!$this->_write_request($request)) {
                show_error('Could not connect to Redis, invalid password');

            }

        }

    }

    /**
     * Write request
     *
     * Write the formatted request to the socket
     * @param    string    request to be written
     * @return    mixed
     */
    private function _write_request($request)
    {

        if ($this->debug === TRUE) {
            log_message('debug', 'Redis unified request: ' . $request);
        }

        fwrite($this->_connection, $request);
        return $this->_read_request();

    }

    /**
     * Read request
     *
     * Route each response to the appropriate interpreter
     * @return    mixed
     */
    private function _read_request()
    {

        $type = fgetc($this->_connection);

        if ($this->debug === TRUE) {
            log_message('debug', 'Redis response type: ' . $type);
        }

        switch ($type) {
            case '+':
                return $this->_single_line_reply();
                break;
            case '-':
                return $this->_error_reply();
                break;
            case ':':
                return $this->_integer_reply();
                break;
            case '$':
                return $this->_bulk_reply();
                break;
            case '*':
                return $this->_multi_bulk_reply();
                break;
            default:
                return FALSE;
        }

    }

    /**
     * Single line reply
     *
     * Reads the reply before the EOF
     * @return    mixed
     */
    private function _single_line_reply()
    {
        $value = trim(fgets($this->_connection));

        return $value;

    }

    /**
     * Error reply
     *
     * Write error to log and return false
     * @return    bool
     */
    private function _error_reply()
    {

        // Extract the error message
        $error = substr(fgets($this->_connection), 4);
        log_message('error', 'Redis server returned an error: ' . $error);

        return $error;

    }

    /**
     * Integer reply
     *
     * Returns an integer reply
     * @return    int
     */
    private function _integer_reply()
    {
        return (int)fgets($this->_connection);
    }

    /**
     * Bulk reply
     *
     * Reads to amount of bits to be read and returns value within the pointer and the ending delimiter
     * @return    string
     */
    private function _bulk_reply()
    {

        // Get the amount of bits to be read
        $value_length = (int)fgets($this->_connection);

        if ($value_length == -1) {
            return NULL;
        }

        $response = fgets($this->_connection, $value_length + 1);

        // Get rid of the \n\r
        fgets($this->_connection);
        return $response;

    }

    /**
     * Multi bulk reply
     *
     * Reads an n amount of bulk replies and return them as an array
     * @return    array
     */
    private function _multi_bulk_reply()
    {

        // Get the amount of values in the response
        $total_values = (int)fgets($this->_connection);

        // Loop all values and add them to the response array
        for ($i = 0; $i < $total_values; $i++) {
            // Move the pointer to correct for the \n\r
            fgets($this->_connection, 2);
            $response[] = $this->_bulk_reply();

        }

        return $response;

    }

    /**
     * Encode request
     *
     * Encode plain-text request to Redis protocol format
     * @link    http://redis.io/topics/protocol
     * @param    string        request in plain-text
     * @return    string        encoded according to Redis protocol
     */
    private function _encode_request($request)
    {
        $slices = explode(' ', $request);
        $arguments = count($slices);

        $request = "*" . $arguments . "\r\n";

        foreach ($slices as $slice) {
            $request .= "$" . strlen($slice) . "\r\n" . $slice . "\r\n";
        }

        return $request;

    }

    /**
     * Converts a input to string with spaces seperated values.
     * Takes arrays, comma separated lists and plain ol' string
     * @param    mixed
     * @return    array
     */
    private function _input_to_string($input)
    {

        $string = '';

        if (is_array($input)) {
            foreach ($input as $element) {
                $string .= $element . ' ';
            }
        } else {
            $string = str_replace(',', '', $input);
        }

        return $string;

    }

    /**
     * Debug
     *
     * Set debug mode
     * @param    bool    set the debug mode on or off
     * @return    void
     */
    public function debug($boolean)
    {
        $this->debug = (bool)$boolean;
    }

    /**
     * Destructor
     *
     * Kill the connection
     * @return    void
     */
    function __destruct()
    {
        fclose($this->_connection);
    }

}