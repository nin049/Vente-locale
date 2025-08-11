<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class ImageUploadService
{
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        // Vérifications supplémentaires
        if (!$file->isValid()) {
            throw new FileException('Le fichier uploadé n\'est pas valide.');
        }

        // Vérifier la taille (5MB max)
        if ($file->getSize() > 5 * 1024 * 1024) {
            throw new FileException('Le fichier est trop volumineux. Taille maximale : 5MB.');
        }

        // Vérifier le type MIME
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            throw new FileException('Type de fichier non autorisé. Formats acceptés : JPEG, PNG, GIF, WEBP.');
        }

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        
        // Nettoyer le nom de fichier
        if (empty($originalFilename)) {
            $originalFilename = 'image';
        }
        
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            throw new FileException('Erreur lors de l\'upload du fichier : ' . $e->getMessage());
        }

        return $fileName;
    }

    public function deleteImages(array $imageNames): void
    {
        foreach ($imageNames as $imageName) {
            if ($imageName) {
                $filePath = $this->getTargetDirectory() . '/' . $imageName;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
        }
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
