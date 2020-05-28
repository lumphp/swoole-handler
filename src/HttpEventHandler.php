<?php
namespace Lum\Swoole;

use Exception;
use Lum\Server\ProtocolHandler;

/**
 * Class HttpEventHandler
 *
 * @package Lum\Swoole
 */
class HttpEventHandler
{
    /**
     * @var ProtocolHandler $protocol
     */
    private $protocol;

    /**
     * HttpEventHandler constructor.
     *
     * @param $protocol
     */
    public function __construct($protocol)
    {
        $this->protocol = $protocol;
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
        }
    }
}