<?php
namespace nGen\Zf2Entity\Mapper;

use nGen\Zf2Entity\Model\SharedEntityStatistics;
use nGen\Zf2Entity\Model\EntityStatistics;
use nGen\Zf2Entity\Model\EntityLog;
use nGen\Zf2Entity\Model\Tag;
use nGen\Zf2Entity\Model\EntityTag;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use nGen\Zfc\Mapper\ExtendedAbstractDbMapper;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

class EntityStatisticsDbMapper extends ExtendedAbstractDbMapper {

    protected $mainDbMapper;
    
    protected $statTableName = "application_entity_statistics";
    protected $logTableName = "application_entity_logs";
    protected $tagTableName = "application_tags";
    protected $entityTagTableName = "application_entity_tags";
    protected $flagTableName = "application_flags";
    protected $entityFlagTableName = "application_entity_flags";


    protected $primary_key_field = "id";
    protected $user_entity_id = 0;
    protected $defaultWhereRestriction = array();
    protected $defaultJoinRestriction = array();
    protected $ordering = "DESC";

    protected $statisticsHydrator = null;
    protected $logsHydrator = null;
    protected $tagsHydrator = null;
    protected $entityTagsHydrator = null;
    protected $flagsHydrator = null;
    protected $entityFlagsHydrator = null;

    //Extensions
    public $taggingSupport = false;
    public $flagingSupport = false;
    public $ratingSupport = false;
    public $votingSupport = false;

    public function setStatisticsHydrator(HydratorInterface $hydrator) {
        $this -> statisticsHydrator = $hydrator;
    }

    public function getStatisticsHydrator() {
        return $this -> statisticsHydrator === null ? new \Zend\Stdlib\Hydrator\ClassMethods() : $this -> statisticsHydrator;
    }

    public function setLogsHydrator(HydratorInterface $hydrator) {
        $this -> logsHydrator = $hydrator;
    }

    public function getLogsHydrator() {
        return $this -> logsHydrator === null ? new \Zend\Stdlib\Hydrator\ClassMethods() : $this -> logsHydrator;
    }

    public function setTagsHydrator(HydratorInterface $hydrator) {
        $this -> tagsHydrator = $hydrator;
    }

    public function getTagsHydrator() {
        return $this -> tagsHydrator === null ? new \Zend\Stdlib\Hydrator\ClassMethods() : $this -> tagsHydrator;
    }

    public function setEntityTagsHydrator(HydratorInterface $hydrator) {
        $this -> entityTagsHydrator = $hydrator;
    }

    public function getEntityTagsHydrator() {
        return $this -> entityTagsHydrator === null ? new \Zend\Stdlib\Hydrator\ClassMethods() : $this -> entityTagsHydrator;
    }

    public function setEntityFlagsHydrator(HydratorInterface $hydrator) {
        $this -> entityFlagsHydrator = $hydrator;
    }

    public function getEntityFlagsHydrator() {
        return $this -> entityFlagsHydrator === null ? new \Zend\Stdlib\Hydrator\ClassMethods() : $this -> entityFlagsHydrator;
    }

    public function setUserEntityId($id) {
        if((int)$id > 0) {
            $this -> user_entity_id = $id;    
        }
    }

    public function getUserEntityId() {
        return $this -> user_entity_id;
    }
    
    public function setPrimaryKeyField($field_name) {
        $this -> primary_key_field = $field_name;
    }    

    public function getPrimaryKeyField() {
        return $this -> primary_key_field;
    }

    public function setDefaultWhereRestriction(Array $where) {
        $this -> defaultWhereRestriction = $where;
    }

    public function getDefaultWhereRestriction() {
        return $this -> defaultWhereRestriction;
    }

    public function setDefaultJoinRestriction(Array $joins) {
        $this -> defaultJoinRestriction = $joins;
    }

    public function getDefaultJoinRestriction() {
        return $this -> defaultJoinRestriction;
    }

    /**
     * @param object|array $entity
     * @param string|TableIdentifier|null $entity_name
     * @param HydratorInterface|null $hydrator
     * @return ResultInterface
     */
    protected function insert($entity, $entity_name = null, HydratorInterface $hydrator = null)
    {
        $this->initialize();
        $entity_name = $entity_name ?: $this->tableName;

        $sql = $this->getSql()->setTable($entity_name);
        $insert = $sql->insert();

        $rowData = $this->entityToArray($entity, $hydrator);
        unset(
            $rowData['ordering'],
            $rowData['views'],
            $rowData['status'],
            $rowData['deleted'],
            $rowData['locked']
        );
        $insert->values($rowData);

        $statement = $sql->prepareStatementForSqlObject($insert);

        return $statement->execute();
    }

    /**
     * @param object|array $entity
     * @param string|array|closure $where
     * @param string|TableIdentifier|null $entity_name
     * @param HydratorInterface|null $hydrator
     * @return ResultInterface
     */
    protected function updateAll($entity, $where, $entity_name = null, HydratorInterface $hydrator = null)
    {
        $this->initialize();
        $entity_name = $entity_name ?: $this->tableName;

        $sql = $this->getSql()->setTable($entity_name);
        $update = $sql->update();

        $rowData = $this->entityToArray($entity, $hydrator);
        unset(
            $rowData['ordering'],
            $rowData['views'],
            $rowData['status'],
            $rowData['deleted'],
            $rowData['locked']
        );
        $update->set($rowData)
            ->where($where);

        $statement = $sql->prepareStatementForSqlObject($update);

        return $statement->execute();
    }

    public function insertEntity($entity, $entity_name = null, HydratorInterface $hydrator = null, $noTransaction = false) {
        //var_dump($entity instanceof SharedEntityStatistics); exit;
        $entity_name = $entity_name ?: $this->tableName;
        if(!$noTransaction) $this -> beginTransaction();
        try {
            $result = $this -> insert($entity, $entity_name, $hydrator);

            $primary_key_value = $entity -> {$this -> getPrimaryKeyField()};
            if($primary_key_value < 1) {
                $primary_key_value = $result -> getGeneratedValue();
            }

            if($primary_key_value > 0) {
                $entity -> {$this -> primary_key_field} = $primary_key_value;
                if($entity instanceof SharedEntityStatistics) {
                    $stat_result = $this -> insertEntityStatistics($entity_name, $primary_key_value, true, false, $this -> getHighestOrdering()+1);
                }
                $log_result = $this -> insertEntityLog($entity_name, $primary_key_value, "created");
                $isSuccess = (($entity instanceof SharedEntityStatistics && $stat_result) || !$entity instanceof SharedEntityStatistics) && $log_result;
                if($isSuccess && !$noTransaction) $this -> commit();
                return $isSuccess;
            } else {
                if(!$noTransaction) $this -> rollback();
                return false;
            }
        } catch(\Exception $e) {
            if(!$noTransaction) $this -> rollback();
            return false;
        }
    }
    
    public function updateEntity($entity, Array $where = array(), $entity_name = null, HydratorInterface $hydrator = null, $noTransaction = false) {        
        $entity_name = $entity_name ?: $this->tableName;
        $primary_key_field = $this -> primary_key_field;
        if(!$noTransaction) $this -> beginTransaction();
        try {
            //Check if Entity Exist or not
            $tEntity = $this -> fetchOne(array(
                $this -> getPrimaryKeyField() => $entity -> {$this -> getPrimaryKeyField()}
            ), array(), $entity_name, null, $entity, $hydrator, $entity instanceof SharedEntityStatistics ? false : true);
            if($tEntity === false) return false;

            //Proceed with update
            $id = $entity -> $primary_key_field;
            $where[$primary_key_field] = $id;            
            $result = $this -> updateAll($entity, $where, $entity_name, $hydrator);
            $stat_status = $this -> insertEntityLog($entity_name, $id, 'modified', 'entity');
            if($stat_status && !$noTransaction) $this -> commit();
            return $stat_status;
        } catch(\Exception $e) {
            if(!$noTransaction) $this -> rollback();
            return false;
        }
    }

    public function insertOrUpdateEntity($entity, Array $where = array(), $entity_name = null, HydratorInterface $hydrator = null, $noTransaction = false) {
        try {
            if($this -> updateEntity($entity, $where, $entity_name, $hydrator, $noTransaction) === false) {
                return $this -> insertEntity($entity, $entity_name, $hydrator, $noTransaction);
            }
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    public function updateOrInsertEntity($entity, Array $where = array(), $entity_name = null, HydratorInterface $hydrator = null, $noTransaction = false) {
        try {
            if($this -> insertEntity($entity, $entity_name, $hydrator, $noTransaction) === false) {
                return $this -> updateEntity($entity, $where, $entity_name, $hydrator, $noTransaction);
            }
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    private function getCurrentIp() {
        // Known prefix
        $v4mapped_prefix_hex = '00000000000000000000ffff';
        $v4mapped_prefix_bin = pack("H*", $v4mapped_prefix_hex);

        // Or more readable when using PHP >= 5.4
        # $v4mapped_prefix_bin = hex2bin($v4mapped_prefix_hex); 

        // Parse
        $addr = $_SERVER['REMOTE_ADDR'];
        $addr_bin = inet_pton($addr);
        if( $addr_bin === FALSE ) {
          // Unparsable? How did they connect?!?
          die('Invalid IP address');
        }

        // Check prefix
        if( substr($addr_bin, 0, strlen($v4mapped_prefix_bin)) == $v4mapped_prefix_bin) {
          // Strip prefix
          $addr_bin = substr($addr_bin, strlen($v4mapped_prefix_bin));
        }

        // Convert back to printable address in canonical form
        $addr = inet_ntop($addr_bin);        
        return $addr;
    }

    public function insertEntityStatistics($entity_name = null, $primary_key_value, $status = true, $deleted = false, $ordering = 0) {
        $entity_name = $entity_name ?: $this->tableName;
        $data = array(
            "entity_name" => $entity_name,
            "entity_primary_key" => $primary_key_value,
            "entity_ordering" => $ordering,
            "views" => 0,
            "status" => (int)$status,
            "deleted" => (int)$deleted,
            "locked" => 0,
        );

        try {
            $statEntity = $this -> getStatisticsHydrator() -> hydrate($data, new EntityStatistics());
            $result = parent::insert($statEntity, $this -> statTableName, $this -> getStatisticsHydrator());
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    public function updateEntityStatistics($entity_name = null, $primary_key_value) {
        $entity_name = $entity_name ?: $this->tableName;

        try {
            $this -> insertEntityLog($entity_name, $primary_key_value, "modified");
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    //Log Related
    public function fetchEntityLog($entity_name = null, $primary_key_value, $event_name) {
        $entity_name = $entity_name ?: $this->tableName;
        $where = array(
            "entity_name" => $entity_name,
            "entity_primary_key" => $primary_key_value,
            "event_name" => $event_name
        );
        return $this -> fetchOne($where, array(), $this -> logTableName, null, new EntityLog(), $this -> getLogsHydrator(), true);
    }

    public function insertEntityLog($entity_name = null, $primary_key_value, $event_name, $event_value = "", $event_time = null) {
        $entity_name = $entity_name ?: $this->tableName;
        $logData = array(
            "entity_name" => $entity_name,
            "entity_primary_key" => $primary_key_value,
            "event_name" => $event_name,
            "event_value" => $event_value,
            "event_time" => $event_time ?: date("Y-m-d H:i:s"),
            "user" => $this -> user_entity_id,
            "user_ip" => $this -> getCurrentIp()
        );
        try {
            $log = $this -> getLogsHydrator() -> hydrate($logData, new EntityLog());
            $result = parent::insert($log, $this -> logTableName, $this -> getLogsHydrator());
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    public function updateEntityLog($entity_name = null, $primary_key_value, $event_name, $event_value = "", $event_time = null) {
        $entity_name = $entity_name ?: $this->tableName;
        $data = array(
            "event_time" => $event_time ?: date("Y-m-d H:i:s"),
            "user" => $this -> user_entity_id,
            "user_ip" => $this -> getCurrentIp()
        );
        //IF $event_value is provided, then only the update that field
        if($event_value != "") $data['event_value'] = $event_value;

        //Where Condition
        $where = array(
            "entity_name" => $entity_name,
            "entity_primary_key" => $primary_key_value,
            "event_name" => $event_name,
        );
        try {
            $result = parent::updateField($data, $where, $this -> logTableName);
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    public function deleteEntityLog($entity_name = null, $primary_key_value, $event_name) {
        $entity_name = $entity_name ?: $this->tableName;
        $where = array(
            "entity_name" => $entity_name,
            "entity_primary_key" => $primary_key_value,
            "event_name" => $event_name
        );
        try {
            $result = parent::delete($where, $this -> logTableName);
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }    

    public function getFetchSelect(Array $where = array(), Array $order = array(), Array $joins = array(), $limit = null, $entity_name = null, $primary_key_field = null, $entity_prototype = null) {
        $primary_key_field = $primary_key_field ?: $this -> primary_key_field;
        $entity_name = $entity_name ?: $this->tableName;

        $select = $this -> getSelect($entity_name);
        $entity_prototype = $entity_prototype ?: $this -> getEntityPrototype();
        if(is_object($entity_prototype) && $entity_prototype instanceof SharedEntityStatistics) {
            $select -> join(
                array('stat' => $this -> statTableName),
                "stat.entity_primary_key = ".$entity_name.".".$primary_key_field,
                array(
                    "ordering" => "entity_ordering",
                "views",
                    "status",
                    "deleted",      
                    "locked",
                )
            );
            $where['stat.entity_name'] = $entity_name;
        }

        $joins = array_replace_recursive($this -> defaultJoinRestriction, $joins);
        if(count($joins)) {
            foreach($joins as $join) {
                $select -> join($join[0], $join[1], $join[2]);
            }
        }
        
        $where = array_replace_recursive($this -> defaultWhereRestriction, $where);

        $select -> where($where);
        $select -> order($order);
        if($limit !== null) $select -> limit($limit);
        return $select;
    }

    public function attachActive($select, $entity_name = null, $primary_key_field = null) {
        $primary_key_field = $primary_key_field ?: $this -> primary_key_field;
        $entity_name = $entity_name ?: $this->tableName;

        $select -> join(
            array('stat' => $this -> statTableName),
            "stat.entity_primary_key = ".$entity_name.".".$primary_key_field,
            array(
                "ordering" => "entity_ordering",
                "views",
                "status",
                "deleted",      
                "locked",
            )
        );

        $where['stat.entity_name'] = $entity_name;
        $where['status'] = true;
        $where['deleted'] = 0;
        $select -> where($where);
        return $select;       
    }

    public function fetchAll($paginated = false, Array $where = array(), $order = array(), Array $joins = array(), $limit = null, $entity_name = null, $primary_key_field = null, $entity_prototype = null, HydratorInterface $hydrator = null) {
        $select = $this -> getFetchSelect($where, $order, $joins, $limit, $entity_name, $primary_key_field, $entity_prototype);
        echo $this -> getSQLString($select);
        if($paginated) {
            $hydrator = $hydrator ?: $this -> getHydrator();
            $entity_prototype = $entity_prototype ?: $this -> getEntityPrototype();
            $resultSet = new HydratingResultSet($hydrator, $entity_prototype);
            $dbSelect = new DbSelect($select, $this->getDbAdapter(), $resultSet);
            return new Paginator($dbSelect);
        }
        $entity = $this -> select($select, $entity_prototype, $hydrator);
        return $entity;
    }

    public function fetchOne(Array $where = array(), Array $joins = array(), $entity_name = null, $primary_key_field = null, $entity_prototype = null, HydratorInterface $hydrator = null) {
        $select = $this -> getFetchSelect($where, array(), $joins, null, $entity_name, $primary_key_field, $entity_prototype);
        $entity = $this -> select($select, $entity_prototype, $hydrator) -> current();
        return $entity; 
    }
           
    public function fetchById($id, Array $where = array(), Array $joins = array(), $entity_name = null, $primary_key_field = null, $entity_prototype = null, HydratorInterface $hydrator = null) { 
        $primary_key_field = $primary_key_field ?: $this -> primary_key_field;
        $entity_name = $entity_name ?: $this->tableName; 
        $where[$entity_name.".".$primary_key_field] = $id;
        return $this -> fetchOne($where, $joins, $entity_name, $primary_key_field, $entity_prototype, $hydrator);
    }

    public function fetchAllActive($paginated = false, Array $where = array(), $order = array(), Array $joins = array(), $limit = null, $entity_name = null, $primary_key_field = null, $entity_prototype = null, HydratorInterface $hydrator = null) {
        $where['status'] = true;
        $where['deleted'] = 0;
        $order[] = 'ordering '.$this -> ordering;
        return $this -> fetchAll($paginated, $where, $order, $joins, $limit, $entity_name, $primary_key_field, $entity_prototype, $hydrator);
    }

    public function fetchAllEnabled($paginated = false, Array $where = array(), $order = array(), Array $joins = array(), $limit = null, $entity_name = null, $primary_key_field = null, $entity_prototype = null, HydratorInterface $hydrator = null) {
        $where['status'] = true;
        $where['deleted'] = 0;
        return $this -> fetchAll($paginated, $where, $order, $joins, $limit, $entity_name, $primary_key_field, $entity_prototype, $hydrator);        
    }

    public function fetchAllDisabled($paginated = false, Array $where = array(), $order = array(), Array $joins = array(), $limit = null, $entity_name = null, $primary_key_field = null, $entity_prototype = null, HydratorInterface $hydrator = null) {
        $where['status'] = 0;
        $where['deleted'] = 0;
        return $this -> fetchAll($paginated, $where, $order, $joins, $limit, $entity_name, $primary_key_field, $entity_prototype, $hydrator);  
    }

    public function fetchAllDeleted($paginated = false, Array $where = array(), $order = array(), Array $joins = array(), $limit = null, $entity_name = null, $primary_key_field = null, $entity_prototype = null, HydratorInterface $hydrator = null) {
        $where['deleted'] = true;
        return $this -> fetchAll($paginated, $where, $order, $joins, $limit, $entity_name, $primary_key_field, $entity_prototype, $hydrator);  
    }

    public function fetchAllUnDeleted($paginated = false, Array $where = array(), $order = array(), Array $joins = array(), $limit = null, $entity_name = null, $primary_key_field = null, $entity_prototype = null, HydratorInterface $hydrator = null) {
        $where['deleted'] = 0;
        return $this -> fetchAll($paginated, $where, $order, $joins, $limit, $entity_name, $primary_key_field, $entity_prototype, $hydrator);  
    }
    
    public function delete($id, $where = array(), $entity_name = null, $primary_key_field = null) {
        $primary_key_field = $primary_key_field ?: $this -> primary_key_field;
        $entity_name = $entity_name ?: $this->tableName;

        $where[$primary_key_field] = $id;
        $statistic_where = array("entity_name" => $entity_name, "entity_primary_key" => $id,);

        try {
            $result = parent::delete($where, $entity_name);
            $statistic_result = parent::delete($statistic_where, $this -> statTableName);
            return $result && $statistic_result;
        } catch(\Exception $e) {
            return false;
        }
    }

    public function setStatisticField($field, $value, $id, Array $where = array(), $entity_name = null, $primary_key_field = null, $log = true) {
        $primary_key_field = $primary_key_field ?: $this -> primary_key_field;
        $entity_name = $entity_name ?: $this->tableName;
        $where[$primary_key_field] = $id;        
        $statistic_where = array("entity_name" => $entity_name, "entity_primary_key" => $id, );

        if(is_array($field)) {
            if(is_array($value) && count($field) == count($value)) {
                foreach($field as $k => $v) {
                    $data[$v] = $value[$k];
                }
            } else {
                throw new InvalidArguementExcepction('"$field" and "$value" should both be ether String Type or Array Type');
            }
        } else {
            $data[$field] = $value;
        }

        try {
            $r1 = parent::updateField($data, $statistic_where, $this -> statTableName);
            if($log) {
                $r2 = $this -> insertEntityLog($entity_name, $id, "modified", is_array($field) ? implode(",", $field) : $field);
                return $r1 && $r2;
            }
            return $r1;
        } catch(\Exception $e) {
            die($e -> getMessage());
            return false;
        }
    }

    public function lock($id, Array $where = array(), $entity_name = null, $primary_key_field = null) {
        $r1 = $this -> setStatisticField('locked', '1', $id, $where, $entity_name, $primary_key_field, false);
        $r2 = $this -> insertEntityLog($entity_name, $id, 'locked');
        return $r1 && $r2;
    }

    public function unlock($id, Array $where = array(), $entity_name = null, $primary_key_field = null) {
        $r1 = $this -> setStatisticField('locked', '0', $id, $where, $entity_name, $primary_key_field, false);
        $r2 = $this -> deleteEntityLog($entity_name, $id, 'locked');
        return $r1 and $r2;
    }

    public function trash($id, Array $where = array(), $entity_name = null, $primary_key_field = null) {
        return $this -> setStatisticField("deleted", true, $id, $where, $entity_name, $primary_key_field);
    }

    public function trashAll(Array $where = array(), $entity_name = null, $primary_key_field = null) {
        $this -> beginTransaction();
        try {
            $entities = $this -> fetchAllUnDeleted(false, $where, array(), $entity_name, $primary_key_field);
            $primary_key_field = $primary_key_field ?: $this -> primary_key_field;
            foreach($entities as $entity) {
                $id = $entity -> $primary_key_field;
                $this -> trash($id, $where, $entity_name, $primary_key_field);
            }
            $this -> commit();
            return true;
        } catch(\Exception $e) {
            $this -> rollback();
            return false;
        }
    }

    public function recycle($id, Array $where = array(), $entity_name = null, $primary_key_field = null) {
        return $this -> setStatisticField("deleted", false, $id, $where, $entity_name, $primary_key_field);
    }

    public function enable($id, Array $where = array(), $entity_name = null, $primary_key_field = null) {
        return $this -> setStatisticField("status", true, $id, $where, $entity_name, $primary_key_field);
    }

    public function disable($id, Array $where = array(), $entity_name = null, $primary_key_field = null) {
        return $this -> setStatisticField("status", false, $id, $where, $entity_name, $primary_key_field);
    }

    /**
    * Ordering Related
    */

    public function getHighestOrdering(Array $where = array(), $entity_name = null, $primary_key_field = null) {
        try {
            $select = $this -> getFetchSelect($where, array('ordering DESC'), array(), null, $entity_name, $primary_key_field);
            $select -> limit(1);
            $entity = $this -> select($select) -> current();
            if($entity) {
                return $entity -> getOrdering();
            } else {
                return 0;
            }
        } catch(\Exception $e) {
            return false;
        }
    }

    public function findPrevInOrder($ordering, Array $where = array(), $entity_name = null, $primary_key_field = null) {
        try {
            $predicate = new \Zend\Db\Sql\Where();
            $where[] = $predicate -> greaterThan('entity_ordering', $ordering);
            $select = $this -> getFetchSelect($where, array('ordering '.($this -> ordering == 'DESC' ? 'ASC' : 'DESC')), array(), null, $entity_name, $primary_key_field);
            $select -> limit(1);
            $entity = $this -> select($select) -> current();
            if($entity) {
                return $entity;
            }
            return false;
        } catch(\Exception $e) {
            echo $e -> getMessage();
            return false;
        }
    }

    public function findNextInOrder($ordering, Array $where = array(), $entity_name = null, $primary_key_field = null) {
        try {
            $predicate = new \Zend\Db\Sql\Where();
            $where[] = $predicate -> lessThan('entity_ordering', $ordering);
            $select = $this -> getFetchSelect($where, array('ordering '.$this -> ordering), array(), null, $entity_name, $primary_key_field);
            $select -> limit(1);
            $entity = $this -> select($select) -> current();
            if($entity) {
                return $entity;
            }   
        } catch(\Exception $e) {}
        return false;
    }

    public function decreaseOrder($id, Array $where = array(), $entity_name = null, $primary_key_field = null) {
        $this -> beginTransaction();
        try {
            $entity = $this -> fetchById($id, $where, array(), $entity_name, $primary_key_field);
            $ordering = $entity -> getOrdering();
            $next = $this -> findNextInOrder($ordering, $where, $entity_name, $primary_key_field);
            if($next !== false) {
                $r1 = $this -> setStatisticField("entity_ordering", $next -> getOrdering(), $id, $where, $entity_name, $primary_key_field, false);
                $r2 = $this -> setStatisticField("entity_ordering", $ordering, $next -> getId(), $where, $entity_name, $next -> getId(), false);
                $r3 = $this -> insertEntityLog($entity_name, $id, 'decrease-order');
                if($r1 && $r2 && $r3) {
                    $this -> commit();
                    return true;
                }                
            }
        } catch(\Exception $e) {}
        $this -> rollback();
        return false;
    }

    public function increaseOrder($id, Array $where = array(), $entity_name = null, $primary_key_field = null) {
        $this -> beginTransaction();
        try {
            $entity = $this -> fetchById($id, $where, array(), $entity_name, $primary_key_field);
            $ordering = $entity -> getOrdering();
            $prev = $this -> findPrevInOrder($ordering, $where, $entity_name, $primary_key_field);
            if($prev !== false) {
                $r1 = $this -> setStatisticField("entity_ordering", $prev -> getOrdering(), $id, $where, $entity_name, $primary_key_field, false);
                $r2 = $this -> setStatisticField("entity_ordering", $ordering, $prev -> getId(), $where, $entity_name, $prev -> getId(), false);
                $r3 = $this -> insertEntityLog($entity_name, $id, 'increased-order');
                if($r1 && $r2 && $r3) {
                    $this -> commit();
                    return true;
                }                
            }
        } catch(\Exception $e) {}
        $this -> rollback();
        return false;    
    }

    //Tagging Related
    public function insertTag($tag) {        
        $data = array("name" => $tag);
        $result = parent::insert($data, $this -> tagTableName);
        $generated_value = $result -> getGeneratedValue(); 
        return $generated_value;
    }

    public function fetchTagById($tag_id) {
        $select = $this -> getSelect($this -> tagTableName);
        $select -> where(array("id" => $tag_id));
        $result = $this -> select($select, new Tag(), $this -> getTagsHydrator()) -> current();
        return $result;
    }

    public function fetchTagByName($tag_name) {
        $select = $this -> getSelect($this -> tagTableName);
        $select -> where(array("name" => $tag_name));
        $result = $this -> select($select, new Tag(), $this -> getTagsHydrator()) -> current();
        return $result;
    }

    public function fetchEntityTags($entity_name = null, $primary_key_value) {
        $entity_name = $entity_name ?: $this->tableName;
        $select = $this -> getSelect($this -> entityTagTableName);
        $where = array(
            "entity_name" => $entity_name,
            "entity_primary_key" => $primary_key_value,
        );
        $select -> where($where);
        $result = $this -> select($select, new EntityTag(), $this -> getEntityTagsHydrator());
        return $result;
    }

    public function insertEntityTag($entity_name = null, $primary_key_value, $tag_id) {
        $entity_name = $entity_name ?: $this->tableName;
        $data = array(
            "entity_name" => $entity_name,
            "entity_primary_key" => $primary_key_value,
            "tag_id" => $tag_id,
        );
        $entity_log = $this -> getLogsHydrator() -> hydrate($data, new EntityTag());
        try {
            return $result = parent::insert($entity_log, $this -> entityTagTableName, $this -> getLogsHydrator());        
        } catch(\Exception $e) { echo $e -> getMessage(); }
    }

    public function deleteEntityTag($entity_name = null, $primary_key_value, $tag_id) {
        $entity_name = $entity_name ?: $this->tableName;
        $where = array(
            "entity_name" => $entity_name,
            "entity_primary_key" => $primary_key_value,
            "tag_id" => $tag_id,
        );
        return $result = parent::delete($where, $this -> entityTagTableName);
    }

    public function processTags($tags, $primary_key_value) {
        try {
            $entries = $this -> fetchEntityTags(null, $primary_key_value);
            $prev_tags = array();
            foreach($entries as $tag) {
                $prev_tags[] = $tag -> getTagid();
            }

            foreach($tags as $t) { echo $t; }
            foreach($tags as $tag_name) {
                $tag_name = trim($tag_name);
                $tag = $this -> fetchTagByName($tag_name);
                if($tag !== false) {
                    $tag_id = $tag -> getId();
                } else {
                    $result = $this -> insertTag($tag_name);
                    if($result !== false && $result > 0) {
                        $tag_id = $result;
                    }
                }

                if(isset($tag_id) && $tag_id) {
                    $search = array_search($tag_id, $prev_tags);
                    if($search !== false) unset($prev_tags[$search]);
                    else {
                        $r = $this -> insertEntityTag(null, $primary_key_value, $tag_id);
                    }
                }
            }
            if(count($prev_tags)) {
                foreach($prev_tags as $tag) {
                    $this -> deleteEntityTag(null, $primary_key_value, $tag);
                }
            }
            return true;
        } catch(\Exception $e) {}
        return false;
    }
}