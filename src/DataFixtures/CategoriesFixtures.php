<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoriesFixtures extends Fixture
{   
    private $count = 1;
    public function __construct(private SluggerInterface $slugger)
    {
        
    }

    public function load(ObjectManager $manager): void
    {
        $biere = $this->createCategory('Bière', null, $manager);
        $this->createCategory('Ambrée', $biere, $manager);
        $this->createCategory('Blanche', $biere, $manager);
        $this->createCategory('Blonde', $biere, $manager);
        $this->createCategory('Brune', $biere, $manager);
    
        $manager->flush();
    }
    
    public function createCategory(string $name, Categorie $parent = null, ObjectManager $manager)
    {
        $category = new Categorie();       
        $category->setName($name);
        $category->setSlug($this->slugger->slug($category->getName())->lower());
        $category->setParent($parent);
        $manager->persist($category);

        $this->addReference('cat-'.$this->count, $category);
        $this->count++;

        return $category;
    }
}
