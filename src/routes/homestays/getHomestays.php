<?php


require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'utils/send-response.php';
require_once ROOT . 'utils/check-keys.php';
require_once ROOT . 'structures/index.php';
require_once ROOT . 'structures/fileHandle.php';
require_once ROOT . 'utils/config.php';
require_once ROOT . 'utils/parse-order.php';
require_once ROOT . 'utils/get-homestay-extras.php';

$GET_HOMES = Route::path('/homes')
  ->setMethod('GET')
  ->setHandler(function ($_, Database $database) {
    $orderBy = parseOrder($_GET['order'], ['price', 'rating', 'createdAt'], 'price_ASC');

    $queryBuilder = QueryBuilder::create($database->connection)
      ->select()
      ->from('homestays');

    if (isset($_GET['search'])) {
      $queryBuilder->where('title', 'LIKE', '%' . $_GET['search'] . '%')
        ->orWhere('desc', 'LIKE', '%' . $_GET['search'] . '%');
    }

    if (isset($_SESSION['user'])) {
      if ($_SESSION['user']['vegetarian']) {
        $queryBuilder->where('vegetarian_friendly', '=', true);
      }

      $queryBuilder->where('location', 'LIKE', '%' . $_SESSION['user']['location'] . '%')
        ->where('price_per_month', '<=', $_SESSION['user']['budget']);
    }

    $orderColumn = '';

    switch ($orderBy['column']) {
      case 'price':
        $orderColumn = 'price_per_month';
        break;
      case 'rating':
        $orderColumn = 'rating';
        break;
      case 'createdAt':
        $orderColumn = 'created_at';
        break;
    }

    $queryBuilder->orderBy($orderColumn, $orderBy['direction']);

    $homestays = $queryBuilder->execute();

    $homestays = getHomestayExtras($database, $homestays);

    send_response($homestays, 200);
  })
  ->build(); // This is the route object
