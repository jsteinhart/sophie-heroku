<?php
namespace Sfwsysadmin\Model\Mail;

class Queue
{
	public function fetchAllOrderByColumn($colOrder = 'creationDate')
	{
		$queueModel = \Symbic_Mail_Queue_Db::getInstance();
		return $queueModel->fetchAll($queueModel->select()->order($colOrder))->toArray();
	}
}