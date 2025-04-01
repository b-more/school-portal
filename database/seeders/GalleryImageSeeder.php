<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class GalleryImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing records
        DB::table('gallery_images')->truncate();

        // Define the new categories
        $categories = ['staff', 'ecl', 'infrastructure', 'primary', 'secondary'];

        $this->command->info('Starting gallery image seeding process with updated categories...');
        $this->command->info('Base path for images: ' . public_path('images'));

        $totalImagesFound = 0;
        $totalImagesProcessed = 0;

        // Process each category
        foreach ($categories as $category) {
            // Get all files from the category directory in public/images
            $categoryPath = public_path("images/{$category}");

            // Check if directory exists
            if (!File::exists($categoryPath)) {
                $this->command->warn("Directory not found: {$categoryPath}");
                continue;
            }

            $this->command->info("Processing category: {$category} (Path: {$categoryPath})");

            // Get files
            $files = File::files($categoryPath);
            $this->command->info("Found " . count($files) . " files in {$category} directory");

            $totalImagesFound += count($files);

            foreach ($files as $file) {
                // Get the filename
                $filename = $file->getFilename();

                // Skip if it's not an image file
                if (!in_array(strtolower($file->getExtension()), ['jpg', 'jpeg', 'png', 'gif'])) {
                    $this->command->warn("Skipping non-image file: {$filename}");
                    continue;
                }

                // Generate a title based on the filename
                $title = ucwords(str_replace(['-', '_'], ' ', pathinfo($filename, PATHINFO_FILENAME)));

                // Generate a human-readable description based on the category
                $description = $this->generateDescription($category, $title);

                // Build the path
                $path = "images/{$category}/{$filename}";

                // Insert into database
                try {
                    DB::table('gallery_images')->insert([
                        'title' => $title,
                        'description' => $description,
                        'filename' => $filename,
                        'path' => $path,
                        'thumbnail_path' => $path, // Same as path for now
                        'category' => $category, // Use the actual category from folder structure
                        'uploaded_by' => 1, // Assuming user ID 1 exists
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]);

                    $totalImagesProcessed++;
                } catch (\Exception $e) {
                    $this->command->error("Failed to insert {$filename}: " . $e->getMessage());
                }
            }
        }

        $this->command->info("Total images found: {$totalImagesFound}");
        $this->command->info("Total images processed and inserted: {$totalImagesProcessed}");

        $this->command->info('Gallery images seeded successfully!');
    }

    /**
     * Generate a more descriptive caption based on the category
     */
    private function generateDescription($category, $title)
    {
        $descriptions = [
            'staff' => 'Our dedicated staff members who provide exceptional education and support to our students.',
            'ecl' => 'Early Childhood Learning - Nurturing young minds in their first steps of education.',
            'infrastructure' => 'Our modern facilities and infrastructure designed to enhance the learning experience.',
            'primary' => 'Primary school students engaged in various educational and co-curricular activities.',
            'secondary' => 'Secondary students participating in academic and extracurricular programs.',
        ];

        return $descriptions[$category] ?? "Image of $title in our school";
    }
}
