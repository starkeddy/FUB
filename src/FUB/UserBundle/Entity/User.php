<?php
/**
 * Created by PhpStorm.
 * User: eddy
 * Date: 20/03/2017
 * Time: 11:18
 */
namespace FUB\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /*public function __construct()
    {
        parent::__construct();
        // your own logic
    }*/
}