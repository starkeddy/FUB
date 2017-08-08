<?php
/**
 * Created by IntelliJ IDEA.
 * User: root
 * Date: 4/13/17
 * Time: 9:11 PM
 */

namespace FUB\GeneralBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="uploaded")
 */
class Upload{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     * @Assert\NotBlank(message="Please, upload the product brochure as a PDF file.")
     * @Assert\File(mimeTypes={ "application/pdf" })
     */
    private $file;
    
    private $upload;

    public function __construct(){

    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }

    /**
     * @return mixed
     */
    public function getUpload()
    {
        return $this->upload;
    }

    /**
     * @param mixed $upload
     */
    public function setUpload($upload)
    {
        $this->upload = $upload;
    }
}