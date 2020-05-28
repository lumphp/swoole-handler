<?php
namespace Lum\Swoole\Event;

use Exception;
use Lum\Server\ProtocolHandler;
use Lum\Server\EventHandler;
use swoole_server as SwooleServer;

/**
 * Class SwooleEventHandler
 *
 * @package Lum\Swoole\Event
 */
class SwooleEventHandler implements EventHandler
{
    /**
     * @var RpcProtocolHandler $handler
     */
    private $protocol;
    private $rootPath;

    /**
     * SwooleEventHandler constructor.
     *
     * @param string $rootPath
     * @param ProtocolHandler $handler
     */
    public function __construct(string $rootPath, ProtocolHandler $handler)
    {
        $this->rootPath = $rootPath;
        $this->protocol = $handler;
    }

    /**
     * @param SwooleServer $server
     */
    public function onStart(SwooleServer $server)
    {
        echo sprintf("\nonStart=[%d,%d]", $server->master_pid, $server->worker_pid);
    }

    /**
     * @param SwooleServer $server
     * @param int $fd
     * @param int $reactorId
     * @param string $data
     */
    public function onReceive(SwooleServer $server, int $fd, int $reactorId, string $data)
    {
        $res = null;
        try {
            $writer = $this->protocol->handle($data);
            $server->send($fd, $writer);
        } catch (Exception $e) {
            echo "\nERROR:", $e->getMessage(), "\tdata=", $data, "\n";
        }
        echo sprintf(
            "\nonReceive=masterPid=%d,workerPid=%d,fd=%d,reactorId=%d,data=%s,res=%s",
            $server->master_pid,
            $server->worker_pid,
            $fd,
            $reactorId,
            $res ? '' : $data,
            strval($res)
        );
    }

    /**
     * @param SwooleServer $server
     */
    public function onWorkerStart(SwooleServer $server)
    {
        $autoloaderFile = sprintf('%s/vendor/autoload.php', $this->rootPath);
        include_once $autoloaderFile;
        $msg = sprintf("\nworker-%d(%d) start ok", $server->worker_id, $server->worker_pid);
        echo $msg, "\n";
    }
}