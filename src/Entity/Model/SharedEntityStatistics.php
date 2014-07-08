<?php
namespace nGen\Zf2Entity\Model;

class SharedEntityStatistics {
	public $ordering;
	public $views;
	public $status;
	public $deleted;
	public $locked;
	public $locked_by;
	public $locked_on;
	public $created_by;
	public $created_on;
	public $last_modified_by;
	public $last_modified_on;

	public function setOrdering($v) { $this -> ordering = $v; }
	public function setStatus($v) { $this -> status = $v; }
	public function setDeleted($v) { $this -> deleted = $v; }
	public function setLocked($v) { $this -> locked = $v; }
	public function setLockedBy($v) { $this -> locked_by = $v; }
	public function setLockedOn($v) { $this -> locked_on = $v; }
	public function setCreatedBy($v) { $this -> created_by = $v; }
	public function setCreatedOn($v) { $this -> created_on = $v; }
	public function setLastModifiedBy($v) { $this -> last_modified_by = $v; }
	public function setLastModifiedOn($v) { $this -> last_modified_on = $v; }

	public function getOrdering() { return $this -> ordering; }
	public function getViews() { return $this -> views; }
	public function getStatus() { return $this -> status; }
	public function getDeleted() { return $this -> deleted; }
	public function getLocked() { return $this -> locked; }
	public function getLockedBy() { return $this -> locked_by; }
	public function getLockedOn() { return $this -> locked_on; }
	public function getCreatedBy() { return $this -> created_by; }
	public function getCreatedOn() { return $this -> created_on; }
	public function getLastModifiedBy() { return $this -> last_modified_by; }
	public function getLastModifiedOn() { return $this -> last_modified_on; }
}