<?php
namespace nGen\Zf2Entity\Mapper;

use nGen\Zf2Entity\Model\EntityStatistics;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Db\ResultSet\HydratingResultSet;
use nGen\Zfc\Mapper\ExtendedAbstractDbMapper;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;

class EntityStatisticsDbMapper extends ExtendedAbstractDbMapper {

    protected $mainDbMapper;
    protected $statTableName = "application_entity_statistics";
    protected $primary_key_field = "id";
    protected $user_entity_id = 0;
    protected $defaultWhereRestriction = array();
    protected $ordering = "DESC";

    protected $statisticsHydrator = null;

    public function setStatisticsHydrator(HydratorInterface $hydrator) {
        $this -> statisticsHydrator = $hydrator;
    }

    public function getStatisticsHydrator() {
        return $this -> statisticsHydrator === null ? new \Zend\Stdlib\Hydrator\ClassMethods() : $this -> statisticsHydrator;
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
            $rowData['locked'],
            $rowData['locked_by'],
            $rowData['locked_on'],
            $rowData['created_by'],
            $rowData['created_on'],
            $rowData['last_modified_by'],
            $rowData['last_modified_on']
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
            $rowData['locked'],
            $rowData['locked_by'],
            $rowData['locked_on'],
            $rowData['created_by'],
            $rowData['created_on'],
            $rowData['last_modified_by'],
            $rowData['last_modified_on']
        );
        $update->set($rowData)
            ->where($where);

        $statement = $sql->prepareStatementForSqlObject($update);

        return $statement->execute();
    }

    public function insertEntity($entity, $entity_name = null, HydratorInterface $hydrator = null, $noTransaction = false) {
        $entity_name = $entity_name ?: $this->tableName;
        if(!$noTransaction) $this -> beginTransaction();
        try {
            $result = $this -> insert($entity, $entity_name, $hydrator);
            $generated_value = $result->getGeneratedValue(); 
            if($generated_value > 0) {
                $entity -> setId($generated_value);
                $stat_status = $this -> insertEntityStatistics($entity_name, $generated_value, true, false, $this -> getHighestOrdering()+1);
                if(!$noTransaction) $this -> commit();
                return true;
            } else {
                $this -> rollback();
                return false;
            }
        } catch(\Exception $e) {
            if(!$noTransaction) $this -> rollback();
            return false;
        }
    }
    
    public function updateEntity($entity, $where = array(), $entity_name = null, HydratorInterface $hydrator = null, $noTransaction = false) {
        $entity_name = $entity_name ?: $this->tableName;
        if(!$noTransaction) $this -> beginTransaction();
        try {
            $where['id'] = $entity -> getId();
            $result = $this -> updateAll($entity, $where, $entity_name, $hydrator);
            $this -> updateEntityStatistics($entity_name, $entity -> getId());
            if(!$noTransaction) $this -> commit();
            return true;
        } catch(\Exception $e) {
            if(!$noTransaction) $this -> rollback();
            return false;
        }
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
            "locked_by" => '',
            "locked_on" => '',
            "created_by" => $this -> user_entity_id,
            "created_on" => date("Y-m-d H:i:s"),
            "last_modified_by" => $this -> user_entity_id,
            "last_modified_on" => date("Y-m-d H:i:s"),
        );
        try {
            $statEntity = $this -> getStatisticsHydrator() 
                -> hydrate($data, new EntityStatistics());
            $result = parent::insert($statEntity, $this -> statTableName, $this -> getStatisticsHydrator());   
            return true;
        } catch(\Exception $e) {
            echo $e -> getMessage();
            return false;
        }
    }

    public function updateEntityStatistics($entity_name = null, $primary_key_value) {
        $entity_name = $entity_name ?: $this->tableName;
        $data = array(
            "last_modified_by" => $this -> user_entity_id,
            "last_modified_on" => date("Y-m-d H:i:s"),
        );

        $where = array(
            "entity_name" => $entity_name,
            "entity_primary_key" => $primary_key_value,            
        );
        try {
            $result = parent::updateField($data, $where, $this -> statTableName);
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    public function getFetchSelect($where = array(), $order = array(), $joins = array(), $entity_name = null, $primary_key_field = null, $unLinked = false) {
        $primary_key_field = $primary_key_field ?: $this -> primary_key_field;
        $entity_name = $entity_name ?: $this->tableName;

        $select = $this -> getSelect($entity_name);
        if($unLinked === false) {
            $select -> join(
                array('stat' => $this -> statTableName),
                "stat.entity_primary_key = ".$entity_name.".".$primary_key_field,
                array(
                    "ordering" => "entity_ordering",
                    "views",
                    "status",
                    "deleted",
                    "locked",
                    "locked_by",
                    "locked_on",
                    "created_by",
                    "created_on",
                    "last_modified_by",
                    "last_modified_on",
                )
            );
            if(count($joins)) {
                foreach($joins as $join) {
                    $select -> join($join[0], $join[1], $join[2]);
                }
            }
            $where['stat.entity_name'] = $entity_name;
        }
        $where = array_replace_recursive($this -> defaultWhereRestriction, $where);
        $select -> where($where);
        $select -> order($order);
        return $select;
    }

    public function fetchAll($paginated = false, $where = array(), $order = array(), $joins = array(), $entity_name = null, $primary_key_field = null, $entity_prototype = null, HydratorInterface $hydrator = null, $unLinked = false) {
        $select = $this -> getFetchSelect($where, $order, $joins, $entity_name, $primary_key_field, $unLinked);

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

    public function fetchOne($where = array(), $joins = array(), $entity_name = null, $primary_key_field = null, $entity_prototype = null, HydratorInterface $hydrator = null, $unLinked = false) {
        $select = $this -> getFetchSelect($where, array(), $joins, $entity_name, $primary_key_field, $unLinked);
        $entity = $this -> select($select, $entity_prototype, $hydrator) -> current();
        return $entity; 
    }
           
    public function fetchById($id, $where = array(), $joins = array(), $entity_name = null, $primary_key_field = null, $entity_prototype = null, HydratorInterface $hydrator = null, $unLinked = false) { 
        $primary_key_field = $primary_key_field ?: $this -> primary_key_field;
        $entity_name = $entity_name ?: $this->tableName; 
        $where[$entity_name.".".$primary_key_field] = $id;
        return $this -> fetchOne($where, $joins, $entity_name, $primary_key_field, $entity_prototype, $hydrator, $unLinked);
    }

    public function fetchAllActive($paginated = false, $where = array(), $order = array(), $joins = array(), $entity_name = null, $primary_key_field = null, $entity_prototype = null, HydratorInterface $hydrator = null, $unLinked = false) {
        $where['status'] = true;
        $where['deleted'] = 0;
        $order[] = 'ordering '.$this -> ordering;
        return $this -> fetchAll($paginated, $where, $order, $joins, $entity_name, $primary_key_field, $entity_prototype, $hydrator, $unLinked);
    }

    public function fetchAllEnabled($paginated = false, $where = array(), $order = array(), $joins = array(), $entity_name = null, $primary_key_field = null, $entity_prototype = null, HydratorInterface $hydrator = null, $unLinked = false) {
        $where['status'] = true;
        $where['deleted'] = 0;
        return $this -> fetchAll($paginated, $where, $order, $joins, $entity_name, $primary_key_field, $entity_prototype, $hydrator, $unLinked);        
    }

    public function fetchAllDisabled($paginated = false, $where = array(), $order = array(), $joins = array(), $entity_name = null, $primary_key_field = null, $entity_prototype = null, HydratorInterface $hydrator = null, $unLinked = false) {
        $where['status'] = 0;
        $where['deleted'] = 0;
        return $this -> fetchAll($paginated, $where, $order, $joins, $entity_name, $primary_key_field, $entity_prototype, $hydrator, $unLinked);  
    }

    public function fetchAllDeleted($paginated = false, $where = array(), $order = array(), $joins = array(), $entity_name = null, $primary_key_field = null, $entity_prototype = null, HydratorInterface $hydrator = null, $unLinked = false) {
        $where['deleted'] = true;
        return $this -> fetchAll($paginated, $where, $order, $joins, $entity_name, $primary_key_field, $entity_prototype, $hydrator, $unLinked);  
    }

    public function fetchAllUnDeleted($paginated = false, $where = array(), $order = array(), $joins = array(), $entity_name = null, $primary_key_field = null, $entity_prototype = null, HydratorInterface $hydrator = null, $unLinked = false) {
        $where['deleted'] = 0;
        return $this -> fetchAll($paginated, $where, $order, $joins, $entity_name, $primary_key_field, $entity_prototype, $hydrator, $unLinked);  
    }
    
    public function delete($id, $where = array(), $entity_name = null, $primary_key_field = null) {
        $primary_key_field = $primary_key_field ?: $this -> primary_key_field;
        $entity_name = $entity_name ?: $this->tableName;

        $where[$primary_key_field] = $id;
        $statistic_where = array("entity_name" => $entity_name, "entity_primary_key" => $id,);

        try {
            $result = parent::delete($where, $entity_name);
            $statistic_result = parent::delete($statistic_where, $this -> statTableName);
            return true;
        } catch(\Exception $e) {
            return false;
        }
    }

    public function setStatisticField($field, $value, $id, $where = array(), $entity_name = null, $primary_key_field = null) {
        $primary_key_field = $primary_key_field ?: $this -> primary_key_field;
        $entity_name = $entity_name ?: $this->tableName;
        $where[$primary_key_field] = $id;        
        $statistic_where = array("entity_name" => $entity_name, "entity_primary_key" => $id, );

        $data = array(
            "last_modified_by" => $this -> user_entity_id,
            "last_modified_on" => date("Y-m-d H:i:s"),
        );

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
            $result = parent::updateField($data, $statistic_where, $this -> statTableName);
            return true;
        } catch(\Exception $e) {
            die($e -> getMessage());
            return false;
        }
    }

    public function lock($id, $where = array(), $entity_name = null, $primary_key_field = null) {
        return $this -> setStatisticField(
            array('locked', 'locked_by', 'locked_on'), 
            array('1', $this -> user_entity_id, date("Y-m-d H:i:s")), 
            $id, $where, $entity_name, $primary_key_field);
    }

    public function unlock($id, $where = array(), $entity_name = null, $primary_key_field = null) {
        return $this -> setStatisticField(
            array('locked', 'locked_by', 'locked_on'), 
            array('0', '', ''), 
            $id, $where, $entity_name, $primary_key_field);
    }

    public function trash($id, $where = array(), $entity_name = null, $primary_key_field = null) {
        return $this -> setStatisticField("deleted", true, $id, $where, $entity_name, $primary_key_field);
    }

    public function trashAll($where = array(), $entity_name = null, $primary_key_field = null) {
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

    public function recycle($id, $where = array(), $entity_name = null, $primary_key_field = null) {
        return $this -> setStatisticField("deleted", false, $id, $where, $entity_name, $primary_key_field);
    }

    public function enable($id, $where = array(), $entity_name = null, $primary_key_field = null) {
        return $this -> setStatisticField("status", true, $id, $where, $entity_name, $primary_key_field);
    }

    public function disable($id, $where = array(), $entity_name = null, $primary_key_field = null) {
        return $this -> setStatisticField("status", false, $id, $where, $entity_name, $primary_key_field);
    }

    /**
    * Ordering Related
    */

    public function getHighestOrdering($where = array(), $entity_name = null, $primary_key_field = null) {
        try {
            $select = $this -> getFetchSelect($where, array('ordering DESC'), $entity_name, $primary_key_field);
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

    public function findPrevInOrder($ordering, $where = array(), $entity_name = null, $primary_key_field = null) {
        try {
            $predicate = new \Zend\Db\Sql\Where();
            $where[] = $predicate -> greaterThan('entity_ordering', $ordering);
            $select = $this -> getFetchSelect($where, array('ordering '.$this -> ordering == 'DESC' ? 'ASC' : 'DESC'), $entity_name, $primary_key_field);
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

    public function findNextInOrder($ordering, $where = array(), $entity_name = null, $primary_key_field = null) {
        try {
            $predicate = new \Zend\Db\Sql\Where();
            $where[] = $predicate -> lessThan('entity_ordering', $ordering);
            $select = $this -> getFetchSelect($where, array('ordering '.$this -> ordering), $entity_name, $primary_key_field);
            $select -> limit(1);
            $entity = $this -> select($select) -> current();
            if($entity) {
                return $entity;
            }
            return false;
        } catch(\Exception $e) {
            return false;
        }
    }

    public function decreaseOrder($id, $where = array(), $entity_name = null, $primary_key_field = null) {
        $this -> beginTransaction();
        try {
            $entity = $this -> fetchById($id, $where, $entity_name, $primary_key_field);
            $ordering = $entity -> getOrdering();
            $next = $this -> findNextInOrder($ordering, $where, $entity_name, $primary_key_field);
            if($next !== false) {
                $this -> setStatisticField("entity_ordering", $next -> getOrdering(), $id, $where, $entity_name, $primary_key_field);
                $this -> setStatisticField("entity_ordering", $ordering, $next -> getId(), $where, $entity_name, $next -> getId());
                $this -> commit();
                return true;
            } else {
                $this -> rollback();
                return false;
            }
            
        } catch(\Exception $e) {
            $this -> rollback();
            return false;
        }
    }

    public function increaseOrder($id, $where = array(), $entity_name = null, $primary_key_field = null) {
        $this -> beginTransaction();
        try {
            $entity = $this -> fetchById($id, $where, $entity_name, $primary_key_field);
            $ordering = $entity -> getOrdering();
            $prev = $this -> findPrevInOrder($ordering, $where, $entity_name, $primary_key_field);
            if($prev !== false) {
                $this -> setStatisticField("entity_ordering", $prev -> getOrdering(), $id, $where, $entity_name, $primary_key_field);
                $this -> setStatisticField("entity_ordering", $ordering, $prev -> getId(), $where, $entity_name, $prev -> getId());
                $this -> commit();
                return true;
            } else {
                $this -> rollback();
                return false;                
            }
        } catch(\Exception $e) {
            $this -> rollback();
            return false;
        }
    }
}
