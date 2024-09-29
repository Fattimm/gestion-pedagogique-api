<?php

namespace App\Services;

use Kreait\Firebase\Storage;
use Illuminate\Support\Facades\Log;

class UploadPhotoFirebaseService
{
    protected $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * Upload a photo with a custom file name based on the entity type.
     * 
     * @param \Illuminate\Http\File|\Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @param string $entityType The type of entity (e.g. 'promotion', 'referentiel', 'user', 'apprenant')
     * @param string $entityValue The value used for naming (e.g. 'libelle' for promotion/referentiel, 'login' for user/apprenant)
     * @return string The URL of the uploaded photo.
     * @throws \Exception
     */
    public function uploadPhoto($file, $folder, $entityType, $entityValue)
    {
        // Valider la configuration de l'environnement
        $bucketName = env('FIREBASE_STORAGE_BUCKET');
        if (!$bucketName) {
            throw new \Exception('Le nom du bucket Firebase n\'est pas configuré.');
        }
        

        // Valider les propriétés du fichier (taille et type)
        $maxSize = 5 * 1024 * 1024; // Limite de 5MB
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        if ($file->getSize() > $maxSize) {
            throw new \Exception('Le fichier dépasse la taille maximale autorisée.');
        }

        if (!in_array($file->getClientOriginalExtension(), $allowedExtensions)) {
            throw new \Exception('Seules les images de type jpg, jpeg, png et gif sont autorisées.');
        }

        try {
            // Accéder au bucket Firebase
            $bucket = $this->storage->getBucket($bucketName);

            // Nettoyer l'entité pour éviter les caractères indésirables dans le nom du fichier
            $safeEntityValue = $this->sanitizeFileName($entityValue);

            // Choisir le nom de fichier en fonction du type d'entité
            $fileName = '';
            switch ($entityType) {
                case 'promotion':
                case 'referentiel':
                    $fileName = $folder . '/' . $safeEntityValue . '.' . $file->getClientOriginalExtension();
                    break;
                case 'user':
                case 'apprenant':
                    $fileName = $folder . '/' . $safeEntityValue . '.' . $file->getClientOriginalExtension();
                    break;
                default:
                    throw new \Exception('Type d\'entité non supporté pour l\'upload.');
            }

            // Uploader le fichier avec des permissions publiques
            $bucket->upload(fopen($file->getPathname(), 'r'), [
                'name' => $fileName,
                'predefinedAcl' => 'publicRead', // Permettre l'accès public
            ]);

            // Retourner l'URL publique du fichier
            return 'https://storage.googleapis.com/' . $bucketName . '/' . $fileName;

        } catch (\Throwable $e) {
            // Log d'erreur et gestion des exceptions
            Log::error('Erreur lors de l\'upload de la photo : ' . $e->getMessage());
            throw new \Exception('Erreur lors de l\'upload de la photo.');
        }
    }

    /**
     * Sanitize the file name to remove or replace invalid characters.
     * 
     * @param string $fileName
     * @return string
     */
    private function sanitizeFileName($fileName)
    {
        // Remplacer les espaces par des underscores et retirer les caractères spéciaux
        return preg_replace('/[^A-Za-z0-9_\-]/', '_', $fileName);
    }
}





// namespace App\Services;

// use Illuminate\Http\UploadedFile;
// use Kreait\Firebase\Storage;
// use Kreait\Firebase\Exception\FirebaseException;

// class UploadPhotoFirebaseService
// {
//     protected $firebaseStorage;

//     public function __construct(Storage $firebaseStorage)
//     {
//         $this->firebaseStorage = $firebaseStorage;
//     }

//     /**
//      * Upload photo to Firebase Storage and return the file URL.
//      */
//     public function uploadPhoto(UploadedFile $photo): string
//     {
//         try {
//             // Générer un nom unique pour le fichier
//             $fileName = 'users/photos/' . uniqid() . '_' . $photo->getClientOriginalName();

//             // Stocker le fichier dans Firebase
//             $this->firebaseStorage->getBucket()->upload(
//                 file_get_contents($photo->getRealPath()),
//                 ['name' => $fileName]
//             );

//             // Retourner l'URL publique du fichier
//             return $this->firebaseStorage->getBucket()->object($fileName)->signedUrl(now()->addYears(1));
//         } catch (FirebaseException $e) {
//             // Gérer l'exception, loguer l'erreur ou relancer
//             throw new \Exception('Erreur lors de l\'upload de la photo : ' . $e->getMessage());
//         }
//     }

    
// }
