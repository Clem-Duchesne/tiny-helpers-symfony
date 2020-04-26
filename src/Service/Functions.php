<?php

namespace App\Service;

use Cocur\Slugify\Slugify;


class Functions
{
    /** Fonction pour supprimer les anciennes images **/
    public function deleteImage($img){

        //Vérifier si l'image est présente sur le serveur

        if(file_exists('../public/assets/img/' . $img)){

            //Suppression de l'image
            unlink('../public/assets/img/' . $img);      
        }
    }
    /** Fonction d'encryptage des mdp */
    function encrypt($password){
        $pwd = password_hash($password, PASSWORD_BCRYPT);
        return $pwd;
      }
 

    /** Fonction pour redimensionner les images **/
    function redimensionner_image($fichier, $nouvelle_taille) {
        
        //VARIABLE D'ERREUR
        global $error;

        //TAILLE EN PIXELS DE L'IMAGE REDIMENSIONNEE
        $longueur = $nouvelle_taille;
        
        //TAILLE DE L'IMAGE ACTUELLE
        $taille = getimagesize($fichier);
        $largeur = ($longueur*$taille[1])/$taille[0];
        //SI LE FICHIER EXISTE
        if ($taille) {    
            //SI JPG
            if ($taille['mime']=='image/jpeg' ) {
                //OUVERTURE DE L'IMAGE ORIGINALE
                $img_big = imagecreatefromjpeg($fichier); 
                $img_new = imagecreate($longueur, $largeur);
                
                //CREATION DE LA MINIATURE
                $img_petite = imagecreatetruecolor($longueur, $largeur) or $img_petite = imagecreate($longueur, $largeur);

                //COPIE DE L'IMAGE REDIMENSIONNEE
                imagecopyresized($img_petite,$img_big,0,0,0,0,$longueur,$largeur,$taille[0],$taille[1]);
                imagejpeg($img_petite,$fichier);

            }
            
            //SI PNG
            else if ($taille['mime']=='image/png' ) {
                //OUVERTURE DE L'IMAGE ORIGINALE
                $img_big = imagecreatefrompng($fichier); // On ouvre l'image d'origine
                $img_new = imagecreate($longueur, $largeur);
                
                //CREATION DE LA MINIATURE
                $img_petite = imagecreatetruecolor($longueur, $largeur) OR $img_petite = imagecreate($longueur, $largeur);

                //COPIE DE L'IMAGE REDIMENSIONNEE
                imagecopyresized($img_petite,$img_big,0,0,0,0,$longueur,$largeur,$taille[0],$taille[1]);
                imagepng($img_petite,$fichier);

            }
            // GIF
            else if ($taille['mime']=='image/gif' ) {
                //OUVERTURE DE L'IMAGE ORIGINALE
                $img_big = imagecreatefromgif($fichier); 
                $img_new = imagecreate($longueur, $largeur);
                
                //CREATION DE LA MINIATURE
                $img_petite = imagecreatetruecolor($longueur, $largeur) or $img_petite = imagecreate($longueur, $largeur);

                //COPIE DE L'IMAGE REDIMENSIONNEE
                imagecopyresized($img_petite,$img_big,0,0,0,0,$longueur,$largeur,$taille[0],$taille[1]);
                imagegif($img_petite,$fichier);

            }
        }
    }
       /** Fonction d'ajout d'image */
       function addImage($imageFile, $uploadDir){
        $slugify = new Slugify();
        $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
        // this is needed to safely include the file name as part of the URL
        $safeFilename = $slugify->slugify($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

        // Move the file to the directory where brochures are stored
        try {
            $imageFile->move($uploadDir,$newFilename);

            $this->redimensionner_image( $uploadDir . '/' .$newFilename, 345);
        }
        catch (FileException $e) {
            // ... handle exception if something happens during file upload
        }
        return $newFilename;
    }
}

