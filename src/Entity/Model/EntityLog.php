<?php
namespace nGen\Zf2Entity\Model;

class EntityLog {
	public $id;
	public $entity_name;
	public $entity_primary_key;
	public $event_name;
	public $event_value;
	public $event_time;
	public $user;
	public $user_ip;

	public function setId($v) { $this -> id = $v; }
	public function setEntityName($v) { $this -> entity_name = $v; }
	public function setEntityPrimaryKey($v) { $this -> entity_primary_key = $v; }
	public function setEventName($v) { $this -> event_name = $v; }
	public function setEventValue($v) { $this -> event_value = $v; }
	public function setEventTime($v) { $this -> event_time = $v; }
	public function setUser($v) { $this -> user = $v; }
	public function setUserIp($v) { $this -> user_ip = $v; }

	public function getId() { return $this -> id; }
	public function getEntityName() { return $this -> entity_name; }
	public function getEntityPrimaryKey() { return $this -> entity_primary_key; }
	public function getEventName() { return $this -> event_name; }
	public function getEventValue() { return $this -> event_value; }
	public function getEventTime() { return $this -> event_time; }
	public function getUser() { return $this -> user; }
	public function getUserIp() { return $this -> user_ip; }
}