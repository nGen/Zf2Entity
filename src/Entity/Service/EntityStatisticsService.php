<?php
namespace nGen\Zf2Entity\Service;

use nGen\Zf2Entity\Model\SharedEntityStatistics;

abstract class EntityStatisticsService {
	protected $dbMapper;

	public function convertToArray($entity) {
		if($entity !== false) {
			return $this -> dbMapper -> getHydrator() -> extract($entity);
		}
		return false;	
	}

	public function convertManyToArray($entities) {
		$entities_array = array();
		foreach($entities as $entity) {
			if($entity !== false) {
				$entities_array[] = $this -> dbMapper -> getHydrator() -> extract($entity);
			}
		}
		return $entities_array;
	}

	public function hasTaggingSupport() {
		return (Boolean)$this -> dbMapper -> taggingSupport;
	}

	public function hasFlagingSupport() {
		return (Boolean)$this -> dbMapper -> flagingSupport;
	}

	public function hasRatingSupport() {
		return (Boolean)$this -> dbMapper -> ratingSupport;
	}

	public function hasVotingSupport() {
		return (Boolean)$this -> dbMapper -> votingSupport;
	}

	public function fetchById($id) {
		if((int) $id) {
			return $this -> dbMapper -> fetchById($id);
		}
		return false;
	}
	
	public function fetchByIdAsArray($id) { 
		return $this -> convertToArray($this -> fetchById($id)); 
	}
	
	public function fetchAsArray($id) { 
		return $this -> convertToArray($this -> fetchById($id)); 
	}

	//All
	public function fetchAll($where = array(), $order = array(), $joins = array()) {
		return $this -> dbMapper -> fetchAll(false, $where, $order, $joins); 
	}
	
	public function fetchAllAsArray($where = array(), $order = array(), $joins = array()) {
		return $this -> convertManyToArray($this -> fetchAll($where, $order, $joins));
	}

	public function fetchAllPaginated($where = array(), $order = array(), $joins = array()) {
		return $this -> dbMapper -> fetchAll(true, $where, $order, $joins);
	}

	public function fetchAllPaginatedAsArray($where = array(), $order = array(), $joins = array()) {
		return $this -> convertManyToArray($this -> fetchAllPaginated($where, $order, $joins));
	}

	public function fetchAllActive($where = array(), $order = array(), $joins = array()) {
		return $this -> dbMapper -> fetchAllActive(false, $where, $order, $joins);
	}

	public function fetchAllActiveAsArray($where = array(), $order = array(), $joins = array()) {
		return $this -> convertManyToArray($this -> fetchAllActive($where, $order, $joins));
	}

	public function fetchAllActivePaginated($where = array(), $order = array(), $joins = array()) {
		return $this -> dbMapper -> fetchAllActive(true, $where, $order, $joins);
	}

	public function fetchAllActivePaginatedAsArray($where = array(), $order = array(), $joins = array()) {
		return $this -> convertManyToArray($this -> fetchAllActivePaginated($where, $order, $joins));
	}

	public function fetchAllEnabled($where = array(), $order = array(), $joins = array()) {
		return $this -> dbMapper -> fetchAllEnabled(false, $where, $order, $joins);
	}

	public function fetchAllEnabledAsArray($where = array(), $order = array(), $joins = array()) {
		return $this -> convertManyToArray($this -> fetchAllEnabled($where, $order, $joins));
	}

	public function fetchAllEnabledPaginated($where = array(), $order = array(), $joins = array()) {
		return $this -> dbMapper -> fetchAllEnabled(true, $where, $order, $joins);
	}

	public function fetchAllEnabledPaginatedAsArray($where = array(), $order = array(), $joins = array()) {
		return $this -> convertManyToArray($this -> fetchAllEnabledPaginated($where, $order, $joins));
	}

	public function fetchAllDisabled($where = array(), $order = array(), $joins = array()) {
		return $this -> dbMapper -> fetchAllDisabled(false, $where, $order, $joins);
	}

	public function fetchAllDisabledAsArray($where = array(), $order = array(), $joins = array()) {
		return $this -> convertManyToArray($this -> fetchAllDisabled($where, $order, $joins));
	}

	public function fetchAllDisabledPaginated($where = array(), $order = array(), $joins = array()) {
		return $this -> dbMapper -> fetchAllDisabled(true, $where, $order, $joins);
	}
	
	public function fetchAllDisabledPaginatedAsArray($where = array(), $order = array(), $joins = array()) {
		return $this -> convertManyToArray($this -> fetchAllDisabledPaginated($where, $order, $joins));
	}

	public function fetchAllDeleted($where = array(), $order = array(), $joins = array()) {
		return $this -> dbMapper -> fetchAllDeleted(false, $where, $order, $joins);
	}

	public function fetchAllDeletedAsArray($where = array(), $order = array(), $joins = array()) {
		return $this -> convertManyToArray($this -> fetchAllDeleted($where, $order, $joins));
	}

	public function fetchAllDeletedPaginated($where = array(), $order = array(), $joins = array()) {
		return $this -> dbMapper -> fetchAllDeleted(true, $where, $order, $joins);
	}
	
	public function fetchAllDeletedPaginatedAsArray($where = array(), $order = array(), $joins = array()) {
		return $this -> convertManyToArray($this -> fetchAllDeletedPaginated($where, $order, $joins));
	}

	// Limited	
	public function fetchLimited($limit, $where = array(), $order = array(), $joins = array()) {
		$entity_name = $this -> dbMapper -> getEntityName();
		$joins[] = array(
	        array('log' => $this -> dbMapper -> getLogTableName()),
	        "log.entity_primary_key = ".$entity_name.".".$this -> dbMapper -> getPrimaryKeyField(),
	        array(
	            "created_on" => "event_time"
	        )
	    );
        $where['log.entity_name'] = $entity_name;
        $where['log.event_name'] = 'created';
		return $this -> dbMapper -> fetchAll(false, $where, $order, $joins, $limit);
	}

	public function fetchLimitedAsArray($limit, $where = array(), $order = array(), $joins = array()) {
		return $this -> convertManyToArray($this -> fetchLimited($limit, $where, $order, $joins));
	}

	public function fetchLimitedActive($limit, $where = array(), $order = array(), $joins = array()) {
		return $this -> dbMapper -> fetchAllActive(false, $where, $order, $joins, $limit);
	}

	public function fetchLimitedActiveAsArray($limit, $where = array(), $order = array(), $joins = array()) {
		return $this -> convertManyToArray($this -> fetchLimitedActive($limit, $where, $order, $joins));
	}

	public function fetchLimitedEnabled($limit, $where = array(), $order = array(), $joins = array()) {
		return $this -> dbMapper -> fetchAllEnabled(false, $where, $order, $joins, $limit);
	}

	public function fetchLimitedEnabledAsArray($limit, $where = array(), $order = array(), $joins = array()) {
		return $this -> convertManyToArray($this -> fetchAllEnabled(false, $where, $order, $joins, $limit));
	}

	public function fetchLimitedDisabled($limit, $where = array(), $order = array(), $joins = array()) {
		return $this -> dbMapper -> fetchAllDisabled(false, $where, $order, $joins, $limit);
	}

	public function fetchLimitedDisabledAsArray($limit, $where = array(), $order = array(), $joins = array()) {
		return $this -> convertManyToArray($this -> fetchLimitedDisabled($limit, $where, $order, $joins));
	}

	public function fetchLimitedDeleted($limit, $where = array(), $order = array(), $joins = array()) {
		return $this -> dbMapper -> fetchAllDeleted(false, $where, $order, $joins, $limit);
	}

	public function fetchLimitedDeletedAsArray($limit, $where = array(), $order = array(), $joins = array()) {
		return $this -> convertManyToArray($this -> fetchLimitedDeleted($limit, $where, $order, $joins));
	}

	//Tagged
	public function fetchAllByTag($tag_id, $paginated = false, $where = array(), $order = array(), $joins = array()) {
		$entity_name = $this -> dbMapper -> getEntityName();
		$joins[] = array(
	        array('tag' => $this -> dbMapper -> getEntityTagTableName()),
	        "tag.entity_primary_key = ".$entity_name.".".$this -> dbMapper -> getPrimaryKeyField(),
	        array(
	            //"created_on" => "event_time"
	        )
	    );
        $where['tag.entity_name'] = $entity_name;
        $where['tag.tag_id'] = $tag_id;
		return $this -> dbMapper -> fetchAll($paginated, $where, $order, $joins);
	}

	public function fetchAllActivePaginatedByTag($tag, $where = array(), $order = array(), $joins = array()) {
		return $this -> fetchAllByTag($tag, true, $where, $order, $joins);
	}


	public function setDefaultWhereRestriction(Array $where) {

		return $this -> dbMapper -> setDefaultWhereRestriction($where);
	}

	public function getDefaultWhereRestriction() {
		return $this -> dbMapper -> getDefaultWhereRestriction();
	}

	public function setDefaultJoinRestriction(Array $joins) {
		return $this -> dbMapper -> setDefaultJoinRestriction($joins);
	}

	public function getDefaultJoinRestriction() {
		return $this -> dbMapper -> getDefaultJoinRestriction();
	}

	protected function saveDefault($data, $entity) {
		try {
			if((int) $data[$this -> dbMapper -> getPrimaryKeyField()] > 0) { $editMode = true; }
			$entity = $this -> dbMapper -> getHydrator() -> hydrate($data, $entity);

			if(isset($editMode) && $editMode === true) {
				$result = $this -> dbMapper -> updateEntity($entity);
			} else {
				$result = $this -> dbMapper -> insertEntity($entity);
			}
			return $result;			
		} catch(\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
			return false;
		}
	}

	protected function insertOrUpdate($data, $entity) {
		try {
			$entity = $this -> dbMapper -> getHydrator() -> hydrate($data, $entity);
			$result = $this -> dbMapper -> insertOrUpdateEntity($entity);
			return $result;			
		} catch(\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
			return false;
		}
	}

	protected function updateorInsert($data, $entity) {
		try {
			$entity = $this -> dbMapper -> getHydrator() -> hydrate($data, $entity);
			$result = $this -> dbMapper -> updateOrInsertEntity($entity);
			return $result;			
		} catch(\Zend\Db\Adapter\Exception\InvalidQueryException $e) {
			return false;
		}
	}

	public function enable($id) { 
		if((int) $id) {
			return $this -> dbMapper -> enable($id); 
		}
		return false;
	}

	public function disable($id) { 
		if((int) $id) {
			return $this -> dbMapper -> disable($id); 
		}
		return false;
	}

	public function permanentDelete($id) { 
		if((int) $id) {
			return $this -> dbMapper -> delete($id); 
		}
		return false;
	}

	public function deleteAll() {
		return $this -> dbMapper -> trashAll();
	}

	public function delete($id) { 
		if((int) $id) {
			return $this -> dbMapper -> trash($id); 
		}
		return false;
	}

	public function unDelete($id) { 
		if((int) $id) {
			return $this -> dbMapper -> recycle($id); 
		}
		return false;
	}

	public function lock($id) { 
		if((int) $id) {
			return $this -> dbMapper -> lock($id); 
		}
		return false;
	}

	public function unlock($id) { 
		if((int) $id) {
			return $this -> dbMapper -> unlock($id); 
		}
		return false;
	}

	public function isLocked($id) {
		if((int) $id) {			
			$entity = $this -> fetchById($id);
			if($entity) {
				return $entity -> getLocked() == 0 ? false : true;
			}
		}
		return false;
	}

    public function isCurrentUserTheLocker($id) {
		$entity = $this -> fetchById($id);
		if($entity && $entity -> getLocked()) {
			$log = $this -> dbMapper -> fetchEntityLog(null, $entity -> id, "locked");
			if($log !== false) {
				return $this -> dbMapper -> getUserEntityId() == $log -> getUser() ? true : false;
			} else {
				throw new \RuntimeException("Locked Log could not be fetched");
			}
		}
		return false;
    }

    public function increaseOrder($id) {
		if((int) $id) {	
			return $this -> dbMapper -> increaseOrder($id);
		}
		return false;
    }

    public function decreaseOrder($id) {
		if((int) $id) {	
			return $this -> dbMapper -> decreaseOrder($id);
		}
		return false;
    }

	public function setupEntity($hasParent = false, $parent = 0) {
		$ordering = 1;
		$pf = $this -> dbMapper -> getPrimaryKeyField();
		$where = array();
		if($hasParent) $where['parent'] = $parent;
		$entities = $this -> convertManyToArray($this -> dbMapper -> fetchAll(false, $where, array(), array(), null, null, null, null, true));
		foreach($entities as $entity) {
			$status = $this -> dbMapper -> insertEntityStatistics(null, $entity[$pf], true, false, $ordering);
			if($hasParent) $this -> setupEntity($hasParent, $entity[$pf]);
			$ordering++;
		}
	}

	public function isActive(SharedEntityStatistics $entity) {
		$active = $entity -> getStatus();
		$deleted = $entity -> getDeleted();
		return (Boolean)$active && !(Boolean)$deleted;
	}

	public function fetchCreatedLog($id) {
		return $this -> dbMapper -> fetchEntityLog(null, $id, 'created');
	}

	public function fetchTagById($id) {
		return $this -> dbMapper -> fetchTagById($id);
	}

	public function fetchTagByName($name) {
		return $this -> dbMapper -> fetchTagByName($name);
	}

	public function fetchTagsByEntityId($id) {
		return $this -> dbMapper -> fetchEntityTags(null, $id);
	}

	public function fetchTagsByEntityIdAsArray($id) {
		return $this -> convertManyToArray($this -> fetchTagsByEntityId($id));
	}

	public function fetchTagsNameByEntityIdAsArray($id) {
    	$tags = $this -> fetchTagsByEntityIdAsArray($id);
    	$tagArray = array();
    	foreach($tags as $tag) {
    		$tag = $this -> fetchTagById($tag['tag_id']);
    		$tagArray[] = $tag -> getName();
    	}
    	return $tagArray;
	}


}