<?php

namespace zeusmqclient;

class ZeusMQClient
{
	private String $zeusMQBaseURI, $rabbitMQHost, $rabbitMQUser, $rabbitMQPassword, $rabbitMQDefaultExchange;
	private int $zeusMQConnectionTimeout, $rabbitMQPort, $rabbitMQTimeout;
	private APIRestClient $APIRestClient;
	private RabbitMQClient $RabbitMQClient;
	
	function __construct(String $zeusMQBaseURI, int $zeusMQConnectionTimeout, String $rabbitMQHost, int $rabbitMQPort, String $rabbitMQUser, String $rabbitMQPassword, int $rabbitMQTimeout, ?String $rabbitMQDefaultExchange, String $rabbitMQvHost)
	{
		$this->zeusMQBaseURI = $zeusMQBaseURI;
		$this->zeusMQConnectionTimeout = $zeusMQConnectionTimeout;
		$this->rabbitMQHost = $rabbitMQHost;
		$this->rabbitMQPort = $rabbitMQPort;
		$this->rabbitMQUser = $rabbitMQUser;
		$this->rabbitMQPassword = $rabbitMQPassword;
		$this->rabbitMQTimeout = $rabbitMQTimeout;
		$this->rabbitMQDefaultExchange = $rabbitMQDefaultExchange;

		$this->APIRestClient = new APIRestClient(
			$this->zeusMQBaseURI,
			$this->zeusMQConnectionTimeout
		);

		$this->RabbitMQClient = new RabbitMQClient(
			$this->rabbitMQHost,
			$this->rabbitMQPort,
			$this->rabbitMQUser,
			$this->rabbitMQPassword,
			$rabbitMQvHost,
			$this->rabbitMQDefaultExchange,
			$this->rabbitMQTimeout
		);
	}

	public function sendMessage(String $message, ?String $exchange, String $routingKey) : bool
	{
		$error = true;

		if(!empty($routingKey) || $routingKey != null || !empty($message) || $message != null)
		{
			try
			{
				if($this->APIRestClient->send($message, $exchange, $routingKey, ''))
				{
					if($this->RabbitMQClient->send($message, $exchange, $routingKey, null)) return $error;
				}
				else
				{
					$error = false;
				}
			}
			catch(\Exception $e)
			{
				throw new \Exception("Error in ZeusMQClient.sendMessage(String $message, String $exchange, String $routingKey): " . $e->getMessage());
			}
		}
		return $error;
	}

	public function sendMessageOperation(String $message, ?String $exchange, String $routingKey, String $operation) : bool
	{
		$error = true;

		if(!empty($routingKey) || $routingKey != null || !empty($message) || $message != null || !empty($operation) || $operation != null)
		{
			try
			{
				if($this->APIRestClient->send($message, $exchange, $routingKey, $operation))
				{
					$headers = array("message-operation", $operation);
					if($this->RabbitMQClient->send($message, $exchange, $routingKey, $headers)) return $error;
				}
				else
				{
					$error = false;
				}
			}
			catch(\Exception $e)
			{
				throw new \Exception("Error in ZeusMQClient.sendMessage(String $message, String $exchange, String $routingKey, String $operation): " . $e->getMessage());
			}
		}
		return $error;
	}
}