<?php

namespace zeusmqclient;

use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitMQClient
{
	private String $host, $username, $password, $vHost, $baseExchange;
	private int $port, $connectionTimeout;
	private AMQPChannel $channel;
    private AMQPStreamConnection $connection;
	//Logger
	
	function __construct(String $host, int $port, String $username, String $password, ?String $vHost, String $baseExchange, int $connectionTimeout)
	{
		$this->host = $host;
		$this->port = $port;
		$this->username = $username;
		$this->password = $password;
		$this->vHost = $vHost;
		$this->baseExchange = $baseExchange;
		$this->connectionTimeout = $connectionTimeout;
        $this->connection = $this->setConnection();
        $this->channel = $this->setChannel();
	}
	
	public function setConnection() : AMQPStreamConnection
    {
        try
        {
            $this->connection = new AMQPStreamConnection(
                $this->host,
                $this->port,
                $this->username,
                $this->password,
                $this->vHost,
				$this->connectionTimeout
            );
            return $this->connection; 
        }
        catch(\Exception $e)
        {
            throw new \Exception("Error while setting connection: " . $e->getMessage());
        }
    }

    public function getConnection() : AMQPStreamConnection
    {
        return $this->connection;
    }

	public function setChannel() : AMQPChannel
	{
		return $this->channel = $this->connection->channel();
	}

    public function getChannel() : AMQPChannel
    {
        return $this->channel;
    }

    public function close()
    {
        $this->channel->close();

        try
        {
            $this->connection->close();
        }
        catch(\Exception $e)
        {
            throw new \Exception("Error closing connection: " . $e->getMessage());
        }
    }

	public function send(String $message, ?String $exchange, String $routingKey, ?Array $headers) : bool
	{
		$error = true;

		$this->connection = $this->setConnection();
		$this->channel = $this->setChannel();
		
		try
        {   		
			if(!empty($headers))
				$message = new AMQPMessage($message, $headers);				
			else
				$message = new AMQPMessage($message);
			
            $this->channel->basic_publish($message, $exchange, $routingKey);

			$error = false;
        }
        catch(\Exception $e)
        {
            throw new \Exception("Error in RabbitMQClient.send(String $message, String $exchange, String $routingKey, Array $headers): " . $e->getMessage());
        }
        finally
        {
			$this->close();
        }
		return $error;
	}
}