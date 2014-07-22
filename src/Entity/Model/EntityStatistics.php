<?php
namespace nGen\Zf2Entity\Model;

class EntityStatistics {
	public $entity_name;
	public $entity_primary_key;
	public $entity_ordering;
	public $views;
	public $status;
	public $deleted;
	public $locked;

	public function setEntityName($v) { $this -> entity_name = $v; }
	public function setEntityPrimaryKey($v) { $this -> entity_primary_key = $v; }
	public function setEntityOrdering($v) { $this -> entity_ordering = $v; }
	public function setViews($v) { $this -> views = $v; }
	public function setStatus($v) { $this -> status = $v; }
	public function setDeleted($v) { $this -> deleted = $v; }
	public function setLocked($v) { $this -> locked = $v; }

	public function getEntityName() { return $this -> entity_name; }
	public function getEntityPrimaryKey() { return $this -> entity_primary_key; }
	public function getEntityOrdering() { return $this -> entity_ordering; }
	public function getViews() { return $this -> views; }
	public function getStatus() { return $this -> status; }
	public function getDeleted() { return $this -> deleted; }
	public function getLocked() { return $this -> locked; }
}