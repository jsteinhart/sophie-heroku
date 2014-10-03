<?php
/**
 * SoPHIE Parameter API Class (inactive)
 *
 * The API is not in use for now. Treatment and Session parameters have been deactivated since version 3.0.0
 *
 * @hidden 1
 */
class Sophie_Api_Parameter_1_0_0_Api extends Sophie_Api_Abstract
{
    /**
     * @var null
     */
    protected $parameterTable = null;

    // parameterTable
    /**
     * @param $parameterTable
     */
    protected function setParameterTable($parameterTable)
    {
        $this->parameterTable = $parameterTable;
    }

    /**
     * @return null|Sophie_Db_Session_Parameter
     */
    protected function getParameterTable()
    {
        if (is_null($this->parameterTable)) {
            $this->parameterTable = Sophie_Db_Session_Parameter::getInstance();
        }
        return $this->parameterTable;
    }

    // FUNCTIONS
    /**
     * @param $name
     * @return mixed
     */
    public function get($name)
    {
		return null;
        $sessionId = $this->getContext()->getSessionId();
        $value = $this->getParameterTable()->fetchValueByNameAndSessionId($name, $sessionId);
        return $value;
    }
}