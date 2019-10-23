<?php
namespace ant\payment\models\query;

class InvoiceQuery extends \ant\components\ActiveQuery {
	
	public function init() {
		if (YII_DEBUG) throw new \Exception('DEPRECATED');
		return parent::init();
	}
	public function __construct($class) {
		if (YII_DEBUG) throw new \Exception('DEPRECATED');
		return parent::__construct($class);
	}
	
	public function issueToUser($user) {
		$user = is_object($user) ? $user : \ant\models\models\User::findOne($user);
		
		if (!isset($user)) throw new \Exception('User is not exist. ');
		
		return $this->andWhere(['issue_to' => $user->id]);
	}
	
	public function issuedAtYear($from = null, $to = null) {
		if (!isset($from)) $from = date('Y');
		if (!isset($to)) $to = $from;
		
		return $this->andWhereYear([
			'between', 'issue_date', $from, $to
		]);
	}
	
	public function createdAtYear($from = null, $to = null) {
		if (!isset($from)) $from = date('Y');
		if (!isset($to)) $to = $from;
		
		return $this->andWhereYear([
			'between', 'created_at', $from, $to
		]);
	}
}