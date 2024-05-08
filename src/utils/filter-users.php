<?php

function filtersUser($user)
{
  // Remove the password from the user object
  unset($user['pass']);
  return $user;
}
