<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Kreait\Firebase\Storage;
use Kreait\Firebase\Exception\FirebaseException;

class UploadPhotoFirebaseService
{
    protected $firebaseStorage;

    public function __construct(Storage $firebaseStorage)
    {
        $this->firebaseStorage = $firebaseStorage;
    }

    /**
     * Upload photo to Firebase Storage and return the file URL.
     */
    public function uploadPhoto(UploadedFile $photo): string
    {
        try {
            // Générer un nom unique pour le fichier
            $fileName = 'users/photos/' . uniqid() . '_' . $photo->getClientOriginalName();

            // Stocker le fichier dans Firebase
            $this->firebaseStorage->getBucket()->upload(
                file_get_contents($photo->getRealPath()),
                ['name' => $fileName]
            );

            // Retourner l'URL publique du fichier
            return $this->firebaseStorage->getBucket()->object($fileName)->signedUrl(now()->addYears(1));
        } catch (FirebaseException $e) {
            // Gérer l'exception, loguer l'erreur ou relancer
            throw new \Exception('Erreur lors de l\'upload de la photo : ' . $e->getMessage());
        }
    }

    
}
