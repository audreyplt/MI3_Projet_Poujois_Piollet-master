<?php


class Association extends Structure
{
    private $_nbDonnateurs;

    private function __construct(int $_id, string $_nom, string $_rue, string $_cp, string $_ville, int $_nbDonnateurs)
    {
        parent::__construct($_id, $_nom, $_rue, $_cp, $_ville);
        $this->_nbDonnateurs = $_nbDonnateurs;
    }

    public static function buildFromArray(array $item):Association
    {
        // 0:ID, 1:NOM, 2:RUE, 3:CP, 4:VILLE, 6:NB_DONATEURS
        return new Association((int)$item[0], $item[1], $item[2], $item[3], $item[4], $item[6]);
    }

    public static function buildFromData(int $_id, string $_nom, string $_rue, string $_cp, string $_ville, int $_nbDonnateurs):Association
    {
        return new Association($_id, $_nom, $_rue, $_cp, $_ville, $_nbDonnateurs);
    }


    public function getNbContributeurs(): int
    {
        return $this->_nbDonnateurs;
    }

    public function setNbContributeurs(int $nbDonnateurs): void
    {
        $this->_nbDonnateurs = $nbDonnateurs;
    }

    public function estAsso() : int
    {
        return 1;
    }


}