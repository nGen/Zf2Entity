<?php
namespace nGen\Zf2Entity\Service;

interface ChildEntityServiceInterface {
	public function fetchAllByParent($parent_id);
}