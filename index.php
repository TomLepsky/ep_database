<?php
$start = microtime(true);

ini_set('display_errors', 1);
ini_set('allow_url_fopen', 1);
error_reporting(E_ALL);

define('ROOT', dirname(__FILE__));

require_once(ROOT . '/config/constants.php');

session_start();

require_once(ROOT . '/components/vendor/autoload.php');
require_once(ROOT . '/components/Autoloader.php');
Autoloader::run();

require_once(ROOT . '/components/DB.php');

$router = new Router();
$router->run();
   


/*
$db = DB::getConnection();
$i = 600155;
for ($j = 100144; $j < 100155; $j++) {
    for ($k = 0; $k < 100; $k++) {
        $db->query("insert INTO `matching_nodes`(`parent_id`, `child_id`, `child_count`) VALUES ($j, $i, 1)");
        $i++;
    }
}
*/

$time = round(microtime(true) - $start, 4);

Logger::consoleLog($time, "Execute time:");

/*

*/
//$db->query("insert INTO `matching_details`(`parent_id`, `child_id`, `child_count`) VALUES (6, $i, 1)");
//
// $db->query("iNSERT INTO `nodes`(`prefix`, `number`, `name`, `suffix`, `node_type`, `status`, `note`, `user_id`,`sheet_format`) VALUES (
//                                    'КУИЖ', '002.$i', 'деталь', '', 30, 0, '', 1, 0)");
//
// $db->query("insert INTO `materials`(`name`, `measure`) VALUES ('material', 'kg')");
// 
//  $db->query("insert INTO `matching_materials_nodes`(`node_id`, `match_id`, `quantity`) VALUES ($i, 30, 1)");
// 
// 
// 
/*
WITH RECURSIVE get_nodes AS (
        SELECT child_id, child_count, parent_id, 0 AS lvl FROM matching_details WHERE child_id = 6
        UNION ALL
        SELECT t.child_id, t.child_count, t.parent_id, get_nodes.lvl + 1 FROM matching_details t JOIN get_nodes
        ON get_nodes.child_id = t.parent_id
    )

SELECT 
    t1.id AS node_id, t1.prefix, t1.number, t1.name, t1.suffix, t1.path, t1.node AS node_type, t1.child, t1.status, t1.note, t1.price AS detail_price,
    t2.quantity AS material_quantity,                    
    t3.price, 
    t4.id as material_id, t4.name AS material_name, t4.measure,
    t5.name as producer_name,
    t6.parent_id, t6.child_count AS quantity, t6.lvl

FROM get_nodes t6 
    LEFT JOIN details t1                    ON t6.child_id      = t1.id
    LEFT JOIN matching_materialsdetails t2  ON t1.id            = t2.detail_id
    LEFT JOIN matching_materials t3         ON t2.match_id      = t3.id
    LEFT JOIN materials t4                  ON t3.material_id   = t4.id
    LEFT JOIN producers t5                  ON t3.producer_id   = t5.id

ORDER BY t6.lvl
    */
   
//UPDATE `users` SET `password`= md5('123') WHERE id = 2
?>