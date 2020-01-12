<?php

class SiteController extends Controller {

	public function actionIndex() {
		if (User::isGuest()) {
			header('Location: /login');
			return true;
		}

		//$str = Project::showNode($projects);
		$this->title = 'Главная';
		/*
		$dom = null;
		try {
			$dom = new DOM();
			$dom->loadProjects();
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		*/
		//$jsonObj = Project::convertJson($dom->projects);
		//$jsonObj = 'data : [ ' . $jsonObj . ']';
		
		//$tree = Project::showTree($dom->projects);

		//$jsonObj = json_encode($dom->projects);
		

		//$listST31mm = new Material('лист ст3 1мм', 'м2', 56,5, 'Поставщик');
		//$listST31mm->add();
		//$material = Material::getMaterialByIdAndProducer(29, 3);
		//$material->quantity = 3;
		//$material->price = 17.8;
		//$material->name = "ст3сп 1мм";
		//$material->measure = "м2";
		//$material->producerId = 3;
		//$material->update();
		//$material->delete();
		//
		//$detail = new detail('деталь', '008', 'название детали', '0', 30, 5, 1);
		//$detail = Node::getNodeById(2, 1);
		//$detail->setMaterial($material);
		//$detail->collectPrice();
		//$detail->realNode();
		//$detail->update();
		//$detail->delete();
		//$detail->add(8);
		
		//$assembly = new Assembly('сборка', '009', 'название сборки', '0', 10, 1, 1);
		//$assembly = Node::getNodeById(15,1);
		//$assembly->parentId = 8;
		//$assembly->suffix = '1';
		//$assembly->delete();
		//$assembly->update();
		//$assembly->add();
		//echo " <script>console.log('*******************');</script>";
		//	
		/*
		*
		*

		echo "<pre>";
		print_r($dom->projects);
		echo "</pre>";
		exit();
		
		*
		*
		*/
		require_once(ROOT . '/views/index.php');

		return true;
	}

	public function actionEnter() : bool {
		$errors = false;

		if (isset($_POST['submit'])) {

			$login = $_POST['login'];
			$password = $_POST['password'];

			if (User::isUserExist($login, $password)) {
				$user = User::getUserIdByLogin($login);
				User::auth($user);
				header("Location: /");
			} else {
				$errors = "wrong password or name";
			}
		}
		$title = "Вход";
		require_once(ROOT . '/views/login.php');

		return true;
	}

	public function actionExit() {
		User::goOut();

		header('Location: /');
	}

	public function actionShowNode($nodeId, $quantity, $parentId) : bool {
		if (User::isGuest()) {
			header('Location: /login');
			return true;
		}
		$this->options['nodelabel'] = true;

		$dom = null;
		try {
			$dom = new DOM();
			$dom->loadProjects();
		} catch (Exception $e) {
			echo $e->getMessage();
		}

		$materials = Material::getMaterials();
		$users = User::getUsers();
		$statuses = Status::getStatuses();
		
		$tree = Project::showTree($dom->projects);

		$node = null;
		try {
			$node = Node::getNodeById($nodeId, $parentId, true, $quantity);
		} catch (Exception $e) {
			$this->info[] = $e->getMessage();
			$this->options['outputinfo'];
			header('Location: /index.php');
		}

		if ($node instanceof Assembly) 
			$this->options['nodetable'] = true;
		else 
			$this->options['detail'] = true;
		

		require_once(ROOT . '/views/index.php');

		return true;
	}

	public function actionOpenFile(int $nodeId) : bool {
		$node = Node::getNodeById($nodeId);
		$filePath = PATH . $node->fileName . $node->extension;
		if (file_exists($filePath)) 
			exec($filePath);
		header('location:' . $_SERVER['HTTP_REFERER']);
		return true;
	}

	public function actionUpdatePrice(int $nodeId) : bool {
		$node = Node::getNodeById($nodeId, 0, true, 1);
		$node->collectPrice();
		//$node->updatePrice();

		header('location:' . $_SERVER['HTTP_REFERER']);

		return true;
	}

	public function actionAddNewNode(int $parentId, int $nodeType) : bool {
		if (User::isAdmin()) {
			$this->title = 'добавление узла';
			$this->options['addnewnode'] = true;

			$dom = null;
			try {
				$dom = new DOM();
				$dom->loadProjects();
			} catch (Exception $e) {
				echo $e->getMessage();
			}

			$materials = Material::getMaterials();
			$users = User::getUsers();
			$statuses = Status::getStatuses();
			
			$tree = Project::showTree($dom->projects);

			if (isset($_POST['submit'])) {
				if ($nodeType == Node::DETAIL) {

				}
				$data = array(
							'prefix' 	=> $_POST['prefix'],
							'number' 	=> $_POST['number'],
							'name'	    => $_POST['name'],
							'suffix'    => isset($_POST['suffix']) ? $_POST['suffix'] : '',
							'node_type' => $nodeType,
							'quantity'  => intval($_POST['quantity']),
							'user_id'   => $_POST['owner'],
							'status' 	=> $_POST['status'],
							'path' 		=> '',
							'note' 		=> isset($_POST['note']) ? $_POST['note'] : ''
				);

				$node = Node::nodeFactory($data, $nodeType);

				if ($nodeType == Node::DETAIL && isset($_POST['material']) 
											&& isset($_POST['material_quantity'])) {
					$material = Material::getMaterialByMatchingId($_POST['material']);
					$material->quantity = $_POST['material_quantity'];
					$node->setMaterial($material);
				}

				if ($node->add($parentId)) {
					$this->info[] = "Узел успешно добавлен!";
				} 
				else {
					$this->info[] = "Не удалось создать узел, возможно, такой уже существует!";
				}
				$this->options['outputinfo'] = true;
				
			}

			require_once(ROOT . '/views/index.php');
		}

		return true;
	}

	public function actionUpdateNode(int $nodeId) : bool {
		if (User::isAdmin()) {
			$this->options['nodelabel'] = true;
			$this->title = 'Редактирование';
			$dom = null;
			try {
				$dom = new DOM();
				$dom->loadProjects();
			} catch (Exception $e) {
				echo $e->getMessage();
			}

			$materials = Material::getMaterials();
			$users = User::getUsers();
			$statuses = Status::getStatuses();
			
			$tree = Project::showTree($dom->projects);

			$node = Node::getNodeById($nodeId);

			if (isset($_POST['submit'])) {

				$node->prefix = $_POST['prefix'];
				$node->number = $_POST['number'];
				$node->name = $_POST['name'];
				$node->suffix = isset($_POST['suffix']) ? $_POST['suffix'] : '';
				$node->status = $_POST['status'];
				$node->note = isset($_POST['note']) ? $_POST['note'] : '';
				$node->ownerId = $_POST['owner'];

				if (isset($_POST['material']) && isset($_POST['quantity'])) {
					$material = Material::getMaterialByMatchingId($_POST['material']);
					$material->quantity = $_POST['material_quantity'];
					$node->setMaterial($material);
				}

				if ($node->update()) 
					$this->info[] = "Успешно обновлен!";
				else 
					$this->info[] = "Не удалось обновить!";
				
				$this->options['outputinfo'] = true;
				$this->options['nodelabel'] = true;

				require_once(ROOT . '/views/index.php');
				
				return true;
			}

			$this->options['editnode'] = true;

			require_once(ROOT . '/views/index.php');
		}

		return true;
	}

	public function actionDeleteNode(int $nodeId) : bool {
		if (User::isAdmin()) {
			$node = Node::getNodeById($nodeId);

			if ($node->delete()) {
				$this->info[] = "Узел удалён!";
				header('Location: /index.php');
			} 
			else {
				$this->info[] = "Не удалось удалить!";
				$this->options['outputinfo'] = true;
			} 
			require_once(ROOT . '/views/index.php');
		}

		return true;
	}

	public function actionRemoveNode(int $nodeId, int $parentId) : bool {
		if (User::isAdmin()) {
			$node = Node::getNodeById($nodeId);
			$node->parentId = $parentId;

			if ($node->remove()) {
				$this->info[] = "Узел убран!";
				$this->options['outputinfo'] = "Узел удален!";
				header('Location: /index.php');
			}
			else {
				$this->info[] = "Не удалось убрать узел из сборки, возможно он больше не входит ни в одну из сборок.";
				$this->options['outputinfo'] = true;
			}
			require_once(ROOT . '/views/index.php');
		}

		return true;
	}

	public function actionCopyNode(int $nodeId = 0) : bool {
		if (User::isAdmin()) {
			$this->title = 'Добавление существующего узла';
			$dom = null;
			try {
				$dom = new DOM();
				$dom->loadProjects();
			} catch (Exception $e) {
				echo $e->getMessage();
			}

			$tree = Project::showTree($dom->projects);

			$node = Node::getNodeById($nodeId);

			if (isset($_POST['submit'])) {

				$node->quantity = isset($_POST['quantity'])? $_POST['quantity'] : 1;
				if (isset($_POST['node_to_add'])) {
					if ($node->copy($_POST['node_to_add'])) {
						$this->info[] = "Узел добавлен!";
						$this->options['outputinfo'] = "Узел добавлен!";
					}
					else {
						$this->info[] = "Не удалось добавить узел, возможно он уже добавлен!";
						$this->options['outputinfo'] = true;
					}
				}

			}
			else { 
				$nodes = Node::getAllNodes();
				$this->options['nodecopy'] = true;
			}

			require_once(ROOT . '/views/index.php');
		}

		return true;
	}

	public function actionTest() {
		header('Location: /index.php');
		return true;
	}

}

?>