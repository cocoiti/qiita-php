<?php

namespace Qiita;

use Guzzle\Common\Event;
use Guzzle\Service\Client;
use Guzzle\Service\Description\ServiceDescription;

use Qiita\Exception\QiitaException;

class Qiita
{
    /**
     * @var Guzzle\Service\Client $client
     */
    private $client;

    /**
     * access_token
     *
     * @var string $accessToken
     */
    private $accessToken;

    /**
     * Constructor
     *
     * @param string $accessToken
     */
    public function __construct($accessToken)
    {
        $this->accessToken = $accessToken;

        $this->client = new Client();
        $this->client->setDescription(ServiceDescription::factory(__DIR__ . '/Resources/config/service.json'));
        $this->client->getEventDispatcher()->addListener('client.create_request', [$this, 'onClientCreateRequest']);
        $this->client->getEventDispatcher()->addListener('request.error', [$this, 'onRequestError']);
    }

    /**
     * Call Api
     *
     * @param string $command
     * @param array $parameters
     * @return array
     */
    public function api($command, $parameters = [])
    {
        $command = $this->client->getCommand($command, $parameters);

        return $command->execute();
    }

    /**
     * @param Event $event
     */
    public function onClientCreateRequest(Event $event)
    {
        $request = $event['request'];
        $request->setHeader(
            'Authorization',
            'Bearer ' . $this->accessToken
        );
    }

    /**
     * @param string
     */
    public function setBaseUrl($baseUrl)
    {
        $this->client->setBaseUrl($baseUrl);
    }

    /**
     * @param Event $event
     * @throws QiitaException
     */
    public function onRequestError(Event $event)
    {
        $response = $event['response'];
        if (!$response->isSuccessful()) {
            $code = $response->getStatusCode();
            $data = $response->json();
            $type = isset($data['type']) ? $data['type'] : null;
            $message = isset($data['message']) ? $data['message'] : 'An Unknown Error Occurred.';
            $e = new QiitaException($message, $code);
            $e->setResponse($response);
            $e->setData($data);
            throw $e;
        }
    }
}
