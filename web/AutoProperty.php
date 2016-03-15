<?php

trait AutoProperty {

	public function __call($name, $arguments) {
		if (!$arguments and property_exists($this, $name))
			return $this->$name;

		if (!preg_match('/^(set|get)([A-Z])(.*)$/', $name, $matches))
			throw new BadMethodCallException("Method '$name' does not exist");

		$property = strtolower($matches[2]) . $matches[3];
		if (!property_exists($this, $property))
			throw new BadMethodCallException("Property '$property' does not exist");

		switch ($matches[1]) {
			case 'set':
				if (1 !== count($arguments))
					throw new BadMethodCallException("Exactly 1 argument is expected");
				$this->$property = $arguments[0];
				return $this;
			case 'get':
				if ($arguments)
					throw new BadMethodCallException('Getter does not accept arguments');
				return $this->$property;
		}
	}

}
