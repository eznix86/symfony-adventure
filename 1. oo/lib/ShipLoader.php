<?php

class ShipLoader {
    function getShips()
    {
        $ships = array();

        $ship = new Ship('Jedi Starfighter');
        $ship->setWeaponPower(5);
        $ship->setJediFactor(15) ;
        $ship->setStrength(30);

        $ship2 = new Ship('CloakShape Fighter');
        $ship2->setWeaponPower(2);
        $ship2->setJediFactor(2) ;
        $ship2->setStrength(70);

        $ship3 = new Ship('Super Star Destroyer');
        $ship3->setWeaponPower(70);
        $ship3->setJediFactor(0) ;
        $ship3->setStrength(500);

        $ship4 = new Ship('RZ-1 A-wing interceptor');
        $ship4->setWeaponPower(4);
        $ship4->setJediFactor(4) ;
        $ship4->setStrength(50);

        $ships['starfighter'] = $ship;
        $ships['cloakshape_fighter'] = $ship2;
        $ships['super_star_destroyer'] = $ship3;
        $ships['rz1_a_wing_interceptor'] = $ship4;

        return $ships;
    }
}