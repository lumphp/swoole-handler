<?php
namespace Lum\Swoole;

use Exception;
use Swoole\Coroutine\Server as SwooleServer;
use Swoole\Coroutine\Server\Connection as SwooleConnection;
use Swoole\Process as SwooleProcess;
use Swoole\Process\Pool as SwooleProcessPool;

/**
 * Class TcpEventHandler
 *
 * @package Lum\Swoole\Coroutine
 */
class TcpEventHandler
{
    private $host;
    private $port;
    /**
     * @var ServerHandler $server
     */
    private $server;
    /**
     * @var ProtocolHandler $protocol
     */
    private $protocol;

    /**
     * TcpEventHandler constructor.
     *
     * @param string $host
     * @param int $port
     * @param $protocol
     */
    public function __construct(string $host, int $port, $protocol)
    {
        $this->host = $host;
        $this->port = $port;
        $this->protocol = $protocol;
    }

    /**
     * @param SwooleProcessPool $pool
     * @param $workerId
     */
    public function onWorkerStart(SwooleProcessPool $pool, $workerId)
    {
        echo "\n[INFO]\tWorker#{$workerId} is started...";
        $this->server = new SwooleServer($this->host, $this->port, false, true);
        SwooleProcess::signal(SIGTERM, [$this, 'onShutdown']);
        $this->server->handle([$this, 'onReceive']);
        $this->server->start();
    }

    /**
     * @param $pool
     * @param $workerId
     */
    public function onWorkerStop(SwooleProcessPool $pool, $workerId)
    {
        echo "\n[INFO]\tWorker#{$workerId} is stopped!";
    }

    /**
     * @param SwooleConnection $conn
     */
    public function onReceive(SwooleConnection $conn)
    {
        $data = $conn->recv();
        if (empty($data)) {
            $conn->close();
        }
        try {
            $str = $this->protocol->handle($data);
            $conn->send($str);
        } catch (Exception $e) {
            //TODO:send error report
            echo "\nERROR:", $e->getMessage(), "\tdata=", $data, "\n";
        }
    }

    /**
     * @return mixed
     */
    public function onShutdown()
    {
        return $this->server->shutdown();
    }
}