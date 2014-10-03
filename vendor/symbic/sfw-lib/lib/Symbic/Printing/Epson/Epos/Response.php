<?php
class Symbic_Printing_Epson_Epos_Response
{

    private $content;

    public function __construct($content = null)
    {
        if (!is_null($content)) {
            if (is_string($content)) {
                $this->content = $content;
            }
        }
    }

    public function getContent()
    {
        return $this->content;
    }
}