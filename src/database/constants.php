<?php

define('QUERY_BUILDER_SORT_ASC', 'ASC');
define('QUERY_BUILDER_SORT_DESC', 'DESC');

define('QUERY_BUILDER_JOIN_INNER', 'INNER JOIN');
define('QUERY_BUILDER_JOIN_LEFT', 'LEFT JOIN');
define('QUERY_BUILDER_JOIN_RIGHT', 'RIGHT JOIN');
define('QUERY_BUILDER_JOIN_FULL', 'FULL JOIN');

define("DB_SERVER_NAME", "host.docker.internal");
define("DB_USER_NAME", "root");
define("DB_PASSWORD", "root");
define("DB_NAME", "bookmyhomestay");

define('QUERY_BUILDER_AND_WHERE', 'AND');
define('QUERY_BUILDER_OR_WHERE', 'OR');


const ALLOWED_OPERATORS = ['=', '!=', '>', '<', '>=', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN'];
