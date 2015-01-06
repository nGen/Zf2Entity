<?php
namespace nGen\Zf2Entity\Controller;

use Zend\View\Model\ViewModel;
use Zend\Mvc\Controller\AbstractActionController;

abstract class EntityStatisticsController extends AbstractActionController {

	protected $mainService;
	protected $childService = null;
	protected $childSettings = array(
		'restrictions' => array(
			'permanent-delete' => '0'
		)
	);

	protected $messenger;
	protected $defaultManagerViewData = array(	
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
			'modes' => array(
				'active' => array(
					'label' => 'Active',
					'glyph' => 'ok-circle',
					'route' => array(
						'name' => array(
							'type' => 'from_view',
							'value' => 'mainRouteName',
						),
						'params' => array(
							'action' => array('type' => 'static', 'value' => 'index',),
						),
						'merge_current_options' => false
					),
					'links' => array(
						"move-down" => array(
							'label' => 'Move Down',
							'glyph' => 'arrow-down',
							'route' => array(
								'name' => array('type' => 'from_view', 'value' => 'mainRouteName',),
								'params' => array(
									'action' => array('type' => 'static', 'value' => 'move-down',),
									'id' => array('type' => 'from_record', 'value' => 'id',),
								),
							),
						),
						"move-up" => array(
							'label' => 'Move Up',
							'glyph' => 'arrow-up',
							'route' => array(
								'name' => array('type' => 'from_view', 'value' => 'mainRouteName',),
								'params' => array(
									'action' => array('type' => 'static', 'value' => 'move-up',),
									'id' => array('type' => 'from_record', 'value' => 'id',),
								),
							),
						),
						"delete" => array(
							'label' => 'Delete',
							'glyph' => 'trash',
							'route' => array(
								'name' => array('type' => 'from_view', 'value' => 'mainRouteName',),
								'params' => array(
									'action' => array('type' => 'static', 'value' => 'delete',),
									'id' => array('type' => 'from_record', 'value' => 'id',),
								),
							),
						),
						'status' => array(
							'type' => 'from_conditional_record',
							'value' => 'status',
							'case' => array(
								'0' => array(
									'label' => 'Enable',
									'glyph' => 'thumbs-up',
									'route' => array(
										'name' => array('type' => 'from_view', 'value' => 'mainRouteName',),
										'params' => array(
											'action' => array('type' => 'static', 'value' => 'enable',),
											'id' => array('type' => 'from_record', 'value' => 'id',),
										),
								   	),
								),
								'1' => array(
									'label' => 'Disable',
									'glyph' => 'thumbs-down',
									'route' => array(
										'name' => array('type' => 'from_view', 'value' => 'mainRouteName',),
										'params' => array(
											'action' => array('type' => 'static', 'value' => 'disable',),
											'id' => array('type' => 'from_record', 'value' => 'id',),
										),
									),
								),
							),
						),
						"edit" => array(
							'label' => 'Edit',
							'glyph' => 'pencil',
							'route' => array(
								'name' => array('type' => 'from_view', 'value' => 'mainRouteName',),
								'params' => array(
									'action' => array('type' => 'static', 'value' => 'edit',),
									'id' => array('type' => 'from_record', 'value' => 'id',),
								),
							),
						),			
					),
					
				),
				'disabled' => array(
					'label' => 'Disabled',
					'glyph' => 'ban-circle',
					'route' => array(
						'name' => array(
							'type' => 'from_view',
							'value' => 'mainRouteName',
						),
						'params' => array(
							'action' => array('type' => 'static', 'value' => 'index',),
						),
						'options' => array(
							'query' => array(
								'browse' => array('type' => 'static', 'value' => 'disabled',),
							),
						),
					),
					'links' => array(
						"delete" => array(
							'label' => 'Delete',
							'glyph' => 'trash',
							'route' => array(
								'name' => array('type' => 'from_view', 'value' => 'mainRouteName',),
								'params' => array(
									'action' => array('type' => 'static', 'value' => 'delete',),
									'id' => array('type' => 'from_record', 'value' => 'id',),
								),
							),
						),
						'enable' => array(
							'label' => 'Enable',
							'glyph' => 'thumbs-up',
							'route' => array(
								'name' => array('type' => 'from_view', 'value' => 'mainRouteName',),
								'params' => array(
									'action' => array('type' => 'static', 'value' => 'enable',),
									'id' => array('type' => 'from_record', 'value' => 'id',),
								),
							),
						),
						"edit" => array(
							'label' => 'Edit',
							'glyph' => 'pencil',
							'route' => array(
								'name' => array('type' => 'from_view', 'value' => 'mainRouteName',),
								'params' => array(
									'action' => array('type' => 'static', 'value' => 'edit',),
									'id' => array('type' => 'from_record', 'value' => 'id',),
								),
							),
						),	 
					),
				),
				'trash' => array(
					'label' => 'Trash',
					'glyph' => 'trash',
					'route' => array(
						'name' => array(
							'type' => 'from_view',
							'value' => 'mainRouteName',
						),
						'params' => array(
							'action' => array('type' => 'static', 'value' => 'index',),
						),
						'options' => array(
							'query' => array(
								'browse' => array('type' => 'static', 'value' => 'trash',),
							),
						),
					),
					'links' => array(
						"delete-permanent" => array(
							'label' => 'Delete Permanently',
							'glyph' => 'fire',
							'route' => array(
								'name' => array('type' => 'from_view', 'value' => 'mainRouteName',),
								'params' => array(
									'action' => array('type' => 'static', 'value' => 'delete-permanent',),
									'id' => array('type' => 'from_record', 'value' => 'id',),
								),
							),
						),
						"undelete" => array(
							'label' => 'UnDelete',
							'glyph' => 'trash',
							'route' => array(
								'name' => array('type' => 'from_view', 'value' => 'mainRouteName',),
								'params' => array(
									'action' => array('type' => 'static', 'value' => 'undelete',),
									'id' => array('type' => 'from_record', 'value' => 'id',),
								),
							),
						),		
						"edit" => array(
							'label' => 'Edit',
							'glyph' => 'pencil',
							'route' => array(
								'name' => array('type' => 'from_view', 'value' => 'mainRouteName',),
								'params' => array(
									'action' => array('type' => 'static', 'value' => 'edit',),
									'id' => array('type' => 'from_record', 'value' => 'id',),
								),
							),
						),
					),
				),
				'all' => array(
					'label' => 'All',
					'route' => array(
						'name' => array(
							'type' => 'from_view',
							'value' => 'mainRouteName',
						),
						'params' => array(
							'action' => array('type' => 'static', 'value' => 'index',),
						),
						'options' => array(
							'query' => array(
								'browse' => array('type' => 'static', 'value' => 'all',),
							),
						),
					),
					'links' => array(
						'status' => array(
							'type' => 'from_conditional_record',
							'value' => 'status',
							'case' => array(
								'0' => array(
									'label' => 'Enable',
									'glyph' => 'thumbs-up',
									'route' => array(
										'name' => array('type' => 'from_view', 'value' => 'mainRouteName',),
										'params' => array(
											'action' => array('type' => 'static', 'value' => 'enable',),
											'id' => array('type' => 'from_record', 'value' => 'id',),
										),
									),
								),
								'1' => array(
									'label' => 'Disable',
									'glyph' => 'thumbs-down',
									'route' => array(
										'name' => array('type' => 'from_view', 'value' => 'mainRouteName',),
										'params' => array(
											'action' => array('type' => 'static', 'value' => 'disable',),
											'id' => array('type' => 'from_record', 'value' => 'id',),
										),
									),
								),
							),
						),
						'deletetion' => array(
							'type' => 'from_conditional_record',
							'value' => 'deleted',
							'case' => array(
								'0' => array(
									'label' => 'Delete',
									'glyph' => 'trash',
									'route' => array(
										'name' => array('type' => 'from_view', 'value' => 'mainRouteName',),
										'params' => array(
											'action' => array('type' => 'static', 'value' => 'delete',),
											'id' => array('type' => 'from_record', 'value' => 'id',),
										),
									),
								),
								'1' => array(
									'label' => 'UnDelete',
									'glyph' => 'trash',
									'route' => array(
										'name' => array('type' => 'from_view', 'value' => 'mainRouteName',),
										'params' => array(
											'action' => array('type' => 'static', 'value' => 'undelete',),
											'id' => array('type' => 'from_record', 'value' => 'id',),
										),
									),
								),
							),
						),						
						"edit" => array(
							'label' => 'Edit',
							'glyph' => 'pencil',
							'route' => array(
								'name' => array('type' => 'from_view', 'value' => 'mainRouteName',),
								'params' => array(
									'action' => array('type' => 'static', 'value' => 'edit',),
									'id' => array('type' => 'from_record', 'value' => 'id',),
								),
							),
						),
					),
				),
			),
	);

	protected $defaultViewData = array(
		'route' => array(
			'params' => array(),
			'options' => array(
				'query' => array(),
			),
		)
	);

	public function __construct() {
		$this -> messenger = $this -> flashMessenger();
	}

	protected function redirectToMain($params = array(), $query = array()) {
		$query = array_replace_recursive($this -> params() -> fromQuery(), $query);
		$options = array("query" => $query);
		$params = array_replace_recursive($this -> params() -> fromRoute(), $params);
		$params['action'] = 'index';
		unset($params['id']);
		return $this -> redirect() -> toRoute($this -> viewData['mainRouteName'], $params, $options);		
	}

	protected function redirectToCurrent($params = array(), $query = array()) {
		$query = array_replace_recursive($this -> params() -> fromQuery(), $query);
		$options = array("query" => $query);
		$params = array_replace_recursive($this -> params() -> fromRoute(), $params);
		return $this -> redirect() -> toRoute($this -> viewData['mainRouteName'], $params, $options);		
	}

	protected function getViewModel($viewData = null) {
		if(!$viewData) $viewData = $this -> viewData;
		$viewData = array_replace_recursive($this -> defaultViewData, $viewData);
		$params = array_replace_recursive($this -> params() -> fromRoute(), $viewData['route']['params']);
		//@todo Needs to be the default way to access the route information
		if(isset($this -> routeData) && count($this -> routeData)) {
			$viewData['route']['data'] = $this -> routeData;
		}

		$viewData['route']['params'] = $params;
		$viewData['route']['options']['query'] = array_replace_recursive($this -> params() -> fromQuery(), $viewData['route']['options']['query']);
		$viewModel = new ViewModel($viewData);

		//If layout file is provided via route parameters then apply it
		if(isset($params['layout_file'])) { $this->layout($params['layout_file']); }

		//If Template file is provided via Params then apply it
		if(isset($params['template_file'])) { $viewModel->setTemplate($params['template_file']); }
		return $viewModel;
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
		$this -> viewData['manager'] = isset($this -> viewData['manager']) ? 
			array_replace_recursive($this -> defaultManagerViewData, $this -> viewData['manager']) : 
			$this -> defaultManagerViewData;
			
		$viewModel = $this -> getViewModel();
		$viewModel -> setTemplate('shared/browse.phtml');
		return $viewModel;
	}

	public function indexAction() {
		$initStatus = $this -> init();
		if($initStatus  !== true) { return $initStatus; }

		return $this -> DefaultIndexAction();
	}

	public function deleteAction() {
		$initStatus = $this -> init();
		if($initStatus  !== true) { return $initStatus; }

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
		$initStatus = $this -> init();
		if($initStatus  !== true) { return $initStatus; }

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
		$initStatus = $this -> init();
		if($initStatus  !== true) { return $initStatus; }

		$response = $this -> mainService -> deleteAll();
		if($response === true) {
			$this -> messenger -> addSuccessMessage("All ".$this -> viewData['pluralTitle']." has been deleted."); 
		} else {
			$this -> messenger -> addErrorMessage("Error encountered while deleting all {$this -> viewData['pluralTitle']}.");
		}
		return $this -> redirectToMain();
	}	

	public function deletePermanentAction() {
		$initStatus = $this -> init();
		if($initStatus  !== true) { return $initStatus; }

		$id = (int) $this -> params() -> fromRoute('id', 0);
		if(isset($this -> childSettings['restrictions']['permanent-delete']) && $this -> childService !== null) {
			$childEntries = $this -> childService -> fetchAllByParent($id);
			if($childEntries -> count() !== $this -> childSettings['restrictions']['permanent-delete']) {
				$this -> messenger -> addErrorMessage("{$this -> viewData['title']} with id: $id cannot be permanently deleted because it is not empty.");
				return $this -> redirectToMain();
			};
		}
		
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
		$initStatus = $this -> init();
		if($initStatus  !== true) { return $initStatus; }

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
		$initStatus = $this -> init();
		if($initStatus  !== true) { return $initStatus; }

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
		$initStatus = $this -> init();
		if($initStatus  !== true) { return $initStatus; }

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
		$initStatus = $this -> init();
		if($initStatus  !== true) { return $initStatus; }

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
		$initStatus = $this -> init();
		if($initStatus  !== true) { return $initStatus; }

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