<?php
/**
 * Created by IntelliJ IDEA.
 * User: eddy
 * Date: 28/05/2017
 * Time: 19:51
 */

namespace FUB\GeneralBundle\Entity;



use Symfony\Component\Validator\Constraints\DateTime;

class Periode
{
    private $debut;
    private $fin;
    private $valider;

    public function __construct()
    {
        $date= new \DateTime();
        $this->fin =date_format(new \DateTime(),'Y-m-d');

        $interval=new \DateInterval('P4D');
        $interval->invert=1;
        $date->add($interval);
        $this->debut=$date->format('Y-m-d');
    }

    /**
     * @return mixed
     */
    public function getDebut()
    {
        return $this->debut;
    }

    /**
     * @param mixed $debut
     */
    public function setDebut($debut)
    {
        $this->debut = $debut;
    }

    /**
     * @return mixed
     */
    public function getFin()
    {
        return $this->fin;
    }

    /**
     * @param mixed $fin
     */
    public function setFin($fin)
    {
        $this->fin = $fin;
    }

    /**
     * @return mixed
     */
    public function getValider()
    {
        return $this->valider;
    }

    /**
     * @param mixed $valider
     */
    public function setValider($valider)
    {
        $this->valider = $valider;
    }
}