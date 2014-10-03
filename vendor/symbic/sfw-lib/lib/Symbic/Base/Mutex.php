<?php

class Symbic_Base_Mutex
{
	private $options = array();

	private $lockImplementor = null;

	private $collection = array();

	public function __construct($taskOptions = array())
	{
		if (!isset($taskOptions['mutexManager']) || !is_array($taskOptions['mutexManager']) || !isset($taskOptions['mutexManager']['adapter']))
		{
			return;
		}

		$this->options = $taskOptions['mutexManager'];

		if (!isset($this->options['adapterOptions']) || !is_array($this->options['adapterOptions']))
		{
			$this->options['adapterOptions'] = array();
		}
		$adapterOptions = $this->options['adapterOptions'];

		if (empty($this->options['adapterOptions']['prefix']))
		{
			$this->options['adapterOptions']['prefix'] = 'mutex_';
		}

		switch ($this->options['adapter'])
		{
			case 'memcache':
				// use NinjaMutex\Lock\MemcacheLock;
				$memcache = new Memcache();
				$memcache->connect($adapterOptions['ip'], $adapterOptions['port']);
				$this->lockImplementor = new NinjaMutex\Lock\MemcacheLock($memcache);
				break;
			case 'file':
				// use NinjaMutex\Lock\FlockLock;
				$this->lockImplementor = new NinjaMutex\Lock\FlockLock($adapterOptions['basePath']);
				break;
			default:
				return;
		}
	}

	public function acquireLock( $mutexName )
	{
		if (empty($this->lockImplementor))
		{
			// always return false if the lock cannot be acquired b/c the mutex is not configured
			return false;
		}
		$mutexName = $this->escapeMutexName($mutexName);

		$this->collection[$mutexName] = new NinjaMutex\Mutex($this->options['adapterOptions']['prefix'] . $mutexName, $this->lockImplementor);
		return $this->collection[$mutexName]->acquireLock( 1000 /* timeout in milliseconds */ );
	}

	public function releaseLock( $mutexName )
	{
		$mutexName = $this->escapeMutexName($mutexName);
		if (empty($this->lockImplementor) || !isset($this->collection[$mutexName]))
		{
			return true;
		}
		return $this->collection[$mutexName]->releaseLock();
	}

	private function escapeMutexName( $mutexName )
	{
		return preg_replace('/[^-A-Z0-9_]/i', '', $mutexName);
	}
}