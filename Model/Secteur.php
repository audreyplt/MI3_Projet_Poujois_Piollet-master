<?php


class Secteur
{
    private $_id;
    private $_nom;

    //Penser à voir pour l'id
    private function __construct(int $_id,string $_nom)
    {
        $this->_id = $_id;
        $this->_nom = $_nom;
    }

    public static function buildFromArray(array $_item)
    {
        return new Secteur($_item[0], $_item[1]);
    }

    public static function buildFromData(int $_id, string $_nom)
    {
        return new Secteur($_id, $_nom);
    }

    public function getId(): int
    {
        return $this->_id;
    }

    public function setId(int $id): void
    {
        $this->_id = $id;
    }

    public function getNom(): string
    {
        return $this->_nom;
    }

    public function setNom(string $nom): void
    {
        $this->_nom = $nom;
    }



}