<?php

class AjaxQueryController extends Controller {
	
	public function actionGetClassifierEmptyNumber() : bool {
		echo Classifier::getEmptyNumber(intval($_POST['classifier']));

		exit();
	}

}

?>