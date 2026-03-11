<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageCacheService
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver());
    }

    public function get(string $url): ?string {

        if (!$url) {
            return null;
        }

        $hash       = md5($url);
        $fileName   = $hash . '.jpg';
        $path       = 'labels/cache/' . $fileName;

        /*
        |----------------------------------------
        | Se já existir no cache
        |----------------------------------------
        */
        if (Storage::disk('public')->exists($path)) {
            return storage_path('app/public/' . $path);
        }

        /*
        |----------------------------------------
        | Baixar imagem
        |----------------------------------------
        */
        try {
            $imageContent = file_get_contents($url);
        } catch (\Exception $e) {
            return null;
        }

        if (!$imageContent) {
            return null;
        }

        /*
        |----------------------------------------
        | Redimensionar imagem
        |----------------------------------------
        */
        try {

            $image = $this->manager->read($imageContent);
            $image->scale(width: 600);
            $encoded = $image->toJpeg(85);
            Storage::disk('public')->put($path, $encoded);
        } catch (\Exception $e) {
            return null;
        }

        return storage_path('app/public/' . $path);
    }
}