<?php
namespace nGen\Zf2Entity\Controller;

use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

abstract class EntityStatisticsController extends AbstractActionController {

	protected $mainService;
	protected $messenger;
	protected $defaultViewData = array(
		'manager' => array(
			'thumbnail_field' => array(
				'active' => false,
			),
			'title_field' => array(
				'active' => true,
				'details' => array(
					'label' => 'Title',
					'field' => 'title'
				),
			),
		),
	);

	public function __construct() {
		$this -> messenger = $this -> flashMessenger();
	}

	protected function redirectToMain($params = array(), $query = array()) {
		$query = array_merge($query, $this -> params() -> fromQuery());
		$options = array("query" => $query);
		$params = array_merge($params, $this -> params() -> fromRoute());
		$params['action'] = 'index';
		unset($params['id']);
		return $this -> redirect() -> toRoute($this -> viewData['mainRouteName'], $params, $options);		
	}

	protected function redirectToCurrent($params = array(), $query = array()) {
		$query = array_merge($query, $this -> params() -> fromQuery());
		$options = array("query" => $query);
		$params = array_merge($params, $this -> params() -> fromRoute());
		return $this -> redirect() -> toRoute($this -> viewData['mainRouteName'], $params, $options);		
	}

	protected function getViewModel($viewData = null) {
		if(!$viewData) $viewData = $this -> viewData;		
		$viewData = array_replace_recursive($this -> defaultViewData, $viewData);
		$viewData['route']['params'] = $this -> params() -> fromRoute();
		$viewData['route']['options']['query'] = $this -> params() -> fromQuery();
		return new ViewModel($viewData);
	}

	abstract protected function init();

	protected function DefaultIndexAction($where = array(), $order = array(), $joins = array()) {
		$browseType = $this->params() -> fromQuery('browse', 'active');
		switch($browseType) {
			case "active": $paginatedEntries = $this -> mainService -> fetchAllActivePaginated($where, $order, $joins); break;
			case "disabled": $paginatedEntries = $this -> mainService -> fetchAllDisabledPaginated($where, $order, $joins); break;
			case "trash": $paginatedEntries = $this -> mainService -> fetchAllDeletedPaginated($where, $order, $joins); break;
			case "all": $paginatedEntries = $this -> mainService -> fetchAllPaginated($where, $order, $joins); break;
			default: die('UNAUTHORISED'); break;
		}
		$paginatedEntries -> setCurrentPageNumber((int) $this->params() -> fromQuery('page', 1));
		$paginatedEntries -> setItemCountPerPage(10);

		$this -> viewData['rows'] = $paginatedEntries;
		$this -> viewData['title_field'] = $this -> mainService -> getTitleField();
		$viewModel = $this -> getViewModel();
        $viewModel -> setTemplate('shared/browse.phtml');
		return $viewModel;
	}

	public function indexAction() {
		return $this -> DefaultIndexAction();
	}

	public function deleteAction() {
    	$id = (int) $this -> params() -> fromRoute('id', 0);
        if($id > 0 && $this -> mainService -> fetchById($id) !== false) {
    		$response = $this -> mainService -> delete($id);
    		if($response === true) {
				$this -> messenger -> addSuccessMessage($this -> viewData['title']." id: $id has been deleted."); 
			} else {
				$this -> messenger -> addErrorMessage("Error encountered while deleting {$this -> viewData['title']} with id: $id.");
			}
		} else {
			$this -> messenger -> addErrorMessage("{$this -> viewData['title']} with id: $id was not found. It may have already been deleted.");
		}

		return $this -> redirectToMain();
    }

	public function undeleteAction() {
    	$id = (int) $this -> params() -> fromRoute('id', 0);
        if($id > 0 && $this -> mainService -> fetchById($id) !== false) {
    		$response = $this -> mainService -> undelete($id);
    		if($response === true) {
				$this -> messenger -> addSuccessMessage($this -> viewData['title']." id: $id has been recovered."); 
			} else {
				$this -> messenger -> addErrorMessage("Error encountered while recovering {$this -> viewData['title']} with id: $id.");
			}
		} else {
			$this -> messenger -> addErrorMessage("{$this -> viewData['title']} with id: $id was not found. It may have already been deleted.");
		}
		return $this -> redirectToMain();
    }

	public function deleteAllAction() {
		$response = $this -> mainService -> deleteAll();
		if($response === true) {
			$this -> messenger -> addSuccessMessage("All ".$this -> viewData['pluralTitle']." has been deleted."); 
		} else {
			$this -> messenger -> addErrorMessage("Error encountered while deleting all {$this -> viewData['pluralTitle']}.");
		}
		return $this -> redirectToMain();
    }    

	public function deletePermanentAction() {
    	$id = (int) $this -> params() -> fromRoute('id', 0);
        if($id > 0 && $this -> mainService -> fetchById($id) !== false) {
    		$response = $this -> mainService -> permanentDelete($id);
    		if($response === true) {
				$this -> messenger -> addSuccessMessage($this -> viewData['title']." id: $id has been permanently deleted."); 
			} else {
				$this -> messenger -> addErrorMessage("Error encountered while deleting {$this -> viewData['title']} with id: $id.");
			}
		} else {
			$this -> messenger -> addErrorMessage("{$this -> viewData['title']} with id: $id was not found. It may have already been deleted.");
		}
		return $this -> redirectToMain();
    }

    public function enableAction() {
    	$id = (int) $this -> params() -> fromRoute('id', 0);
        if($id > 0 && $this -> mainService -> fetchById($id) !== false) {
    		$response = $this -> mainService -> enable($id);
    		if($response === true) {
				$this -> messenger -> addSuccessMessage($this -> viewData['title']." id: $id has been enabled."); 
			} else {
				$this -> messenger -> addErrorMessage("Error encountered while enabling {$this -> viewData['title']} with id: $id.");
			}
		} else {
			$this -> messenger -> addErrorMessage("{$this -> viewData['title']} with id: $id was not found. It may have already been deleted.");
		}
		return $this -> redirectToMain();
    }
    
    public function disableAction() {
    	$id = (int) $this -> params() -> fromRoute('id', 0);
        if($id > 0 && $this -> mainService -> fetchById($id) !== false) {
    		$response = $this -> mainService -> disable($id);
    		if($response === true) {
				$this -> messenger -> addSuccessMessage($this -> viewData['title']." id: $id has been disabled."); 
			} else {
				$this -> messenger -> addErrorMessage("Error encountered while disabling {$this -> viewData['title']} with id: $id.");
			}
		} else {
			$this -> messenger -> addErrorMessage("{$this -> viewData['title']} with id: $id was not found. It may have already been deleted.");
		}
		return $this -> redirectToMain();
    }    

    public function moveUpAction() {
    	$id = (int) $this -> params() -> fromRoute('id', 0);
        if($id > 0 && $this -> mainService -> fetchById($id) !== false) {
    		$response = $this -> mainService -> increaseOrder($id);
    		if($response === true) {
				$this -> messenger -> addSuccessMessage($this -> viewData['title']." id: $id has been moved up.");
			} else {
				$this -> messenger -> addErrorMessage("Error encountered while moving {$this -> viewData['title']} with id: $id.");
			}
		} else {
			$this -> messenger -> addErrorMessage("{$this -> viewData['title']} with id: $id was not found. It may have already been deleted.");
		}
		return $this -> redirectToMain();
    }

    public function moveDownAction() {
    	$id = (int) $this -> params() -> fromRoute('id', 0);
        if($id > 0 && $this -> mainService -> fetchById($id) !== false) {
    		$response = $this -> mainService -> decreaseOrder($id);
    		if($response === true) {
				$this -> messenger -> addSuccessMessage($this -> viewData['title']." id: $id has been moved down.");
			} else {
				$this -> messenger -> addErrorMessage("Error encountered while moving {$this -> viewData['title']} with id: $id.");
			}
		} else {
			$this -> messenger -> addErrorMessage("{$this -> viewData['title']} with id: $id was not found. It may have already been deleted.");
		}
		return $this -> redirectToMain();
    }

    public function unlockAction() {
    	$id = (int) $this -> params() -> fromRoute('id', 0);
        if($id > 0 && $this -> mainService -> fetchById($id) !== false) {
    		$response = $this -> mainService -> unlock($id);
    		if($response === true) {
				$this -> messenger -> addSuccessMessage($this -> viewData['title']." id: $id has been unlocked.");
			} else {
				$this -> messenger -> addErrorMessage("Error encountered while moving {$this -> viewData['title']} with id: $id.");
			}
		} else {
			$this -> messenger -> addErrorMessage("{$this -> viewData['title']} with id: $id was not found. It may have already been deleted.");
		}
		return $this -> redirectToMain();
    }

	
}
