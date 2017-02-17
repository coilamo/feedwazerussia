<?php

function filter(&$value) {
  $value = htmlspecialchars($value, ENT_COMPAT, 'UTF-8');
}

?>
