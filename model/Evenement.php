<?php

class Evenement
{
    // ─── Propriétés ───────────────────────────────────────────
    private int    $ID;
    private string $Titre;
    private string $Type;
    private string $Description;
    private string $dateEvent;   // format Y-m-d
    private int    $duree;
    private string $lieu_lien;
    private string $Statut;
    private int    $nbplaces;

    // ─── Constructeur ─────────────────────────────────────────
    public function __construct(
        string $Titre       = '',
        string $Type        = '',
        string $Description = '',
        string $dateEvent   = '',
        int    $duree       = 0,
        string $lieu_lien   = '',
        string $Statut      = 'ouvert',
        int    $nbplaces    = 0,
        int    $ID          = 0
    ) {
        $this->ID          = $ID;
        $this->Titre       = $Titre;
        $this->Type        = $Type;
        $this->Description = $Description;
        $this->dateEvent   = $dateEvent;
        $this->duree       = $duree;
        $this->lieu_lien   = $lieu_lien;
        $this->Statut      = $Statut;
        $this->nbplaces    = $nbplaces;
    }

    // ─── Getters ──────────────────────────────────────────────
    public function getID()          : int    { return $this->ID;          }
    public function getTitre()       : string { return $this->Titre;       }
    public function getType()        : string { return $this->Type;        }
    public function getDescription() : string { return $this->Description; }
    public function getDateEvent()   : string { return $this->dateEvent;   }
    public function getDuree()       : int    { return $this->duree;       }
    public function getLieuLien()    : string { return $this->lieu_lien;   }
    public function getStatut()      : string { return $this->Statut;      }
    public function getNbplaces()    : int    { return $this->nbplaces;    }

    // ─── Setters ──────────────────────────────────────────────
    public function setID(int $v)             : void { $this->ID          = $v; }
    public function setTitre(string $v)       : void { $this->Titre       = $v; }
    public function setType(string $v)        : void { $this->Type        = $v; }
    public function setDescription(string $v) : void { $this->Description = $v; }
    public function setDateEvent(string $v)   : void { $this->dateEvent   = $v; }
    public function setDuree(int $v)          : void { $this->duree       = $v; }
    public function setLieuLien(string $v)    : void { $this->lieu_lien   = $v; }
    public function setStatut(string $v)      : void { $this->Statut      = $v; }
    public function setNbplaces(int $v)       : void { $this->nbplaces    = $v; }
}
?>
