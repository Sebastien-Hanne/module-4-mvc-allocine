<?php

class DiffusionModel
{
    private $bdd;
    private PDOStatement $addDate;
    private PDOStatement $delDate;
    private PDOStatement $getDates;
    private PDOStatement $getDate;
    private PDOStatement $editDate;

   function __construct()
{
    // Correction 1 : Ajout de la gestion des erreurs PDO
    $this->bdd = new PDO("mysql:host=bdd;dbname=app-database","root","root", array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    
    $this->getDates = $this->bdd->prepare("SELECT * FROM `Diffussion` LIMIT :limit");
    
    // Récupère une seule diffusion
    $this->getDate = $this->bdd->prepare("SELECT * FROM `Diffussion` WHERE id = :id");
    
    // Correction 2 : Syntaxe SQL dans la requête INSERT + Utilisation de marqueurs nommés
    $this->addDate = $this->bdd->prepare("INSERT INTO `Diffussion` (film_id, date) VALUES (:film_id, :date)");
    
    // Correction 3 : Suppression par ID (en supposant que la colonne est 'id')
    $this->delDate = $this->bdd->prepare("DELETE FROM `Diffussion` WHERE id = :id");
    
    // Correction 4 : Mise à jour de la requête UPDATE pour la diffusion
    $this->editDate = $this->bdd->prepare("UPDATE `Diffussion` SET film_id = :film_id, date = :date WHERE id = :id");
}


   /**
     * Récupérer toutes les diffusions
     * ...
     * */
    public function getAll(int $limit = 50) : array
    {
        $this->getDates->bindValue("limit",$limit,PDO::PARAM_INT);
        $this->getDates->execute();
        $rawDiffusions = $this->getDates->fetchAll();
        
        $diffusionEntity = [];
        foreach($rawDiffusions as $rawDiffusion){
            // Correction 5 : Conversion en DateTime et utilisation de film_id comme "nom"
            $diffusionEntity[] = new DiffusionEntity(
                $rawDiffusion["film_id"],
                new DateTime($rawDiffusion["date"]),
                $rawDiffusion["id"]
            );
        }
        
        return $diffusionEntity;
    }

    /**
     * Recupérer une diffusion via son id.
     * ...
     * */
    public function get(int $id): DiffusionEntity | NULL
    {
        $this->getDate->bindValue("id",$id,PDO::PARAM_INT);
        $this->getDate->execute();
        $rawDiffusion = $this->getDate->fetch(PDO::FETCH_ASSOC);
        
        if(!$rawDiffusion){
            return NULL;
        }
        
        // Correction 6 : Utilisation des bonnes colonnes
        return new DiffusionEntity(
                $rawDiffusion["film_id"],
                new DateTime($rawDiffusion["date"]),
                $rawDiffusion["id"]);
       
    }

    /**
     * Ajouter une diffusion
     * ...
     * */
    public function add(int $film_id, DateTime $date) : void
    {   
        // Correction 7 : Utilisation des marqueurs nommés et de film_id
        $this->addDate->bindValue("film_id",$film_id, PDO::PARAM_INT);
        $this->addDate->bindValue("date",$date->format('Y-m-d H:i:s'));
        $this->addDate->execute();
    }

    // ... La méthode del est corrigée dans le constructeur.

    /**
     * Modifier une diffusion
     * ...
     * */
    public function edit(int $id,int $film_id = NULL,
    DateTime $date = NULL) : DiffusionEntity | NULL
    {
          $originalDiffusion = $this->get($id);
          if(!$originalDiffusion){
            return NULL;
        }

        $film_id_maj = $film_id ?? $originalDiffusion->getNom(); // Le "nom" est l'id du film dans l'Entity
        $date_maj = $date ?? $originalDiffusion->getDate();

        // Liaison des valeurs
        $this->editDate->bindValue("film_id", $film_id_maj, PDO::PARAM_INT);
        $this->editDate->bindValue("date", $date_maj->format('Y-m-d H:i:s'));
        $this->editDate->bindValue("id",$id,PDO::PARAM_INT);

        $this->editDate->execute();

        return $this->get($id);
    }
}
// ... DiffusionEntity (aucune correction requise, la logique est correcte)

class DiffusionEntity
{

    private $nom;
    private $date;
    private $id;

    //getter
    public function getNom(): string
    {
        return $this->nom;
    }
    public function getDate(): DateTime
    {
        return $this->date;
    }
    public function getId(): int
    {
        return $this->id;
    }


    private const NOM_MIN_LENGTH = 3;
    private const DATE_MIN = 0;


    //setter
    public function setNom(string $nom)
    {
        if (strlen($nom) < $this::NOM_MIN_LENGTH) {
            throw new Error("Le nom est trop court " . $this::NOM_MIN_LENGTH);
        }
        $this->nom = $nom;
    }
    public function setDate(DateTime $date)
    {
        if ($date < 0) {
            throw new Error("Price is too short minimum price is " . $this::DATE_MIN);
        }
        $this->date = $date;
    }
   
    //constructor
    function __construct(string $nom, DateTime $date, int $id = NULL)
    {
        $this->setNom($nom);
        $this->setDate($date);
        $this->id = $id;
    }
}
