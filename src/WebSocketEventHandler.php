<?php
namespace Lum\Swoole;

use Exception;
use Lum\Server\ProtocolHandler;

/**
 * Class WebSocketEventHandler
 *
 * @package Lum\Swoole
 */
class WebSocketEventHandler
{
    /**
     * @var ProtocolHandler $protocol
     */
    private $protocol;

    /**
     * WebSocketEventHandler constructor.
     *
     * @param $protocol
     */
    public function __construct($protocol)
    {
        $this->protocol = $protocol;
        print_r($protocol);
    }

    /**
     * @param $request
     * @param $response
     */
    public function onRequest($request, $response)
    {
        $data = $request->rawContent();
        if ($data) {
            $servicePathInfo = trim(trim($request->server['path_info'], '/'));
            try {
                $str = $this->protocol->handle($data, $servicePathInfo);
                $response->end($str);
            } catch (Exception $e) {
                //TODO:send error report
                echo "\nERROR:", $e->getMessage(), "\tdata=", $data, "\n";
            }
            echo "\n\n";
        }
    }
}