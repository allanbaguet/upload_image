<?php
require_once __DIR__ . '/../config/config.php';

try {
    if ($_SERVER["REQUEST_METHOD"] == 'POST') {

        try {

            if (!isset($_FILES['profile'])) {
                throw new Exception("Le champ profile n'existe pas");
            }

            if ($_FILES['profile']['error'] != 0) {
                throw new Exception("Une erreur est survenue lors du transfert");
            }

            if ($_FILES['profile']['type'] != 'image/png') {
                throw new Exception("Ce fichier n'est pas au bon format");
            }

            if ($_FILES['profile']['size'] > MAX_FILESIZE) {
                throw new Exception("Ce fichier est trop volumineux");
            }

            // Upload de l'image sur le serveur dans le bon dossier
            $from = $_FILES['profile']['tmp_name'];
            $extension = pathinfo($_FILES['profile']['name'], PATHINFO_EXTENSION);
            $filename_original = uniqid('profile_') . '.' . $extension;
            $to = __DIR__ . '/../public/uploads/users/' . $filename_original;
            move_uploaded_file($from, $to);

            // Création d'une ressource de type GDImage (espace mémoire contenant l'image)
            $image = imagecreatefromjpeg($to);

            // Récupération des dimensions originales de l'image
            $widthOriginal = imagesx($gd_original);
            $heightOriginal = imagesy($gd_original);

            // L'image est-elle de type portrait ou paysage?
            $isPortrait = ($heightOriginal > $widthOriginal) ? true : false;

            $ratio = $heightOriginal / $widthOriginal;

            // Selon portrait ou paysage, définition des hauteurs et largeurs de l'image devant
            // etre redimensionnée.
            if ($isPortrait) {
                $widthResized = 300;
                $heightResized = -1;
            } else {
                $heightResized = 300;
                // Définition de la largeur en fonction du ratio d'origine
                $widthResized = $heightResized / $ratio;
            }

            $mode = IMG_BICUBIC;
            // Création d'une ressource GD contenant l'image redimensionnée selon valeurs précédentes
            $resizedImgRessource = imagescale($image, $widthResized, $heightResized, $mode);
            // Enregistrement de l'image dans le bon dossier et selon une compression optionnelle (75)
            imagejpeg($resizedImgRessource, $to, 85);

            // On recadre au centre horizontal et vertical
            $widthCropped = 200;
            $heightCropped = 200;
            $x = ($widthResized - $widthCropped) / 2;
            $y = ($heightResized - $heightCropped) / 2;

            // Création d'une ressource GD contenant l'image recadrée selon valeurs précédentes
            $croppedImgRessource = imagecrop($resizedImgRessource, ['x' => $x, 'y' => $y, 'width' => $widthCropped, 'height' => $heightCropped]);
            $filename = uniqid('cropped_') . '.' . $extension;
            $to = __DIR__ . '/../public/uploads/users/' . $filename;
            imagejpeg($croppedImgRessource, $to);
        } catch (\Throwable $th) {
            $error = $th->getMessage();
        }
    }
} catch (\Throwable $th) {
    var_dump($th);
}

include __DIR__ . '/../views/templates/header.php';
include __DIR__ . '/../views/upload.php';
include __DIR__ . '/../views/templates/footer.php';
