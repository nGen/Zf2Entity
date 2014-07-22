<?php
namespace nGen\Zf2Entity\Model;

class EntityTag {
	public $entity_name;
	public $entity_primary_key;
	public $tag_id;

	public function setEntityName($v) { $this -> entity_name = $v; }
	public function setEntityPrimaryKey($v) { $this -> entity_primary_key = $v; }
	public function setTagId($v) { $this -> tag_id = $v; }

	public function getEntityName() { return $this -> entity_name; }
	public function getEntityPrimaryKey() { return $this -> entity_primary_key; }
	public function getTagId() { return $this -> tag_id; }
}