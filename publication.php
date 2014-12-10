<?php
class hangar // Cet objet est unique par hangar, il servira à donner certaines informations spécifiques pour certains usages et à fournir la base de donnée.
{
	private $PDO;
	private $Nom;
	
	public function __construct(PDO $bdd, $nom)
	{
		$this->PDO = $bdd;
		$this->Nom = $nom;
	}
	
	public function setName($nom)
	{
		$this->Nom = $nom;
	}
	
	public function getName()
	{
		return $this->Nom;
	}
	
	public function getDB()
	{
		return $this->PDO;
	}
}
class publication // Cette classe sert à gérer les publications.
{
	private $Hangar;
	private $ID;
	private $Nom;
	private $Auteur;
	private $Categorie;
	private $Modde;
	private $Subassembly;
	private $Description;
	private $ImgExt;
	private $Request;
	private $Autorext = array('jpg','jpeg','gif','png');
	
	public function __construct(hangar $hangar, $nom, $auteur, $categorie, $modde, $subassembly, $description, $imgExt)
	{
		$this->Hangar = $hangar;
		$this->Nom = $nom;
		$this->Auteur = $auteur;
		$this->Categorie = $categorie;
		$this->Modde  = $modde;
		$this->Subassembly = $subassembly;
		$this->Description = $description;
		$this->ImgExt = $imgExt;
		}
	
	public function getDatas() // retourne un tableau contenant toutes les données de la publication.
	{
		return array(
		'ID'=>$this->ID,
		'Name'=>$this->Nom,
		'Author'=>$this->Auteur,
		'Category'=>$this->Categorie,
		'Modded'=>$this->Modde,
		'Subassembly'=>$this->Subassembly,
		'Description'=>$this->Description,
		'CraftPATH'=>$this->Categorie.'/'.$this->Nom.'.craft',
		'ImgPATH'=>$this->Categorie.'/'.$this->Nom.'.'.$this->ImgExt
		);
	}
	
	public function push($craftname, $imgname)
	{
		$this->Request = $this->Hangar->prepare('SELECT Nom FROM publications WHERE Nom = ?');
		$this->Request->execute(array($this->Nom));
		$data = $this->Request->fetch();
		$craftinfo = pathinfo($_FILES[$craftname]['name']);
		$extcraft = $craftinfo['extension'];
		$imginfo = pathinfo($_FILES[$imgname]['name']);
		$extimg = $imginfo['extension'];
		if($data['Nom'] == '' && in_array($extimg, $this->Autorext) && $extcraft == 'craft')
		{
			$this->Request->closeCursor();
			move_uploaded_file($_FILES[$craftname]['tmp_name'], 'publications/'.$this->Categorie.'/'.$this->Nom.'.craft'); // On met les fichiers ou il faut
			move_uploaded_file($_FILES[$imgname]['tmp_name'], 'publications/'.$this->Categorie.'/'.$this->Nom.'.'.$extimg);
			$this->Request = $this->Hangar->prepare("INSERT INTO publications(ID, Nom, Auteur, Categ, ImgExt, Descr, MODV, SUB) VALUES('', ? , ? , ? , ? , ? , ? , ? )"); // On ajoute tout ca dans la BDD
			$this->Request->execute(array($this->Nom,$this->Auteur,$this->Categorie,$this->ImgExt,$this->Description,$this->Modde,$this->Subassembly));
			$this->Request = $this->Hangar->prepare('SELECT * FROM publications WHERE Nom = ?');
			$this->Request->execute(array($this->Nom));
			$data2 = $this->Request->fetch();
			return $data2['ID'];
		}
		else
		{
			$this->Request->closeCursor();
			return "Error : Name already exist or the extensions don't match with autorized extensions.";
		}
	}
}
?>