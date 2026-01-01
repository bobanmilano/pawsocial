<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/profile-image')]
#[IsGranted('ROLE_USER')]
class ProfileImageController extends AbstractController
{
    #[Route('/update/{type}/{id}', name: 'app_api_profile_image_update', methods: ['POST'])]
    public function update(
        string $type,
        ?User $user,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        /** @var User $currentUser */
        $currentUser = $this->getUser();

        // If no user object (id=null), use current user
        $targetUser = $user ?? $currentUser;

        if ($targetUser !== $currentUser && !$targetUser->isManagedBy($currentUser)) {
            return new JsonResponse(['error' => 'Access denied'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $imageData = $data['image'] ?? null;

        if (!$imageData) {
            return new JsonResponse(['error' => 'No image data'], 400);
        }

        // Decode base64
        if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $typeMatch)) {
            $extension = strtolower($typeMatch[1]);
            $imageData = substr($imageData, strpos($imageData, ',') + 1);
            $decoded = base64_decode($imageData, true);

            if ($decoded === false) {
                return new JsonResponse(['error' => 'Invalid base64'], 400);
            }
            $imageData = $decoded;
        } else {
            return new JsonResponse(['error' => 'Invalid image format'], 400);
        }

        // Create temp file
        $tmpFilePath = sys_get_temp_dir() . '/' . uniqid('profile_upload_', true) . '.' . $extension;
        file_put_contents($tmpFilePath, $imageData);

        // Wrapping in UploadedFile so VichUploader treats it like a real upload
        $file = new UploadedFile(
            $tmpFilePath,
            "profile_image.$extension",
            "image/$extension",
            null,
            true // test mode to allow moving the file
        );

        try {
            if ($type === 'avatar') {
                $targetUser->setImageFile($file);
            } elseif ($type === 'cover') {
                $targetUser->setCoverImageFile($file);
            } else {
                return new JsonResponse(['error' => 'Invalid type'], 400);
            }

            $entityManager->flush();

            // The image name is updated by Vich during flush
            $imageName = ($type === 'avatar') ? $targetUser->getImageName() : $targetUser->getCoverImageName();
            $folder = ($type === 'avatar') ? 'avatars' : 'covers';

            return new JsonResponse([
                'success' => true,
                'image_url' => "/uploads/$folder/$imageName"
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        } finally {
            if (file_exists($tmpFilePath)) {
                @unlink($tmpFilePath);
            }
        }
    }
}
