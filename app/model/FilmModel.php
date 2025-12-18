<?php

class FilmModel
{
    private $bdd;
    private PDOStatement $addFilm;
    private PDOStatement $delFilm;
    private PDOStatement $getFilms;
    private PDOStatement $getFilm;
    private PDOStatement $editfilm;

   function __construct()
{
    // Correction 1 : Ajout de la gestion des erreurs PDO
    $this->bdd = new PDO("mysql:host=bdd;dbname=app-database","root","root", array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
    
    // Création d'une requête préparée qui récupère tout les films avec LIMIT pour pagination
    $this->getFilms = $this->bdd->prepare("SELECT * FROM `Film` LIMIT :limit");
    
    // Correction 2 : Utilisation d'un marqueur nommé (:id)
    $this->getFilm = $this->bdd->prepare("SELECT * FROM `Film` WHERE id = :id");
    
    // Correction 3 : Ajout d'un film (Cohérence avec FilmEntity)
    $this->addFilm = $this->bdd->prepare("INSERT INTO `Film` (nom, date, genre, auteur) VALUES (:nom, :date, :genre, :auteur)");
    
    // Correction 4 : Supprime par id (en supposant que la colonne est 'id' et non 'id_Film')
    $this->delFilm = $this->bdd->prepare("DELETE FROM `Film` WHERE id = :id");
    
    // Correction 5 : Modification d'un film (Cohérence avec FilmEntity)
    $this->editfilm = $this->bdd->prepare("UPDATE `Film` SET nom = :nom, date = :date, genre = :genre, auteur = :auteur WHERE id = :id");
}


   /**
     * Récupérer tout les films
     * return array : Renvoi un array de FilmEntity
     * param int $limit : défini le nombre maximum d'Entity renvoyée, par défaut 50.
     * */
    public function getAll(int $limit = 50) : array
    {
        $this->getFilms->bindValue("limit",$limit,PDO::PARAM_INT);
        $this->getFilms->execute();
        $rawfilms = $this->getFilms->fetchAll();
        
        $filmsEntity = [];
        foreach($rawfilms as $rawfilm){
            // Correction 6 : Utilisation des colonnes de FilmEntity
            $filmsEntity[] = new FilmEntity(
                $rawfilm["nom"],
                new DateTime($rawfilm["date"]), // Nécessite la conversion en DateTime
                $rawfilm["genre"],
                $rawfilm["auteur"],
                $rawfilm["id"]
            );
        }
        
        return $filmsEntity;
    }

    /**
     * Recupérer un film via son id.
     * return Une FilmEntity ou NULL si aucune ne correspond à l'$id
     * param int id : la clé primaire de l'entity demandée.
     * */
    public function get(int $id): FilmEntity | NULL
    {
        // Correction 7 : Utilisation du marqueur nommé :id
        $this->getFilm->bindValue("id",$id,PDO::PARAM_INT);
        $this->getFilm->execute(); 
        $rawFilm = $this->getFilm->fetch(PDO::FETCH_ASSOC); // Utilisation de FETCH_ASSOC pour plus de clarté
        
        if(!$rawFilm){
            return NULL;
        }
        
        // Correction 8 : Utilisation des colonnes de FilmEntity
        return new FilmEntity(
            $rawFilm["nom"],
            new DateTime($rawFilm["date"]),
            $rawFilm["genre"],
            $rawFilm["auteur"],
            $rawFilm["id"]);
    }

    /**
     * Ajouter un film
     * return void : ne renvoi rien
     * param les informations de l'entity
     * */
    public function add(string $nom, DateTime $date, string $genre, string $auteur) : void
    {   
        // Correction 9 : Utilisation des marqueurs nommés
        $this->addFilm->bindValue("nom",$nom);
        $this->addFilm->bindValue("date",$date->format('Y-m-d H:i:s')); // Conversion de DateTime en string SQL
        $this->addFilm->bindValue("genre",$genre);
        $this->addFilm->bindValue("auteur",$auteur);
        $this->addFilm->execute();
    }

    /**
     * Supprime un film via son id
     * return void : ne renvoi rien
     * param int $id : la clé primaire de l'entité à supprimer
     * */
    public function del(int $id) : void
    {
        $this->delFilm->bindValue("id",$id,PDO::PARAM_INT); // Correction : Utilisation de :id
        $this->delFilm->execute();
    }

    /**
     * Modifier un film
     * return FilmEntity ou NULL : Le film modifié après modification ou NULL si l'id n'existe pas.
     * param int $id l'identifiant du produit, ce paramètre ne défini pas la nouvelle valeur de l'id car un id SQL est immuable, mais permet de définir quelle produit modifier.
     * */
    public function edit(int $id,string $nom = NULL,
    DateTime $date = NULL, string $genre = NULL, string $auteur = NULL) : FilmEntity | NULL
    {
          $originalProduct = $this->get($id);
          if(!$originalProduct){
            return NULL;
        }

        // --- Détermination des valeurs à mettre à jour ---
        $nom_maj = $nom ?? $originalProduct->getNom();
        $date_maj = $date ?? $originalProduct->getDate();
        $genre_maj = $genre ?? $originalProduct->getGenre();
        $auteur_maj = $auteur ?? $originalProduct->getAuteur();

        // --- Liaison des valeurs ---
        $this->editfilm->bindValue("nom", $nom_maj);
        $this->editfilm->bindValue("date", $date_maj->format('Y-m-d H:i:s'));
        $this->editfilm->bindValue("genre", $genre_maj);
        $this->editfilm->bindValue("auteur", $auteur_maj);
        $this->editfilm->bindValue("id",$id,PDO::PARAM_INT);

        $this->editfilm->execute();

        return $this->get($id);
    }
}

// ... FilmEntity (aucune correction requise, la logique est correcte)
class FilmEntity
{

    private $nom;
    private $date_de_sortie;
    private $genre;

    private $auteur;
    private $id;

    //getter
    public function getNom(): string
    {
        return $this->nom;
    }
    public function getDate(): DateTime
    {
        return $this->date_de_sortie;
    }
    public function getGenre(): string
    {
        return $this->genre;
    }

    public function getAuteur(): string
    {
        return $this->auteur;
    }
    public function getId(): int
    {
        return $this->id;
    }


    private const NOM_MIN_LENGTH = 3;
    private const DATE_MIN = 0;
    private const GENRE_MIN_LENGTH = 3;

    private const AUTEUR_MIN_LENGTH = 3;

    //setter
    public function setNom(string $nom)
    {
        if (strlen($nom) < $this::NOM_MIN_LENGTH) {
            throw new Error("Le nom est trop court" . $this::NOM_MIN_LENGTH);
        }
        $this->nom = $nom;
    }
    public function setDate(DateTime $date)
    {
        if ($date < 0) {
            throw new Error("Pas la bonne date" . $this::DATE_MIN);
        }
        $this->date_de_sortie = $date;
    }
    public function setGenre(string $genre)
    {
        if (strlen($genre) <= 0) {
            $this->genre = $this::GENRE_MIN_LENGTH;
        }
        $this->genre = $genre;
    }

     public function setAuteur(string $auteur)
    {
        if (strlen($auteur) <= 0) {
            $this->auteur = $this::AUTEUR_MIN_LENGTH;
        }
        $this->auteur = $auteur;
    }
    //constructor
    function __construct(string $nom, DateTime $date, string $genre, string $auteur, int $id = NULL)
    {
        $this->setNom($nom);
        $this->setDate($date);
        $this->setGenre($genre);
        $this->setAuteur($auteur);
        $this->id = $id;
    }
}
