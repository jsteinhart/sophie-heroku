<?php
class Symbic_Printing_Epson_Epos_Server
{
    private $options = array();
    private $defaultOptions = array('timeout' => 600000, 'devid' => 'local_printer');

    public function __construct($options = null)
    {
        if (!is_null($options)) {
            if (is_string($options)) {
                $this->setHostname($options);
            } elseif (is_array($options)) {
                $this->setOptions($options);
            } else {
                throw new Exception('Options passed to constructor must be either the server hostname as string or an assoicative array of options');
            }
        }
    }

    public function setOptions($options)
    {
        $this->options = $options;
    }

    public function setHostname($hostname)
    {
        $this->options['hostname'] = $hostname;
    }

    public function getHostname()
    {
        return $this->options['hostname'];
    }

    public function composeUrl()
    {
        if (!isset($this->options['hostname'])) {
            throw new Exception('No hostname set');
        }

        if (isset($this->options['timeout'])) {
            $timeout = $this->options['timeout'];
        } else {
            $timeout = $this->defaultOptions['timeout'];
        }

        if (isset($this->options['devid'])) {
            $devid = $this->options['devid'];
        } else {
            $devid = $this->defaultOptions['devid'];
        }

        return 'http://' . $this->options['hostname'] . '/cgi-bin/epos/service.cgi?devid=' . $devid . '&timeout=' . $timeout;
    }

    public function newRequest()
    {
        return new Symbic_Printing_Epson_Epos_Request();
    }

    public function sendRequest(Symbic_Printing_Epson_Epos_Request $request)
    {
        $httpClient = new Zend_Http_Client($this->composeUrl());
        $httpClient->setHeaders($request->getHttpHeaders());
        $httpClient->setRawData($request->toString());
        $printResponse = $httpClient->request('POST');

        $response = new Symbic_Printing_Epson_Epos_Response($printResponse);
        return $response;
    }
}