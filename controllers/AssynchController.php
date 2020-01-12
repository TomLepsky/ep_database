<?php
/*

EXIST не работает!!!

 */
class AssynchController extends Controller {

	public function actionShowTree() : bool {
		if (User::isGuest()) {
			header('Location: /login');
			return true;
		}

		$nodes = array();
		if (isset($_GET['li_attr'])) {
			$nodes = Node::getChildNodes(intval($_GET['li_attr']['node_id']));
		} else {
			$nodes = Node::getChildNodes(0);
		}

		require (ROOT . '/views/modules/menutree.php');
		return true;
	}

	public function actionShowNode(int $nodeId, int $quantity, int $parentId) : bool {
		if (User::isGuest()) {
			header('Location: /login');
			return true;
		}

		try {
			$node = Product::getNodeById($nodeId, $parentId, true, $quantity);
			$toShow;
			if ($node->nodeType == Node::ASSEMBLY || $node->nodeType == Node::PARTITION)
				$toShow = $node->child;
			else 
				$toShow[] = $node;

			require_once(ROOT . '/views/modules/nodelabel.php');
			require_once(ROOT . '/views/modules/nodetable.php');
		} 
		catch (NodeException $e) {
			$this->message[] = $e->getMessage();
			require_once(ROOT . '/views/modules/info.php');
		}

		return true;
	}

	public function actionAddNewNode(int $parentId, int $nodeType) : bool {
		if (User::isAdmin()) {

			$materials 		= Material::getMaterials();
			$users 			= User::getUsers();
			$statuses 		= Status::getStatuses();
			$sheetFormats 	= SheetFormat::getSheetFormats();
			$classifiers	= Classifier::getAllClassifiers();

			$data = array();
			if (isset($_POST['name'])) {
				$data = array(
							'prefix' 		=> isset($_POST['prefix']) ? trim($_POST['prefix']) : "",
							'classifier'	=> isset($_POST['classifier']) ? intval($_POST['classifier']) : 0,
							'number' 		=> isset($_POST['number']) ? intval($_POST['number']) : 0,
							'name'	    	=> isset($_POST['name']) ? trim($_POST['name']) : "",
							'suffix'		=> '',
							'node_type' 	=> intval($nodeType),
							'quantity'  	=> isset($_POST['quantity']) ? intval($_POST['quantity']) : 0,
							'user_id'   	=> isset($_POST['owner']) ? $_POST['owner'] : "",
							'status' 		=> isset($_POST['status']) ? $_POST['status'] : "",
							'path' 			=> '',
							'note' 			=> isset($_POST['note']) ? $_POST['note'] : '',
							'version' 		=> 0,
							'sheet_format'	=> isset($_POST['sheet_format']) ? $_POST['sheet_format'] : "",
							'parent_id'		=> intval($parentId),
				);
										
				if (!Node::isNodeExists($data['number'], $data['classifier'])) {

					if (((($parentNodeType = Node::getNodeTypeById($data['parent_id'])) != Node::NONE) || $parentId == 0) && 
						Node::isNodeTypeExists($data['node_type'])) {

						if ($data['node_type'] < Node::NONE) {
							$node = Product::nodeFactory($data);
							$node->sheetFormat = $data['sheet_format'];
						}
						else {
							$node = Partition::nodeFactory($data);
						}

						$node->parentId = $data['parent_id'];
						

						if ($nodeType == Node::DETAIL && isset($_POST['material']) 
													&& isset($_POST['material_quantity'])) {
							$material = Material::getMaterialByMatchingId($_POST['material']);
							$material->quantity = floatval(str_replace(",", ".", $_POST['material_quantity']));
							$node->setMaterial($material);
						}

						if ($parentNodeType == Node::PARTITION) 
							$node->path = $node->getParentPath() . DIRECTORY_SEPARATOR . $node->fileName;

						$success = true;
						switch ($node->nodeType) {
							case Node::PARTITION:
								if ($parentId == 0) {
									$node->path = DIRECTORY_SEPARATOR . $node->fileName;
								}
								elseif ($parentNodeType == Node::ASSEMBLY) {
										$node->path = dirname($node->getParentPath(), 2) . DIRECTORY_SEPARATOR . $node->fileName;
								}
								if (!file_exists($node->path))
									if (!mkdir(WORK_FOLDER . $node->path))
										$success = false;
								break;

							case Node::ASSEMBLY:
								if ($parentNodeType == Node::ASSEMBLY) {
									$node->path = dirname($node->getParentPath(), 2) . DIRECTORY_SEPARATOR . ASSEMBLIES . DIRECTORY_SEPARATOR . $node->filename;

									if (isset($_POST['new_folder']) && $_POST['new_folder'] == 'yes') {
										if (!$this->createAssemblyFolders($node->path))
											$success = false;

										$node->path .= DIRECTORY_SEPARATOR . DOCUMENTATION . DIRECTORY_SEPARATOR . $node->fileName;
									}
								}
								break;

							case Node::DETAIL:
								if ($parentNodeType == Node::ASSEMBLY) {
									$node->path = dirname($node->getParentPath(), 2) . DIRECTORY_SEPARATOR . DETAILS . DIRECTORY_SEPARATOR . $node->fileName;
								}
								break;	

							default:

								break;
						}

						if ($success) {
							if ($node->add()) {
								if ($node instanceof Product) {
									Classifier::increaseEmptyNumber($data['classifier']);
									if (is_uploaded_file($_FILES["inputfile2D"]["tmp_name"])) {
										rename($_FILES['inputfile2D']['tmp_name'], WORK_FOLDER . $node->path . $node->extension2D);
										$this->message[] = "Чертёж успешно добавлен.";
									}
									else {
										$this->massage[] = "Чертёж не был загружен.";
									}

									if (is_uploaded_file($_FILES["inputfile3D"]["tmp_name"])) {
										rename($_FILES['inputfile3D']['tmp_name'], WORK_FOLDER . $node->path . $node->extension3D);
										$this->message[] = "Модель успешно добавлена.";
									}
									else {
										$this->massage[] = "Модель не была загружена.";
									}
								}

								$this->message[] = "Узел успешно добавлен.";
							}
							else {
								$this->message[] = "Не удалось создать узел, возможно нет подключения к БД.";
							}
						}
						else {
							$this->message[] = "Не удалось создать папки, возможно у вас нет прав доступа или потеряно соединение с сервером.";
						}
					}
					else {
						$this->message[] = "Не верный тип узла.";
					}
					
				}
				else {
					$this->message[] = "Не удалось добавить узел, возможно с таким номером уже существует или неверен классификатор.";
				}

				require_once(ROOT . '/views/index.php');

			}
			else {
				require_once(ROOT . '/views/modules/addnewnode.php');
			}
		}

		return true;
	}

	public function actionUpdateProduct(int $nodeId, int $parentId) : bool {
		if (User::isAdmin()) {

			try {

				$node 			= Product::getNodeById($nodeId, $parentId);
				$materials 		= Material::getMaterials();
				$users 			= User::getUsers();
				$statuses 		= Status:: getStatuses();
				$sheetFormats 	= SheetFormat::getSheetFormats();
				$classifiers 	= Classifier::getAllClassifiers();
				
				$oldFileName 	= $node->fileName;
				$oldPath 		= $node->path;
				$oldNumber 		= $node->number;

				if (isset($_POST['number'])) {
					
					$node->prefix 		= trim($_POST['prefix']);
					$node->classifier 	= intval($_POST['classifier']);
					$node->number 		= Classifier::convertToLeadingZeros(intval($_POST['number']));
					$node->name 		= trim($_POST['name']);
					$node->status 		= $_POST['status'];
					$node->note 		= isset($_POST['note']) ? $_POST['note'] : '';
					$node->ownerId 		= $_POST['owner'];
					$node->quantity 	= $_POST['quantity'];
					$node->sheetFormat 	= $_POST['sheet_format'];

					$node->createFileName();
					$node->path = str_replace($oldFileName, $node->fileName, $node->path);

					if (isset($_POST['material']) && isset($_POST['quantity'])) {
						$material = Material::getMaterialByMatchingId($_POST['material']);
						$material->quantity = floatval(str_replace(",", ".", $_POST['material_quantity']));
						$node->setMaterial($material);
					}
					
					if ((strcmp($oldFileName, $node->fileName) == 0) || !Product::isNodeExists(intval($data['number']), $data['classifier'])) {
						if ($this->renamePath(WORK_FOLDER . $oldPath . $node->extension2D, WORK_FOLDER . $node->path . $node->extension2D) &&
							$this->renamePath(WORK_FOLDER . dirname($node->path) . DIRECTORY_SEPARATOR . $oldFileName . $node->extension3D, 
																							WORK_FOLDER . $node->path . $node->extension3D)) {
							if ($node->update($oldFileName)) {
								if (strcmp($oldFileName, $node->fileName) != 0)
									Classifier::increaseEmptyNumber($data['classifier']);

								if (isset($_FILES["inputfile2D"])) {
									if (is_uploaded_file($_FILES["inputfile2D"]["tmp_name"])) {
										$moveFlag = true;
										if (file_exists(WORK_FOLDER . $node->path . $node->extension2D)) {
											if (!unlink(WORK_FOLDER . $node->path . $node->extension2D)) {
												$moveFlag = false;
												$this->message[] = "Чертёж не удалось обновить, возможно он кем - то занят или у вас нет прав доступа для обновления файла.";
											}
										}	
										if ($moveFlag) {
											$this->system->move($_FILES['inputfile2D']['tmp_name'], WORK_FOLDER . $node->path . $node->extension2D);
											$this->message[] = "Чертёж успешно обновлён.";
										}
									}
									else {
										$this->massage[] = "Чертёж не был загружен.";
									}
								}

								if (isset($_FILES["inputfile3D"])) {
									if (is_uploaded_file($_FILES["inputfile3D"]["tmp_name"])) {
										$moveFlag = true;
										if (file_exists(WORK_FOLDER . $node->path . $node->extension3D)) {
											if (!unlink(WORK_FOLDER . $node->path . $node->extension3D)) {
												$moveFlag = false;
												$this->message[] = "Чертёж не удалось обновить, возможно он кем - то занят или у вас нет прав доступа для обновления файла.";
											}
										}	
										if ($moveFlag) {
											$this->system->move($_FILES['inputfile3D']['tmp_name'], WORK_FOLDER . $node->path . $node->extension3D);
											$this->message[] = "Чертёж успешно обновлён.";
										}
									}
									else {
										$this->massage[] = "Чертёж не был загружен.";
									}
								}
								
								$this->message[] = "Узел успешно обновлён.";	
							}
							else {
								$this->message[] = "Не удалось обновить, возможно потеряно соединение с сервером.";
							}
						}
						else {
							$this->message[] = "Не удалось обновить, возможно файл или папка кем-то заняты или у вас нет прав на редактирование.";
						}
					}
					else {
						$this->message[] = "Узел с таким номером уже существует!";
					}
					
					require_once(ROOT . '/views/index.php');	
					//require_once(ROOT . '/views/modules/info.php');
				} 
				else {
					require_once(ROOT . '/views/modules/updatenode.php');
				}
			} 
			catch (NodeException $e) {
				$this->message[] = $e->getMessage();
				require_once(ROOT . '/views/modules/info.php');
			}
		}

		return true;
	}

	public function actionDeleteNode(int $nodeId) : bool {
		if (User::isAdmin()) {
			try {

				$node = Node::getNodeById($nodeId);

				if ($node->delete()) {
					$success = true;

					if (file_exists(WORK_FOLDER . $node->path . $node->extension3D))
								unlink(WORK_FOLDER . $node->path . $node->extension3D);

					if (file_exists(WORK_FOLDER . $node->path . $node->extension2D)) {
						if (unlink(WORK_FOLDER . $node->path . $node->extension2D)) {

							if ($node instanceof Assembly) {

								preg_match("~[^\/\\\]+$~", dirname(PATH_TO_PROJECTS . $node->path), $matches);
								if ($matches[0] == DOCUMENTATION) {
									$this->rmrf(dirname(PATH_TO_PROJECTS . $node->path, 2));
									/*
									if (!$this->rmrf(dirname(PATH_TO_PROJECTS . $node->path, 2))) {
										$success = false;
									}	
									*/
								}
							}
						}
						else {
							$success = false;
						}
					}

					if ($success) {
						$this->message[] = "Узел успешно удалён!";	
					}
					else {
						$node->add();
						$this->message[] = "Узел не был удалён, возможно узел кем - то занят, или у вас нет прав на удаление с физического носителя.";
					}
					
				}
				else {
					$this->message[] = "Не удалось удалить узел, возможно он включает в себя другие узлы.";
				}
			}
			catch (NodeException $e) {
				$this->message[] = $e->getMessage();
			}
			require_once(ROOT . '/views/modules/info.php');
		}

		return true;
	}

	public function actionRemoveNode(int $nodeId, int $parentId) : bool {
		if (User::isAdmin()) {
			try {
				$node = Node::getNodeById($nodeId, $parentId);

				if ($node->remove()) {
					$parentNode = Node::getNodeById($parentId);

					$partition = $node->nodeType == Node::ASSEMBLY ? ASSEMBLIES : DETAILS;
					$path = WORK_FOLDER . dirname($parentNode->path, 2) . DIRECTORY_SEPARATOR . $partition . DIRECTORY_SEPARATOR . $node->fileName . ".dwg";

					if (file_exists($path)) {
						unlink($path);
					}

					$this->message[] = "Узел успешно убран!";
				}
				else
					$this->message[] = "Не удалось убрать узел, возможно он больше не входит ни в одну из сборок.";
			}
			catch (NodeException $e) {
				$this->message[] = $e->getMessage();
			}
			require_once(ROOT . '/views/modules/info.php');
		}

		return true;
	}

	public function actionCopyNode(int $nodeId) : bool {
		if (User::isAdmin()) {
			try {

				$node = Node::getNodeById($nodeId);
				$node->quantity = 1;

				if (isset($_POST['copy_node'])) {

					if ($node instanceof Assembly) {
						$copiedNodeId = intval($_POST['node_to_add']);
						$node->parentId = $copiedNodeId;

						if (!$node->isDescendant($node->id)) {

							if ($node->copy($copiedNodeId)) {
								$copiedNode = Node::getNodeById($copiedNodeId);
								$partition = $copiedNode->nodeType == Node::ASSEMBLY ? ASSEMBLIES : DETAILS;
								$path = PATH_TO_PROJECTS . dirname($node->path, 2) . DIRECTORY_SEPARATOR . $partition . DIRECTORY_SEPARATOR . $copiedNode->fileName . ".dwg";

								if (!file_exists($path)) {
									//link(PATH_TO_PROJECTS . $node->path, $path);
									//$this->createFile($path, "Копия " . PATH_TO_PROJECTS . $copiedNode->path);
								}
								
								$this->message['success'] = "Успешно скопировано!";
							}
							else {
								$this->message['program_error'] = "Не удалось скопировать!";	
							}
						}
						else {
							$this->message['user_error'] = "Недопустимо копирование элемента, который является родительским узлом!";
						}
					} 
					else {
						$this->message['user_error'] = "Добавлять можно только  в сборочную еденицу!";
					}
					require_once(ROOT . '/views/modules/info.php');
				}
				else {
					$nodes = Node::getAllnodes();
					require_once(ROOT . '/views/modules/nodelist.php');
				}		
			}
			catch (NodeException $e) {
				$this->message[] = $e->getMessage();
				require_once(ROOT . '/views/modules/info.php');
			}
		}

		return true;
	}

	public function actionOpenFile(int $nodeId) : bool {

		return true;
	}

	public function actionCopyFile(int $nodeId) : bool {
		try {
			$node = Node::getNodeById($nodeId);
			
			if ($node->exist) { 
				if (file_exists(PATH_TO_PROJECTS . $node->path)) {
										
					if (ob_get_level()) {
				    	ob_end_clean();
				    }

					header("Pragma: public"); 
					header("Expires: 0");
					header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
					header("Cache-Control: private",false); 
					header("Content-Type: application/force-download");
					header("Content-Disposition: attachment; filename=$node->fileName");
					header("Content-Transfer-Encoding: binary");
					header("Content-Length: ".filesize(PATH_TO_PROJECTS . $node->path));
					readfile(PATH_TO_PROJECTS . $node->path);
					
				}
			}
		}
		catch (NodeException $e) {
			$this->message[] = $e->getMessage();
			require_once(ROOT . '/views/modules/info.php');
		}
		return true;
	}

	public function actionUpdatePrice(int $nodeId) : bool {
		$node = Node::getNodeById($nodeId, 0, FALSE, 1);
		$node->collectPrice();
		$node->updatePrice();

		return true;
	}

	public function actionCreateSpecification(int $nodeId, int $parentId) : bool {
		try {
			if (Node::getNodeTypeById($nodeId) == Node::ASSEMBLY) {
				$specification = Specification::getInstance($nodeId, $parentId);
				$specification->create();				
			}
			else {
				echo $this->message[] = "Спецификацию возможно создать только для сборки.";
			}
		} 
		catch (NodeException $e) {
			echo $this->message[] = $e->getMessage();
		}

		return true;
	}


	/*
	 * ---------------testing--------------
	 */

	public function actionTest() : bool {

		require_once(ROOT . '/views/modules/test.php');
		return true;
	}

		

}

?>