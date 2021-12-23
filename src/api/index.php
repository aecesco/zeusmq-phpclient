<?php

use Dotenv\Dotenv;
use zeusmqclient\ZeusMQClient;
use zeusmqclient\APIRestClient;
use zeusmqclient\RabbitMQClient;

require '../zeusmq-phpclient/src/resources/bootstrap.php';

launch(
	array(
		'*** ROUTINGKEY ***',
		'manin'
	)
);

launch(
	array(
		'### EXCHANGE+ROUTINGKEY ###',
		'manin',
		'wuololo'
	)
);

function launch(Array $args) 
{
	try 
	{
		//Start Logger
		$msg = "";
		$routingKey = "";
		$exchange = null;

		switch (count($args)) 
		{
			case 2:
				if (empty($args[0]) || empty($args[1]))
				{
					echo("Al menos uno de los dos parámetros de entrada está vacio <br/>");
					return;
				} 
				else 
				{
					$msg = $args[0];
					$routingKey = $args[1];
				}
				break;
			case 3:
				if (empty($args[0]) || empty($args[1]))
				{
					echo("Al menos uno de los tres parámetros de entrada está vacio <br/>");
					return;
				} 
				else 
				{
					$msg = $args[0];
					$routingKey = $args[1];
					$exchange = $args[2];
				}
				break;
			default:
				echo("Nº de argumentos no válido: " . count($args) . "<br/>");
				return;		
		}
		
		$dotenv = Dotenv::createImmutable('..\zeusmq-phpclient\src\resources');
		$dotenv->load();
		
		$ZeusMQClient = new ZeusMQClient(
			$_ENV['APIRESTCLIENT_BASEURI_FULL'],
			$_ENV['APIRESTCLIENT_CONNECTION_TIMEOUT'],
			$_ENV['RABBITMQ_HOST'],
			$_ENV['RABBITMQ_PORT'],
			$_ENV['RABBITMQ_USERNAME'],
			$_ENV['RABBITMQ_PASSWORD'],
			$_ENV['RABBITMQ_CONNECTION_TIMEOUT'],
			($exchange == null) ? '' : $exchange,
			'/'
		);

		$APIRestClient = new APIRestClient( 
			$_ENV['APIRESTCLIENT_BASEURI_FULL'], 
			$_ENV['APIRESTCLIENT_CONNECTION_TIMEOUT']
		);

		$RabbitMQClient = new RabbitMQClient(
			$_ENV['RABBITMQ_HOST'], 
			$_ENV['RABBITMQ_PORT'], 
			$_ENV['RABBITMQ_USERNAME'], 
			$_ENV['RABBITMQ_PASSWORD'], 
			'/',
			$_ENV['RABBITMQ_EXCHANGE'],
			$_ENV['RABBITMQ_CONNECTION_TIMEOUT']
		);

		//if(empty($exchange)) $exchange = $_ENV['RABBITMQ_EXCHANGE'];
		
		if(!$ZeusMQClient->sendMessage($msg, $exchange, $routingKey, null))
			echo("Mensaje enviado correctamente <br/>");
		
		if($APIRestClient->send($msg, $exchange, $routingKey, null))
		{
			echo("APIRestClient KO <br/>");

			if($RabbitMQClient->send($msg, $exchange, $routingKey, null))
			{	
				echo("RabbitMQClient KO <br/>");
			}
			else echo("RabbitMQClient OK <br/>");
		}
		else echo("APIRestClient OK <br/>");
	}
	catch(\Exception $e)
	{
		echo("Error in App.launch(): " . $e->getMessage() . "<br/>");
		throw new \Exception("Error in App.launch(): " . $e->getMessage());
	}
}