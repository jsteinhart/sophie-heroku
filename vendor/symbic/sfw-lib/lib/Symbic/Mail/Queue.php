<?php

/**
 * Class for sending an email rendered by a template.
 *
 * @category   Symbic
 * @package    Symbic_Mail
 * @copyright  Copyright (c) 2009-2013 Symbic GmbH (http://www.symbic.de)
 */

class Symbic_Mail_Queue
{
	public $sentMails = null;
	public $failedMails = null;

	protected $dbModel = null;
	protected $context = null;

	public function __construct(Zend_Db_Table_Abstract $dbModel = null)
	{
		if (is_null($dbModel))
		{
			$this->dbModel = Symbic_Mail_Queue_Db :: getInstance();
		}
		else
		{
			$this->dbModel = $dbModel;
		}
	}

	public function setContext($context)
	{
		$this->context = $context;
	}

	public function getContext($style = 'raw')
	{
		if ($style === 'json')
		{
			$json = json_encode($this->context);
			return (json_last_error() === JSON_ERROR_NONE)
				? $json
				: null;
		}
		else
		{
			return $this->context;
		}
	}

	public function append(Zend_Mail $mail, $deleteWhenSent = true, $isFailedSend = false)
	{
		$data = array(
			'contextJson' => $this->getContext('json'),
			'mailData' => serialize($mail),
			'creationDate' => new Zend_Db_Expr('NOW()'),
			'deleteWhenSent' => ($deleteWhenSent) ? 1 : 0,
		);
		if ($isFailedSend)
		{
			$data['transportStartDate'] = new Zend_Db_Expr('NOW()');
			$data['failureCount'] = 1;
		}
		return $this->dbModel->insert($data);
	}

	public function send(Zend_Mail $mail)
	{
		try
		{
			$mail->send();
		}
		catch (Exception $e)
		{
			$this->append($mail, true /* delete when sent */, true /* is failed send */);
		}
	}

	public function sendQueue($limit = -1)
	{
		$this->sentMails = 0;
		$this->failedMails = 0;

		$counter = 0;

		$whereOr = array();
		$whereOr[] = '`queue`.`transportStartDate` IS NULL';
		$whereOr[] = '`queue`.`failureCount` = 1 AND `queue`.`transportStartDate` < NOW() - INTERVAL 15 MINUTE';
		$whereOr[] = '`queue`.`failureCount` = 2 AND `queue`.`transportStartDate` < NOW() - INTERVAL 30 MINUTE';
		$whereOr[] = '`queue`.`failureCount` = 3 AND `queue`.`transportStartDate` < NOW() - INTERVAL 60 MINUTE';

		$db = $this->dbModel->getAdapter();
		$select = $db->select()
			->from(array('queue' => $this->dbModel->_name), array(
				'*',
			))
			->where('(' . implode(') OR (', $whereOr) . ')')
			->where('`queue`.`transportFinishDate` IS NULL')
			->order(array('queue.id ASC'))
			->limit(1);
		while ((($limit < 0) || ($counter < $limit)) && ($item = $db->fetchRow($select)))
		{
			$counter++;
			$update = array();
			$where = array(
				'id = ?' => $item['id']
			);
			try
			{
				$this->dbModel->update(array(
					'transportStartDate' => new Zend_Db_Expr('NOW()'),
				), $where);

				$mail = unserialize($item['mailData']);
				$mail->send();

				if ($item['deleteWhenSent'])
				{
					$this->dbModel->delete($where);
				}
				else
				{
					$update['transportFinishDate'] = new Zend_Db_Expr('NOW()');
				}
				$this->sentMails++;
			}
			catch (Exception $e)
			{
				$update['failureCount'] = $item['failureCount'] + 1;
				$this->failedMails++;
			}
			if (count($update))
			{
				$this->dbModel->update($update, $where);
			}
		}
		return $counter;
	}
}