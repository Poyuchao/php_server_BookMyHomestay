<?php

function filtersUser($user)
{
  unset($user['password']);
  return $user;
}
