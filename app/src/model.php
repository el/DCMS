<?php

/**
 * Model parent class
 */
class Model {

	/**
	 * Creates models for connected branches from db
	 * @return
	 */
	public function makeConnections() {
		global $controller;
		foreach ($this->bounds as $key => $value) {
			$set = "set_$key";
			$this->$set($controller->getFromID($value,$this->$key));
		}
	}
}