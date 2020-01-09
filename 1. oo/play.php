<?php

class Ship
{
  public $name;

  public $weaponPower = 0;

  public $jediFactor = 0;

  public $strength = 0;

  public function sayHello() {
      echo 'HELLO';
  }
  public function getName() {
      return $this->name;
  }

  public function doesGivenShipHaveMoreStrength($givenShip) {
    return $givenShip->strength > $this->strength;
  }
  public function getNameAndSpecs($useShortFormats) {
    if ($useShortFormats) {
        return sprintf(
            '%s: %s, %s, %s',
            $this->name,
            $this->weaponPower,
            $this->jediFactor,
            $this->strength
        );
    } else {
        return sprintf(
            '%s: w:%s, j:%s, s:%s',
            $this->name,
            $this->weaponPower,
            $this->jediFactor,
            $this->strength
        );
    }
  }
}

function printShipSummary($someShip) {
    echo "Ship name: ".$someShip->name;
    echo '<hr/>';
    $someShip->sayHello();
    echo '<hr/>';
    echo $someShip->getName();
    echo '<hr/>';
    var_dump($someShip->weaponPower);
    echo '<hr/>';
    echo $someShip->getNameAndSpecs(false);
    echo '<hr/>';
    echo $someShip->getNameAndSpecs(true);
}
$myShip = new Ship();
$myShip->name = "Jedi Starship";
$myShip->weaponPower = 10;
$myShip->strength = 100;

$otherShip = new Ship();

$otherShip->name = "Imperial Shuttle";
$otherShip->weaponPower = 5;
$otherShip->strength = 50;

printShipSummary($myShip);
echo '<hr/>';
printShipSummary($otherShip);

echo '<hr/>';

if ($myShip->doesGivenShipHaveMoreStrength($otherShip)) {
    echo $otherShip->name." has more strength";
} else {
    echo $myShip->name." has more strength";

}