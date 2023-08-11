<?php

namespace Sametcoban\Laravelthumbnails;

use Exception;

trait Thumbnails{
    public function createThumbnail($source, $destination, $targetWidth, $targetHeight)
    {
        // Kaynak dosyanın tipini kontrol edin
        $imageType = exif_imagetype($source);

        // Kaynak dosyayı uygun bir şekilde açın
        switch ($imageType) {
            case IMAGETYPE_JPEG:
                $originalImage = imagecreatefromjpeg($source);
                break;
            case IMAGETYPE_PNG:
                $originalImage = imagecreatefrompng($source);
                break;
            case IMAGETYPE_GIF:
                $originalImage = imagecreatefromgif($source);
                break;
            default:
                throw new Exception("Geçersiz görüntü formatı");
        }

        // Orijinal görüntünün boyutlarını alın
        $originalWidth = imagesx($originalImage);
        $originalHeight = imagesy($originalImage);

        // En-boy oranını koruyun
        $ratio = $originalWidth / $originalHeight;
        if ($targetWidth / $targetHeight > $ratio) {
            $targetWidth = $targetHeight * $ratio;
        } else {
            $targetHeight = $targetWidth / $ratio;
        }

        // Yeni bir true color görüntü kaynağı oluşturun
        $thumbnail = imagecreatetruecolor($targetWidth, $targetHeight);

        // Kaynak görüntüyü hedef boyutlara uyacak şekilde yeniden boyutlandırın
        imagecopyresampled(
            $thumbnail, $originalImage,
            0, 0, 0, 0,
            $targetWidth, $targetHeight,
            $originalWidth, $originalHeight
        );

        // Thumbnail'ı JPEG olarak kaydedin
        imagejpeg($thumbnail, $destination);

        // Görüntü kaynaklarını bellekten temizleyin
        imagedestroy($originalImage);
        imagedestroy($thumbnail);

        // Thumbnail görüntüsünün gerçek boyutlarını alın
        $actualSize = getimagesize($destination);

        // Oluşturulan thumbnail ile ilgili bilgileri döndürün
        return [
            'path' => $destination,
            'filename' => basename($destination),
            'width' => $actualSize[0], // Thumbnail görüntüsünün gerçek genişliği
            'height' => $actualSize[1], // Thumbnail görüntüsünün gerçek yüksekliği
            'fileSize' => filesize($destination), // Thumbnail dosyasının boyutu
        ];
    }

}
