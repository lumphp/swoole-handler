<?php
namespace Lum\Swoole;

use Lum\Server\ServerHandler;
use Swoole\Coroutine\Http\Server as SwooleHttpServer;
use function Co\Run as CoRun;

/**
 * Class HttpServerHandler
 *
 * @package Lum\Swoole
 */
class HttpServerHandler implements ServerHandler
{
    private $host;
    private $port;
    private $options;
    /**
     * @var HttpEventHandler $eventHandler
     */
    private $eventHandler;
    /**
     * @var SwooleHttpServer $server
     */
    private $server;

    /**
     * HttpServerHandler constructor.
     *
     * @param string $host
     * @param int $port
     * @param array $options
     */
    public function __construct(string $host, int $port, array $options)
    {
        $this->host = $host;
        $this->port = $port;
        $this->options = $options;
        $service = $this->options['services'] ?? null;
        $this->eventHandler = new HttpEventHandler(new $this->options['protocol']($service));
    }

    public function run()
    {
        $that = $this;
        CoRun(
            function () use ($that) {
                $that->bootstrap();
            }
        );
    }

    public function bootstrap()
    {
        $this->server = new SwooleHttpServer($this->host, $this->port);
        $this->server->handle('/', [$this->eventHandler, 'onRequest']);
        $this->server->start();
    }
}