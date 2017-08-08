<?php
/**
 * Created by IntelliJ IDEA.
 * User: root
 * Date: 4/18/17
 * Time: 2:40 PM
 */

namespace FUB\GeneralBundle\Entity;

namespace FUB\GeneralBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="annonces")
 */
class Annonces
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $upd_dt;

    /**
     * @ORM\Column(type="string")
     */
    private $contenu;

    /**
     * @ORM\Column(type="string")
     */
    private $status;


    private $create;


    /**
     * @return mixed
     */
    public function getCreate()
    {
        return $this->create;
    }

    /**
     * @return mixed
     */
    public function getUpdDt()
    {
        return $this->upd_dt;
    }

    /**
     * @param mixed $upd_dt
     */
    public function setUpdDt($upd_dt)
    {
        $this->upd_dt = $upd_dt;
    }

    /**
     * @return mixed
     */
    public function getContenu()
    {
        return $this->contenu;
    }

    /**
     * @param mixed $contenu
     */
    public function setContenu($contenu)
    {
        $this->contenu = $contenu;
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
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

}