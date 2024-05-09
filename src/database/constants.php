<?php

define('QUERY_BUILDER_SORT_ASC', 'ASC');
define('QUERY_BUILDER_SORT_DESC', 'DESC');

define('QUERY_BUILDER_JOIN_INNER', 'INNER JOIN');
define('QUERY_BUILDER_JOIN_LEFT', 'LEFT JOIN');
define('QUERY_BUILDER_JOIN_RIGHT', 'RIGHT JOIN');
define('QUERY_BUILDER_JOIN_FULL', 'FULL JOIN');
date_default_timezone_set("America/Vancouver");

define("DB_SERVER_NAME", "localhost");
define("DB_USER_NAME", "phpAgent");
define("DB_PASSWORD", "test1234");
define("DB_NAME", "bookmyhomestay");

define('QUERY_BUILDER_AND_WHERE', 'AND');
define('QUERY_BUILDER_OR_WHERE', 'OR');


const ALLOWED_OPERATORS = ['=', '!=', '>', '<', '>=', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN'];
