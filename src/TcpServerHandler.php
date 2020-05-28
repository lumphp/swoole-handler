<?php
namespace Lum\Swoole;

use Lum\Server\ServerHandler;
use Swoole\Process\Pool as SwooleProcessPool;

/**
 * Class TcpServerHandler
 *
 * @package Lum\Swoole
 */
class TcpServerHandler implements ServerHandler
{
    private $options;
    /**
     * @var TcpEventHandler $eventHandler
     */
    private $eventHandler;

    /**
     * TcpServerHandler constructor.
     *
     * @param string $host
     * @param int $port
     * @param array $options
     */
    public function __construct(string $host, int $port, array $options)
    {
        $this->options = $options;
        $service = $this->options['services'] ?? null;
        $this->eventHandler = new TcpEventHandler($host, $port, new $this->options['protocol']($service));
    }

    public function run()
    {
        $workerNum = intval($this->options['workerNum'] ?? 1);
        $pool = new SwooleProcessPool($workerNum);
        $pool->set(['enable_coroutine' => true]);
        $pool->on("workerStart", [$this->eventHandler, 'onWorkerStart']);
        $pool->on('workerStop', [$this->eventHandler, 'onWorkerStop']);
        $pool->start();
    }
}