<?php

class BattleResult {
    private $usedJediPower;
    private $winningShip;
    private $losingShip;

    public function __construct($usedJediPower, Ship $winningShip = null, Ship $losingShip = null)
    {
        $this->usedJediPower =$usedJediPower;
        $this->winningShip = $winningShip;
        $this->losingShip = $losingShip;
    }

    /**
     * @return boolean
     */
    public function wereJediPowerUsed()
    {
        return $this->usedJediPower;
    }

    /**
     * @param boolean $usedJediPower
     */
    public function setUsedJediPower($usedJediPower)
    {
        $this->usedJediPower = $usedJediPower;
    }

    /**
     * @return Ship|null
     */
    public function getWinningShip()
    {
        return $this->winningShip;
    }

    /**
     * @param Ship $winningShip
     */
    public function setWinningShip($winningShip)
    {
        $this->winningShip = $winningShip;
    }

    /**
     * @return Ship|null
     */
    public function getLosingShip()
    {
        return $this->losingShip;
    }

    /**
     * @param Ship $losingShip
     */
    public function setLosingShip($losingShip)
    {
        $this->losingShip = $losingShip;
    }

    public function isThereAWinner() {
        return $this->getWinningShip() !== null;
    }
}