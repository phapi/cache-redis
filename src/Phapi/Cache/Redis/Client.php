<?php


namespace Phapi\Cache\Redis;

use Phapi\Exception\InternalServerError;

/**
 * Redis client handling the communication with the redis server.
 * It's a simple client that basically connects and redirects all
 * calls via the magic __call method to the redis server without
 * even validating if the command is a valid command before the
 * request is redirected to the redis server.
 *
 * @category Phapi
 * @package  Phapi\Cache\Redis
 * @author   Peter Ahinko <peter@ahinko.se>
 * @license  MIT (http://opensource.org/licenses/MIT)
 * @link     https://github.com/phapi/cache-redis
 */
class Client
{
    /**
     * Store the socket
     *
     * @var resource
     */
    private $socket;

    /**
     * Create a socket
     *
     * @param string $host
     * @param int $port
     * @throws InternalServerError
     */
    public function __construct($host = 'localhost', $port = 6379)
    {
        // Suppressing the error that the stream_socket_client function might throw
        // since we want any problems with cache to be silent since the application
        // should keep working even if we don't have a working cache. See the documentation
        // for more information regarding this.
        $this->socket = @stream_socket_client($host . ':' . $port);

        if (!$this->socket) {
            throw new InternalServerError();
        }
    }

    /**
     * Handle all the different functions
     *
     * @param $method
     * @param array $args
     * @return array|null|string
     * @throws \Exception
     */
    public function __call($method, array $args)
    {
        array_unshift($args, $method);

        // Count arguments and start building command
        $cmd = '*' . count($args) . "\r\n";

        // Loop through all arguments
        foreach ($args as $item) {
            // Check and add length and the argument to the command
            $cmd .= '$' . strlen($item) . "\r\n" . $item . "\r\n";
        }

        // Send command
        fwrite($this->socket, $cmd);

        // Parse the response
        return $this->parseResponse();
    }

    /**
     * Parse the response from the Redis server
     *
     * @return array|null|string
     * @throws \Exception
     */
    protected function parseResponse()
    {
        $line = fgets($this->socket);
        list($type, $result) = array($line[0], substr($line, 1, strlen($line) - 3));

        // Check response type
        if ($type == '$') {
            // This is a bulk reply
            if ($result == -1) {
                $result = null;
            } else {
                $line = fread($this->socket, $result + 2);
                $result = substr($line, 0, strlen($line) - 2);
            }
        } elseif ($type == '*') {
            // Multi bulk reply
            $count = (int) $result;
            for ($i = 0, $result = array(); $i < $count; $i++) {
                $result[] = $this->parseResponse();
            }
        } elseif ($type == '-') {
            // An error occurred
            throw new \Exception($result);
        }

        return $result;
    }
}
