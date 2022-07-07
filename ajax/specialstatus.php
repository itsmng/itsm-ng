<?php


include ('../inc/includes.php');

if (isset($_GET["status"])) {
   SpecialStatus::deleteStatus($_GET["id"]);
}
