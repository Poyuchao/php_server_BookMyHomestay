<?php

require_once ROOT . 'database/QueryBuilder.php';
require_once ROOT . 'database/index.php';

function getHomestayExtras(Database $database, array $homestayList): array
{
  for ($i = 0; $i < count($homestayList); $i++) {
    $homestayList[$i]['images'] = QueryBuilder::create($database->connection)
      ->select()
      ->from('homestay_images')
      ->where('homestay_id', '=', $homestayList[$i]['id'])
      ->execute();

    $homestayAmenities = QueryBuilder::create($database->connection)
      ->select(['amenities.name'])
      ->from('homestays_amenities')
      ->where('homestay_id', '=', $homestayList[$i]['id'])
      ->innerJoin('amenities', 'amenities.id', 'homestays_amenities.amenities_id')
      ->execute();

    $homestayList[$i]['amenities'] = array_map(function ($amenity) {
      return $amenity['name'];
    }, $homestayAmenities);
  }

  return $homestayList;
}
