<?php
/**
 * Created by IntelliJ IDEA.
 * User: root
 * Date: 4/1/17
 * Time: 9:39 PM
 */

namespace FUB\GeneralBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="adhesion")
 */
class Adhesion
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="Please, upload the product brochure as a jpeg file.")
     * @Assert\File(mimeTypes={ "image/jpeg","image/png"})
     */
    private $picture;
    /**
     * @ORM\Column(type="string")
     *
     */

    private $nom;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $prenom;
    /**
     * @ORM\Column(type="datetime")
     *
     */
    private $dateNaissance;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $perenom;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $merenom;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $nationalite;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $profession;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $adresse;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $ville;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $country;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $contact;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $mail;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $montantChiffre;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $montantLettre;
    /**
     * @ORM\Column(type="datetime")
     *
     */
    private $dateContrat;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $dureeContrat;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $heritieNomPrenom;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $heritierContact;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $heritierMail;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $heritierAdresse;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $sondageFub;
    /**
     * @ORM\Column(type="string")
     *
     */
    private $motivationFub;
    /**
     * @ORM\Column(type="boolean", options={"default":true})
     *
     */
    private $flag;

    /**
     * @return mixed
     */
    public function getFlag()
    {
        return $this->flag;
    }

    /**
     * @param mixed $flag
     */
    public function setFlag($flag)
    {
        $this->flag = $flag;
    }

    public function __construct(){

    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    

    /**
     * @return mixed
     */
    public function getPicture()
    {
        return $this->picture;
    }

    /**
     * @param mixed $picture
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    /**
     * @return \DateTime
     */
    public function getDateDemande()
    {
        return $this->dateDemande;
    }

    /**
     * @return mixed
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @return mixed
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * @return mixed
     */
    public function getDateNaissance()
    {
        return $this->dateNaissance;
    }

    /**
     * @return mixed
     */
    public function getPerenom()
    {
        return $this->perenom;
    }

    /**
     * @return mixed
     */
    public function getMerenom()
    {
        return $this->merenom;
    }

    /**
     * @return mixed
     */
    public function getNationalite()
    {
        return $this->nationalite;
    }

    /**
     * @return mixed
     */
    public function getProfession()
    {
        return $this->profession;
    }

    /**
     * @return mixed
     */
    public function getAdresse()
    {
        return $this->adresse;
    }

    /**
     * @return mixed
     */
    public function getVille()
    {
        return $this->ville;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return mixed
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @return mixed
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @return mixed
     */
    public function getMontantChiffre()
    {
        return $this->montantChiffre;
    }

    /**
     * @return mixed
     */
    public function getMontantLettre()
    {
        return $this->montantLettre;
    }

    /**
     * @return mixed
     */
    public function getDateContrat()
    {
        return $this->dateContrat;
    }

    /**
     * @return mixed
     */
    public function getDureeContrat()
    {
        return $this->dureeContrat;
    }

    /**
     * @return mixed
     */
    public function getHeritieNomPrenom()
    {
        return $this->heritieNomPrenom;
    }

    /**
     * @return mixed
     */
    public function getHeritierContact()
    {
        return $this->heritierContact;
    }

    /**
     * @return mixed
     */
    public function getHeritierMail()
    {
        return $this->heritierMail;
    }

    /**
     * @return mixed
     */
    public function getHeritierAdresse()
    {
        return $this->heritierAdresse;
    }

    /**
     * @return mixed
     */
    public function getSondageFub()
    {
        return $this->sondageFub;
    }

    /**
     * @return mixed
     */
    public function getMotivationFub()
    {
        return $this->motivationFub;
    }

    /**
     * @param mixed $prenom
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;
    }

    /**
     * @param mixed $dateDemande
     */
    public function setDateDemande($dateDemande)
    {
        $this->dateDemande = $dateDemande;
    }

    /**
     * @param mixed $nom
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
    }

    /**
     * @param mixed $dateNaissance
     */
    public function setDateNaissance($dateNaissance)
    {
        $this->dateNaissance = $dateNaissance;
    }

    /**
     * @param mixed $perenom
     */
    public function setPerenom($perenom)
    {
        $this->perenom = $perenom;
    }

    /**
     * @param mixed $merenom
     */
    public function setMerenom($merenom)
    {
        $this->merenom = $merenom;
    }

    /**
     * @param mixed $nationalite
     */
    public function setNationalite($nationalite)
    {
        $this->nationalite = $nationalite;
    }

    /**
     * @param mixed $profession
     */
    public function setProfession($profession)
    {
        $this->profession = $profession;
    }

    /**
     * @param mixed $adresse
     */
    public function setAdresse($adresse)
    {
        $this->adresse = $adresse;
    }

    /**
     * @param mixed $ville
     */
    public function setVille($ville)
    {
        $this->ville = $ville;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
    }

    /**
     * @param mixed $contact
     */
    public function setContact($contact)
    {
        $this->contact = $contact;
    }

    /**
     * @param mixed $mail
     */
    public function setMail($mail)
    {
        $this->mail = $mail;
    }

    /**
     * @param mixed $montantChiffre
     */
    public function setMontantChiffre($montantChiffre)
    {
        $this->montantChiffre = $montantChiffre;
    }

    /**
     * @param mixed $montantLettre
     */
    public function setMontantLettre($montantLettre)
    {
        $this->montantLettre = $montantLettre;
    }

    /**
     * @param mixed $dateContrat
     */
    public function setDateContrat($dateContrat)
    {
        $this->dateContrat = $dateContrat;
    }

    /**
     * @param mixed $dureeContrat
     */
    public function setDureeContrat($dureeContrat)
    {
        $this->dureeContrat = $dureeContrat;
    }

    /**
     * @param mixed $heritieNomPrenom
     */
    public function setHeritieNomPrenom($heritieNomPrenom)
    {
        $this->heritieNomPrenom = $heritieNomPrenom;
    }

    /**
     * @param mixed $heritierContact
     */
    public function setHeritierContact($heritierContact)
    {
        $this->heritierContact = $heritierContact;
    }

    /**
     * @param mixed $heritierMail
     */
    public function setHeritierMail($heritierMail)
    {
        $this->heritierMail = $heritierMail;
    }

    /**
     * @param mixed $heritierAdresse
     */
    public function setHeritierAdresse($heritierAdresse)
    {
        $this->heritierAdresse = $heritierAdresse;
    }

    /**
     * @param mixed $sondageFub
     */
    public function setSondageFub($sondageFub)
    {
        $this->sondageFub = $sondageFub;
    }

    /**
     * @param mixed $motivationFub
     */
    public function setMotivationFub($motivationFub)
    {
        $this->motivationFub = $motivationFub;
    }
}