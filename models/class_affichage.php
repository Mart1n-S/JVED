<?php

class Affichage
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getTopTopicsAccueil()
    {
        return $this->db->getTopTopics();
    }
}
