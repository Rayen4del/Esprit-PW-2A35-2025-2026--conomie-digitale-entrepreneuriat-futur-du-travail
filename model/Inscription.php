<?php

class Inscription
{
    // ─── Propriétés ───────────────────────────────────────────
    private int    $ID;
    private int    $IDUtilisateur;
    private int    $IDEvent;
    private string $DateInscription;   // format Y-m-d H:i:s
    private string $Statut;

    // ─── Constructeur ─────────────────────────────────────────
    public function __construct(
        int    $IDUtilisateur   = 0,
        int    $IDEvent         = 0,
        string $DateInscription = '',
        string $Statut          = 'inscrit',
        int    $ID              = 0
    ) {
        $this->ID              = $ID;
        $this->IDUtilisateur   = $IDUtilisateur;
        $this->IDEvent         = $IDEvent;
        $this->DateInscription = $DateInscription;
        $this->Statut          = $Statut;
    }

    // ─── Getters ──────────────────────────────────────────────
    public function getID()              : int    { return $this->ID;              }
    public function getIDUtilisateur()   : int    { return $this->IDUtilisateur;   }
    public function getIDEvent()         : int    { return $this->IDEvent;         }
    public function getDateInscription() : string { return $this->DateInscription; }
    public function getStatut()          : string { return $this->Statut;          }

    // ─── Setters ──────────────────────────────────────────────
    public function setID(int $v)              : void { $this->ID              = $v; }
    public function setIDUtilisateur(int $v)   : void { $this->IDUtilisateur   = $v; }
    public function setIDEvent(int $v)         : void { $this->IDEvent         = $v; }
    public function setDateInscription(string $v) : void { $this->DateInscription = $v; }
    public function setStatut(string $v)       : void { $this->Statut          = $v; }
}
?>
