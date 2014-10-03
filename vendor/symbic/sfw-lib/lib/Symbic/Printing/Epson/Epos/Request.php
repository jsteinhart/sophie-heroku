<?php
class Symbic_Printing_Epson_Epos_Request
{

    private $httpHeaders = array(
        'Content-Type' => 'text/xml; charset=utf-8',
        'If-Modified-Since' => 'Thu, 01 Jan 1970 00:00:00 GMT');

    private $elements = array();

    public function setHttpHeader($name, $value)
    {
        $this->httpHeaders[$name] = $value;
    }

    public function getHttpHeaders()
    {
        return $this->httpHeaders;
    }

    public function addRaw($content)
    {
        // TODO: escape headline
        $this->elements[] = $content;
    }

    public function addText($text)
    {
        // TODO: escape headline
        $this->elements[] = '<text lang="en" smooth="true">' . $text . '</text>';
    }

    public function addTextLn($text)
    {
        // TODO: escape headline
        $this->elements[] = '<text lang="en" smooth="true">' . $text . '&#10;</text>';
    }

    public function addCut()
    {
        // TODO: escape headline
        $this->elements[] = '<cut/>';
    }

    public function toString()
    {
        $printRequest = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">' . "\n";
        $printRequest .= '<s:Body>' . "\n";
        $printRequest .= '<epos-print xmlns="http://www.epson-pos.com/schemas/2011/03/epos-print">' . "\n";

        foreach ($this->elements as $element) {
            if (is_string($element)) {
                $printRequest .= $element . "\n";
            } else {
                // TODO: implement epos element objects
                throw new Exception('Epos element type not supported');
            }
        }
        $printRequest .= '</epos-print>' . "\n";
        $printRequest .= '</s:Body>' . "\n";
        $printRequest .= '</s:Envelope>' . "\n";
        return $printRequest;
    }
}