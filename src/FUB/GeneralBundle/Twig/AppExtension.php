<?php
/**
 * Created by IntelliJ IDEA.
 * User: root
 * Date: 4/18/17
 * Time: 12:27 PM
 */
namespace FUB\GeneralBundle\Twig;
use Doctrine\ORM\Mapping;
use Symfony\Component\HttpKernel\Tests\Controller;

class AppExtension extends \Twig_Extension
{
    protected $dbs;

    public function __construct($em)
    {
        $this->dbs = $em;
    }
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('nombreDemande', array($this, 'getNombreDemande')),
            new \Twig_SimpleFilter('nombreInvestisseur', array($this, 'getNombreInvestisseur')),
        );
    }

    public function getNombreDemande(){
        $base='fubmad';
        $sql='
        select
            sum(nb) nb_demande
        from 
        (select count(*)nb from '.$base.'.adhesion where flag=0
        union all
        select count(*)nb from '.$base.'.uploaded) res';

        $stmt=$this->dbs->getConnection()->prepare($sql);
        $stmt->execute();

        $res=0;
        foreach($stmt->fetchAll() as $value) {
            $res= $value['nb_demande'];
        }

        return $res;
    }

    public function getNombreInvestisseur(){
        $base='fubmad';
        $sql='
        select count(*)nb from '.$base.'.adhesion where flag=1
        ';

        $stmt=$this->dbs->getConnection()->prepare($sql);
        $stmt->execute();

        $res=0;
        foreach($stmt->fetchAll() as $value) {
            $res= $value['nb'];
        }

        return $res;
    }
}