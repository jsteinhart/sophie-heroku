<?php
class Symbic_Form_Element_Hash extends Symbic_Form_Element_Hidden
{
    protected $_hash;
    protected $_salt = 'salt';
    protected $_session;
    protected $_timeout = 300;

    public function __construct($spec, $options = null)
    {
        parent::__construct($spec, $options);

        $this->setAllowEmpty(false)
             ->setRequired(true)
             ->initCsrfValidator();
    }

    public function setSession($session)
    {
        $this->_session = $session;
        return $this;
    }

    public function getSession()
    {
        if (null === $this->_session) {
            require_once 'Zend/Session/Namespace.php';
            $this->_session = new Zend_Session_Namespace($this->getSessionName());
        }
        return $this->_session;
    }

    public function initCsrfValidator()
    {
        $session = $this->getSession();
        if (isset($session->hash)) {
            $rightHash = $session->hash;
        } else {
            $rightHash = null;
        }

        $this->addValidator('Identical', true, array($rightHash));
        return $this;
    }

    public function setSalt($salt)
    {
        $this->_salt = (string) $salt;
        return $this;
    }

    public function getSalt()
    {
        return $this->_salt;
    }

    public function getHash()
    {
        if (null === $this->_hash) {
            $this->_generateHash();
        }
        return $this->_hash;
    }

    public function getSessionName()
    {
        return __CLASS__ . '_' . $this->getSalt() . '_' . $this->getName();
    }

    public function setTimeout($ttl)
    {
        $this->_timeout = (int) $ttl;
        return $this;
    }

    public function getTimeout()
    {
        return $this->_timeout;
    }

    public function getLabel()
    {
        return null;
    }

    public function initCsrfToken()
    {
        $session = $this->getSession();
        $session->setExpirationHops(1, null, true);
        $session->setExpirationSeconds($this->getTimeout());
        $session->hash = $this->getHash();
    }

    public function render(Zend_View_Interface $view = null)
    {
        $this->initCsrfToken();
        return parent::render($view);
    }

    protected function _generateHash()
    {
        $this->_hash = md5(
            mt_rand(1,1000000)
            .  $this->getSalt()
            .  $this->getName()
            .  mt_rand(1,1000000)
        );
        $this->setValue($this->_hash);
    }
}