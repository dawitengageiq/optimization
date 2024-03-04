<?php

namespace App\Helpers;

use Curl\Curl;
use Log;

class JSONParser
{
    protected $error;

    protected $error_code = 200;

    protected $basicAuthUsername = null;

    protected $basicAuthPassword = null;

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
    }

    public function setBasicAuthenticationUsername($userName)
    {
        $this->basicAuthUsername = $userName;
    }

    public function setBasicAuthenticationPassword($password)
    {
        $this->basicAuthPassword = $password;
    }

    /**
     * Get the response from the server
     *
     * @return mixed
     *
     * @throws \ErrorException
     */
    public function getResponse($url)
    {
        $curl = new Curl();
        $callCounter = 0;

        if ($this->basicAuthUsername != null && $this->basicAuthPassword != null) {
            $curl->setBasicAuthentication($this->basicAuthUsername, $this->basicAuthPassword);
        }

        do {

            $curl->get($url);

            if ($curl->error) {
                $this->error = $curl->error;
                $this->error_code = $curl->error_code;

                Log::info('JSONParser getResponse Error!');
                Log::info($curl->error_message);
                $callCounter++;
            }

            //stop the process when budget breached
            if ($callCounter == 10) {
                Log::info('JSONParser getResponse call limit reached!');
                break;
            }

            sleep(10);

        } while ($curl->error);

        return $curl->response;
    }

    /**
     * Get the response from the server and convert it to array.
     *
     * @return mixed|null
     */
    public function getDataArrayJSON($url)
    {
        $response = $this->getResponse($url);

        if ($this->error_code != 200) {
            return null;
        }

        $dataArray = json_decode($response, true);

        return $dataArray;
    }

    /**
     * Get the response from the server and convert the XML response to an object.
     */
    public function getXMLResponseObject($url): ?SimpleXMLElement
    {
        $dataArray = null;
        $curlResponse = $this->getResponse($url);

        if ($this->error_code != 200) {
            return null;
        }

        // Gets rid of all namespace definitions
        $curlResponse = preg_replace('/xmlns[^=]*="[^"]*"/i', '', $curlResponse);

        // Gets rid of all namespace references
        $curlResponse = preg_replace('/[a-zA-Z]+:([a-zA-Z]+[=>])/', '$1', $curlResponse);

        $xml = simplexml_load_string($curlResponse);

        return $xml;
    }

    /**
     * Get the response from the server and convert the XML response to an object.
     */
    public function getXMLResponseNoCDATA($url): ?SimpleXMLElement
    {
        $dataArray = null;
        $xml = null;
        $curlResponse = $this->getResponse($url);

        if ($this->error_code != 200) {
            return null;
        }

        try {
            $xml = simplexml_load_string($curlResponse, 'SimpleXMLElement', LIBXML_NOCDATA);
        } catch (\Exception $e) {
            Log::info($e->getMessage());
            Log::info('Response is null!');
        }

        return $xml;
    }

    /**
     * Error message
     *
     * @return mixed
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Error code
     */
    public function getErrorCode(): int
    {
        return $this->error_code;
    }
}
