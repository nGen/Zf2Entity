<?php
namespace nGen\Zf2Entity\Model;

class SharedEntityStatistics {
	public $ordering;
	public $views;
	public $status;
	public $deleted;
	public $locked;

	public function setOrdering($v) { $this -> ordering = $v; }
	public function setStatus($v) { $this -> status = $v; }
	public function setDeleted($v) { $this -> deleted = $v; }
	public function setLocked($v) { $this -> locked = $v; }

	public function getOrdering() { return $this -> ordering; }
	public function getViews() { return $this -> views; }
	public function getStatus() { return $this -> status; }
	public function getDeleted() { return $this -> deleted; }
	public function getLocked() { return $this -> locked; }
}