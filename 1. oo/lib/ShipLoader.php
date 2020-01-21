<?php

class ShipLoader
{
    /**
     * @return Ship[]
     */
    function getShips()
    {
        $shipsData = $this->queryForShips();
        $ships = array();

        foreach ($shipsData as $shipData) {
            $ships[] = $this->createShipFromData($shipData);
        }
        return $ships;
    }

    /**
     * @param $id
     * @return Ship|null
     */
    public function findOneById($id)
    {
        $pdo = new PDO('mysql:host=localhost;dbname=oo_battle', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $statement = $pdo->prepare('SELECT * FROM ship where id = :id');

        $statement->execute(array(
            'id' => $id
        ));

        $ship = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$ship) {
            return null;
        }

        return $this->createShipFromData($ship);
    }


    public function createShipFromData(array $shipData)
    {
        $ship = new Ship($shipData['name']);
        $ship->setId($shipData['id']);
        $ship->setWeaponPower($shipData['weapon_power']);
        $ship->setJediFactor($shipData['jedi_factor']);
        $ship->setStrength($shipData['strength']);

        return $ship;
    }

    private function queryForShips()
    {
        $pdo = new PDO('mysql:host=localhost;dbname=oo_battle', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $statement = $pdo->prepare('SELECT * FROM ship');

        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}