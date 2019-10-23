<?php
namespace ant\discount\helpers;

class Discount {
	const TYPE_PERCENT = 1;
	const TYPE_AMOUNT = 0;
	
	protected $_type;
	protected $_value;
	
	public function __construct($value, $type) {
		if (!isset($value) || $value === '') throw new \Exception('Cannot have empty value for discount. ');
		
		if (!in_array($type, [self::TYPE_AMOUNT, self::TYPE_PERCENT])) throw new \Exception('Discount type is not supported. ');
		$this->_type = $type;
		$this->_value = $value;
	}
	
	public function __get($name) {
		$method = 'get'.ucfirst($name);
		return $this->{$method}();
	}
	
	public function __toString() {
		return $this->getValue();
	}
	
	public static function amount($amount) {
		return new self($amount, self::TYPE_AMOUNT);
	}
	
	public static function percent($percentage) {
		return new self($percentage, self::TYPE_PERCENT);
	}

	public function getPercent() {
		if ($this->_type == self::TYPE_PERCENT) {
			return $this->value;
		}
	}
	
	public function getType() {
		return $this->_type;
	}
	
	public function getValue() {
		return $this->_value;
	}
	
	public function newPriceFor($price) {
		return $price - $this->of($price);
	}
	
	public function of($amount) {
		if ($this->getType() == self::TYPE_AMOUNT) {
			return $this->getValue() < $amount ? $this->getValue() : $amount;
		} else if ($this->getType() == self::TYPE_PERCENT) {
			return $amount * $this->getValue() / 100;
		}
		throw new \Exception('Discount type is not supported. ');
	}
}