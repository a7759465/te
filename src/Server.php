<?php

namespace Te;
class Server
{
    public $_mainSocket;
    public $_local_socket;
    static public $_connections = [];
    public $_events = [];

    public function __construct($local_socket)
    {
        $this->_local_socket = $local_socket;
    }


    public function on($eventName, $eventCall)
    {
        $this->_events[$eventName] = $eventCall;
    }

    public function Listen()
    {
        $flag = STREAM_SERVER_LISTEN | STREAM_SERVER_BIND;
        $option['socket']['backlog'] = 10;
        $context = stream_context_create($option);
        $this->_mainSocket = stream_socket_server($this->_local_socket, $errno, $errstr, $flag, $context);
        if (!is_resource($this->_mainSocket)) {
            fprintf(STDOUT, "server create fail:%s\n", $errstr);
            exit(0);
        }
        fprintf(STDOUT, "Listen on :%s\n", $this->_local_socket);
    }

    public function eventLoop()
    {
        //中断信号
        while (1) {
            $readFds[] = $this->_mainSocket;
            $writeFds = [];
            $expFds = [];
            if (!empty(static::$_connections)) {
                foreach (static::$_connections as $idx => $connection) {
                    $sockfd = $connection->socketfd();
                    $readFds[] = $sockfd;
                    $writeFds[] = $sockfd;
                }
            }
//            var_dump(static::$_connections);;
//            var_dump($readFds);die;
            $ret = stream_select($readFds, $writeFds, $expFds, null, null);
            if ($ret === false) {
                break;
            }
            if ($readFds) {
                foreach ($readFds as $fd) {
                    if ($fd == $this->_mainSocket) {    //监听socket
                        $this->Accept();
                    } else {
                        $connection = static::$_connections[(int)$fd];
                        $connection->recv4socket();
                    }
                }
            }
        }
    }


    public function runEventCallBack($eventName, $args = [])
    {
        if (isset($this->_events[$eventName]) && is_callable($this->_events[$eventName])) {
            $this->_events[$eventName]($this, ...$args);
        }
    }

    public function Accept()
    {
        $connfd = stream_socket_accept($this->_mainSocket, -1, $peername);
        if (is_resource($connfd)) {
            $connection = new TcpConnection($connfd, $peername, $this);
            static::$_connections[(int)$connfd] = $connection;
            $this->runEventCallBack('connect', [$connection]);
        }
    }


}