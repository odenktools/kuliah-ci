<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Config for the Redis library
 *
 * @see ../libraries/Redis.php
 */

// Connection details
$config['redis_host'] = '127.0.0.1';        // IP address or host
$config['redis_port'] = '6379';                // Default Redis port is 6379
$config['redis_password'] = 'moeloet';                // Can be left empty when the server does not require AUTH