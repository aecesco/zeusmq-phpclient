<?php

namespace zeusmqclient;

use Slim\Factory\AppFactory;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Factory\StreamFactory as StreamFactory;
use Slim\Psr7\Factory\RequestFactory as RequestFactory;
use Slim\Psr7\Factory\ResponseFactory as ResponseFactory;

class APIRestClient
{
    //Logger -> https://www.loggly.com/ultimate-guide/php-logging-basics/
    private $APIRestClient;
    private String $baseURI;
    private int $connectionTimeout;

    function __construct(String $baseURI, int $connectionTimeout)
    {
        $this->baseURI = $baseURI;
        $this->connectionTimeout = $connectionTimeout;
        $this->APIRestClient = AppFactory::create();
    }

    public function __invoke($path, $message)
    {
        $APIRestClient = AppFactory::create();

        $APIRestClient->post('/zeusmq/api/send/{exchange}',
            function(Request $request, Response $response) use ($path, $message) 
            {
                $streamFactory = new StreamFactory();
                $requestFactory = new RequestFactory();
                $responseFactory = new ResponseFactory();

                $request = $requestFactory->createRequest('POST', $path);
                $request->withBody($streamFactory->createStream($message))
                        ->withHeader('Content-Type', 'application/json');
                $response = $responseFactory->createResponse(200);

                return $response;
            }
        );

        $APIRestClient->post('/zeusmq/api/send/{exchange}/{routingKey}',
            function(Request $request, Response $response) use ($path, $message) 
            {
                $streamFactory = new StreamFactory();
                $requestFactory = new RequestFactory();
                $responseFactory = new ResponseFactory();

                $request = $requestFactory->createRequest('POST', $path);
                $request->withBody($streamFactory->createStream($message))
                        ->withHeader('Content-Type', 'application/json');
                $response = $responseFactory->createResponse(200);

                return $response;
            }
        );
        $APIRestClient->run();
    }

    public function getAPIRestClient()
    {
        return $this->APIRestClient;
    }

    public function getBaseURI()
    {
        return $this->baseURI;
    }

    public function setBaseURI(String $baseURI)
    {
        $this->baseURI = $baseURI;
    }

    public function getConnectionTimeout()
    {
        return $this->connectionTimeout;
    }

    public function setConnectionTimeout(String $connectionTimeout)
    {
        $this->connectionTimeout = $connectionTimeout;
    }

     public function send(String $messageBody, ?String $exchange, String $routingKey, ?String $operation) : bool
     {
        $error = true;
        $connectionURI = "";

        if(empty($exchange)) $connectionURI = $this->getBaseURI() . $routingKey;
        else $connectionURI = $this->getBaseURI() . $exchange . "/" . $routingKey;
        
        if(!empty($operation)) $connectionURI = $connectionURI . $operation;

        if(!empty($connectionURI) && !empty($messageBody))
        {
            $this->__invoke($connectionURI, $messageBody);
            $error = false;
        }
        return $error;
    }
}