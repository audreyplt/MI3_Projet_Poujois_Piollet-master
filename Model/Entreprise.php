<?php


class Entreprise extends Structure
{

    private $_nbActionnaires;

    private function __construct(int $_id, string $_nom, string $_rue, string $_cp, string $_ville, int $_nbActionnaires)
    {
        parent::__construct($_id, $_nom, $_rue, $_cp, $_ville);
        $this->_nbActionnaires = $_nbActionnaires;
    }

    public static function buildFromArray(array $item): Entreprise
    {
        // 0:ID, 1:NOM, 2:RUE, 3:CP, 4:VILLE, 7:NB_ACTIONNAIRES
        return new Entreprise((int)$item[0], $item[1], $item[2], $item[3], $item[4], $item[7]);
    }

    public static function buildFromData(int $_id, string $_nom, string $_rue, string $_cp, string $_ville, int $_nbActionnaires): Entreprise
    {
        return new Entreprise($_id, $_nom, $_rue, $_cp, $_ville, $_nbActionnaires);
    }


    public function getNbContributeurs(): int
    {
        return $this->_nbActionnaires;
    }

    public function setNbContributeurs(int $nbActionnaires): void
    {
        $this->_nbActionnaires = $nbActionnaires;
    }


    public function estAsso(): int
    {
        return 0;
    }

}