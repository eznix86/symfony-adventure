<?php

class Ship
{
  public $name;
}

$myShip = new Ship();
$myShip->name = "Jedi Starship";
echo "Ship name: ".$myShip->name;
