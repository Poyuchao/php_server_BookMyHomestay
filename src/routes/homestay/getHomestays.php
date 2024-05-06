<?php


// require_once ROOT . 'database/QueryBuilder.php';
// require_once ROOT . 'utils/send-response.php';
// require_once ROOT . 'utils/check-keys.php';
// require_once ROOT . 'structures/index.php';
// require_once ROOT . 'structures/fileHandle.php';
// require_once ROOT . 'utils/config.php';

// $GET_HOMES = Route::path('/homes')
//   ->setMethod('GET')
//   ->setHandler(function ($_, Database $database) {
//     $queryBuilder = QueryBuilder::create($database->connection)
//       ->select()
//       ->from('homestays');
//     if (isset($_GET['search'])) {
//       $queryBuilder->where('title', 'LIKE', '%' . $_GET['search'] . '%');
//     }

//     send_response($queryBuilder->execute());
//   })
//   ->build(); // This is the route object
