<?php

namespace Te;

class TcpConnection
{
    public $_socketfd;
    public $_clientId;  //ip:port
    public $_server;

    public function __construct($sockfd, $clientId, $server)
    {
        $this->_socketfd = $sockfd;
        $this->_clientId = $clientId;
        $this->_server = $server;
    }

    public function socketfd()
    {
        return $this->_socketfd;
    }

    public function recv4socket()
    {
        $data = fread($this->_socketfd, 1024);
        if ($data) {
            /**
             * @var Server $server
             */
            $server = $this->_server;
            $server->runEventCallBack('receive', [$data, $this]);
        }
        $this->write2socket("received client message :" . $data . PHP_EOL);
    }

    public function write2socket($data)
    {
        $len = fwrite($this->_socketfd, $data, strlen($data));
        echo "send server message :" . $data . ',len:' . strlen($data) . PHP_EOL;
    }
}