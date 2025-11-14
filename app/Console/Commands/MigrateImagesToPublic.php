<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class MigrateImagesToPublic extends Command
{
    protected $signature = 'images:migrate-to-public';
    protected $description = 'Migrate images from storage/app/public to public/ for Laravel Cloud';

    public function handle()
    {
        $this->info('Starting image migration...');

        $sourceDisk = 'public'; // storage/app/public
        $targetDisk = 'public_direct'; // public/

        // Create target directory if not exists
        $targetPath = public_path('profile-images');
        if (!File::exists($targetPath)) {
            File::makeDirectory($targetPath, 0755, true);
            $this->info('Created directory: ' . $targetPath);
        }

        // Get all files from source
        $sourceFiles = Storage::disk($sourceDisk)->files('profile-images');
        $migratedCount = 0;

        foreach ($sourceFiles as $file) {
            $filename = basename($file);
            $sourcePath = Storage::disk($sourceDisk)->path($file);
            $targetFilePath = 'profile-images/' . $filename;

            // Copy file if it exists
            if (File::exists($sourcePath)) {
                $content = File::get($sourcePath);
                Storage::disk($targetDisk)->put($targetFilePath, $content);
                $migratedCount++;
                $this->info("Copied: {$filename}");
            }
        }

        $this->info("Migrated {$migratedCount} images to public/");

        // Update database - fix photo URLs
        $this->info('Updating database URLs...');

        $users = User::whereNotNull('photos')->get();
        $updatedUsers = 0;

        foreach ($users as $user) {
            $photos = $user->photos;
            $updated = false;

            if (is_array($photos)) {
                $newPhotos = array_map(function($photo) use (&$updated) {
                    // Remove /storage/ prefix
                    if (str_starts_with($photo, '/storage/')) {
                        $updated = true;
                        return str_replace('/storage/', '', $photo);
                    }
                    return $photo;
                }, $photos);

                if ($updated) {
                    $user->photos = $newPhotos;
                    $user->save();
                    $updatedUsers++;
                }
            }
        }

        $this->info("Updated {$updatedUsers} user records");
        $this->info('Migration complete!');

        return 0;
    }
}
