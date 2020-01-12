<?php

return array(
	'showtree' 							=> 'assynch/showTree',
	'node/([\d]+)/([\d]+)/([\d])'		=> 'assynch/showNode/$1/$2/$3',
	'addnewnode/([\d]+)/([\d]+)' 		=> 'assynch/addNewNode/$1/$2',
	'updatenode/([\d]+)/([\d]+)'		=> 'assynch/updateNode/$1/$2',
	'deletenode/([\d]+)' 				=> 'assynch/deleteNode/$1',
	'removenode/([\d]+)/([\d]+)'		=> 'assynch/removeNode/$1/$2',
	'copynode/([\d]+)'					=> 'assynch/copyNode/$1',
	'copyfile/([\d]+)' 					=> 'assynch/copyFile/$1',
	'updateprice/([\d]+)' 				=> 'assynch/updatePrice/$1',
	'specification/([\d]+)/([\d]+)'		=> 'assynch/createSpecification/$1/$2',

	'test' 								=> 'assynch/test',

	'classifiernumber'					=> 'ajaxQuery/getClassifierEmptyNumber',

	'exit' 								=> 'site/exit',
	'login' 							=> 'site/enter',
	'.+' 								=> 'site/index',
	'' 									=> 'site/index'
);

?>