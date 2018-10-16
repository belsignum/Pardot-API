<?php

namespace CyberDuck\Pardot\Query;

use Exception;
use CyberDuck\Pardot\Contract\PardotApi;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use stdClass;

/**
 * Base query class for all Pardot API queries
 * 
 * @category   PardotApi
 * @package    PardotApi
 * @author     Andrew Mc Cormack <andy@cyber-duck.co.uk>
 * @copyright  Copyright (c) 2018, Andrew Mc Cormack
 * @license    https://github.com/Cyber-Duck/Pardot-API/license
 * @version    1.0.0
 * @link       https://github.com/Cyber-Duck/Pardot-API
 * @since      1.0.0
 */
class Query 
{
    /**
     * Pardot API andpoint
     *
     * @var string
     */
    protected $endpoint = 'https://pi.pardot.com/api/%s/version/%s/do/%s';
    
    /**
     * API instance
     *
     * @var PardotApi
     */
    protected $api;
    
    /**
     * API <object> identifier
     * /api/<object>/version/3/do/<operator>/<identifier_field>/<identifier>
     *
     * @var string
     */
    protected $object;

    /**
     * API <operator> identifier
     * /api/<object>/version/3/do/<operator>/<identifier_field>/<identifier>
     *
     * @var string
     */
    protected $operator;

    /**
     * Sets the API instance
     *
     * @param PardotApi $api
     */
    public function __construct(PardotApi $api)
    {
        $this->api = $api;
    }

    /**
     * Static binding call to initiate method chaining
     *
     * @param PardotApi $api
     * @return void
     */
    public static function obj(PardotApi $api)
    {
        return new static($api);
    }

    /**
     * Sets the API object identifier
     *
     * @param string $object
     * @return Query
     */
    protected function setObject(string $object): Query
    {
        $this->object = $object;
        return $this;
    }

    /**
     * Sets the API operator identifier
     *
     * @param string $operator
     * @return Query
     */
    protected function setOperator(string $operator): Query
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * Performs the API query
     * 
     * The passed property value is the property on the response object to return
     * and is dependent on the type of data being returned
     * 
     * Reading an individual account may require reading the <account> property
     * while reading a results list may require reading <result> property
     *
     * @param string $property
     * @return string|null
     * @throws Exception
     */
    protected function query(string $property):? string
    {
        if(!$this->api->getAuthenticator()->isAuthenticated()) {
            $this->api->getAuthenticator()->doAuthentication();
        }
        if($this->api->getAuthenticator()->isAuthenticatedSuccessfully()) {
            try {
                $client = new Client();
                $response = $client->request('POST', 
                    $this->getQueryEndpoint(),
                    $this->getQueryRequestOptions()
                );
                $data = json_decode((string) $response->getBody());

                if($response->getStatusCode() !== 200) {
                    throw new Exception('Pardot query error: 200 response not returned');
                }
                if(!is_object($data)) {
                    throw new Exception('Pardot query error: returned incorrect response');
                }
                if(property_exists($data, 'err')) {
                    throw new Exception(sprintf('Pardot query error: %s', $data->err));
                }
                if(!property_exists($data, $property)) {
                    throw new Exception(sprintf('Pardot query error: cannot find property %s in response', $data->err));
                }
                return $data->{$property};
            } catch(Exception $e) {
                if($this->api->getDebug() === true) {
                    echo $e->getMessage();
                    die;
                }
            }
        }
        return null;
    }

    /**
     * Returns the query request endpoint URL
     *
     * @return string
     */
    protected function getQueryEndpoint(): string
    {
        return sprintf(
            $this->endpoint,
            $this->object,
            $this->api->getVersion(),
            $this->operator
        );
    }

    /**
     * Returns the query request additional options
     *
     * @return array
     */
    protected function getQueryRequestOptions(): array
    {
        return [
            'form_params' => [
                'user_key' => $this->api->getAuthenticator()->getUserkey(),
                'api_key'  => $this->api->getAuthenticator()->getApiKey(),
                'format'   => 'json'
            ]
        ];
    }
}