<?php
/**
 *
 */
abstract class Sophie_Api_Abstract
{
    /**
     * @var null|Sophie_Context
     */
    protected $context = null;

    /**
     * @param Sophie_Context $context
     */
    public function __construct(Sophie_Context $context)
    {
        $this->context = $context;
    }

    /**
     * @return null|Sophie_Context
     */
    protected function getContext()
    {
        return $this->context;
    }

    public function __call($name, $arguments)
    {
        trigger_error('Call to undefined method ' . get_class($this) . '::' . $name . ' - ' . print_r(debug_backtrace(), 1), E_USER_ERROR);
    }

    public static function __callStatic($name, $arguments)
    {
        trigger_error('Call to undefined static method ' . get_called_class() . '::' . $name. ' - ' . print_r(debug_backtrace(), 1), E_USER_ERROR);
    }

}