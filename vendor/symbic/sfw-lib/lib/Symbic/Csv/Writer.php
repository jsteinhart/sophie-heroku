<?php
class Symbic_Csv_Writer
{
    protected $fileHandler;
    protected $delim = ',';
    protected $enclosure = '"';
    protected $escape = '"';
    protected $useHeader = false;
    protected $headerFields = array();
    protected $lineOffset = 0;
    protected $autoClose = true;

    public function __construct($filename = null)
    {
        if (!is_null($filename)) {
            $this->open($filename);
        }
    }

    public function __destruct()
    {
        if ($this->autoClose && $this->fileHandler) {
            $this->close();
        }
    }

    public function open($filename)
    {
        $this->fileHandler = fopen($filename, 'w+');
        if (!$this->fileHandler) {
            throw new Exception(__CLASS__ . ': Could not open file ' . $filename);
        }
    }

    public function close()
    {
        if ($this->fileHandler) {
            fclose($this->fileHandler);
        }
    }

    public function setOptions($options = array())
    {
        if (!is_array($options))
            throw new Exception(__CLASS__ . ': can not set options from a non array input');

        if (isset($options['delim']))
            $this->delim = $options['delim'];

        if (isset($options['enclosure']))
            $this->enclosure = $options['enclosure'];

        if (isset($options['escape']))
            $this->escape = $options['escape'];

    }

    public function initHeader(array $headerFields)
    {
        if ($this->lineOffset > 0) {
            throw new Exception(__CLASS__ . ': CSV headers can not been initialized afer writing data');
        }

        $this->useHeader = true;
        $this->headerFields = $headerFields;

        $this->_write($this->headerFields);
    }

    public function write(array $fields)
    {
        if ($this->useHeader) {
            $numFields = array();
            foreach ($this->headerFields as $headerField) {
                if (!isset($fields[$headerField]) || empty($fields[$headerField])) {
                    $numFields[] = '';
                } else {
                    $numFields[] = $fields[$headerField];
                }
            }

            $this->_write($numFields);
        } else {
            $this->_write($fields);
        }
    }

    protected function _write(array $fields)
    {
        $this->fputcsv($fields);
        $this->lineOffset++;
    }

    protected function fputcsv(array $fields)
    {
        if (!$this->fileHandler) {
            throw new Exception(__CLASS__ . ': has no open fileHandler');
        }

        if (fputcsv($this->fileHandler, $fields, $this->delim, $this->enclosure) === false) {
            //print_r($fields);
            throw new Exception(__CLASS__ . ': writing to fileHandler failed');
        }
    }
}