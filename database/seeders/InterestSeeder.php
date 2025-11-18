<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Interest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class InterestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create categories first
        $actionCategory = Category::firstOrCreate(
            ['key' => 'action'],
            [
                'uuid' => Str::uuid(),
                'title' => 'Action',
                'description' => 'Action-packed content',
                'sort_order' => 1,
                'is_active' => true,
            ]
        );

        $comedyCategory = Category::firstOrCreate(
            ['key' => 'comedy'],
            [
                'uuid' => Str::uuid(),
                'title' => 'Comedy',
                'description' => 'Funny and entertaining content',
                'sort_order' => 2,
                'is_active' => true,
            ]
        );

        $dramaCategory = Category::firstOrCreate(
            ['key' => 'drama'],
            [
                'uuid' => Str::uuid(),
                'title' => 'Drama',
                'description' => 'Dramatic storylines',
                'sort_order' => 3,
                'is_active' => true,
            ]
        );

        $scifiCategory = Category::firstOrCreate(
            ['key' => 'sci-fi'],
            [
                'uuid' => Str::uuid(),
                'title' => 'Sci-Fi',
                'description' => 'Science fiction content',
                'sort_order' => 4,
                'is_active' => true,
            ]
        );

        $horrorCategory = Category::firstOrCreate(
            ['key' => 'horror'],
            [
                'uuid' => Str::uuid(),
                'title' => 'Horror',
                'description' => 'Scary and thrilling content',
                'sort_order' => 5,
                'is_active' => true,
            ]
        );

        $documentaryCategory = Category::firstOrCreate(
            ['key' => 'documentary'],
            [
                'uuid' => Str::uuid(),
                'title' => 'Documentary',
                'description' => 'Real-world documentaries',
                'sort_order' => 6,
                'is_active' => true,
            ]
        );

        $animationCategory = Category::firstOrCreate(
            ['key' => 'animation'],
            [
                'uuid' => Str::uuid(),
                'title' => 'Animation',
                'description' => 'Animated content',
                'sort_order' => 7,
                'is_active' => true,
            ]
        );

        // Define interests by category
        $interests = [
            // Action
            ['category_id' => $actionCategory->id, 'name' => 'Superhero Movies', 'slug' => 'superhero-movies'],
            ['category_id' => $actionCategory->id, 'name' => 'Martial Arts', 'slug' => 'martial-arts'],
            ['category_id' => $actionCategory->id, 'name' => 'Thriller Action', 'slug' => 'thriller-action'],
            ['category_id' => $actionCategory->id, 'name' => 'Adventure', 'slug' => 'adventure'],
            
            // Comedy
            ['category_id' => $comedyCategory->id, 'name' => 'Romantic Comedy', 'slug' => 'romantic-comedy'],
            ['category_id' => $comedyCategory->id, 'name' => 'Stand-up Comedy', 'slug' => 'stand-up-comedy'],
            ['category_id' => $comedyCategory->id, 'name' => 'Sitcoms', 'slug' => 'sitcoms'],
            ['category_id' => $comedyCategory->id, 'name' => 'Parody', 'slug' => 'parody'],
            
            // Drama
            ['category_id' => $dramaCategory->id, 'name' => 'Crime Drama', 'slug' => 'crime-drama'],
            ['category_id' => $dramaCategory->id, 'name' => 'Historical Drama', 'slug' => 'historical-drama'],
            ['category_id' => $dramaCategory->id, 'name' => 'Family Drama', 'slug' => 'family-drama'],
            ['category_id' => $dramaCategory->id, 'name' => 'Political Drama', 'slug' => 'political-drama'],
            
            // Sci-Fi
            ['category_id' => $scifiCategory->id, 'name' => 'Space Opera', 'slug' => 'space-opera'],
            ['category_id' => $scifiCategory->id, 'name' => 'Time Travel', 'slug' => 'time-travel'],
            ['category_id' => $scifiCategory->id, 'name' => 'Dystopian', 'slug' => 'dystopian'],
            ['category_id' => $scifiCategory->id, 'name' => 'Alien Encounters', 'slug' => 'alien-encounters'],
            
            // Horror
            ['category_id' => $horrorCategory->id, 'name' => 'Psychological Horror', 'slug' => 'psychological-horror'],
            ['category_id' => $horrorCategory->id, 'name' => 'Supernatural Horror', 'slug' => 'supernatural-horror'],
            ['category_id' => $horrorCategory->id, 'name' => 'Zombie Movies', 'slug' => 'zombie-movies'],
            ['category_id' => $horrorCategory->id, 'name' => 'Slasher Films', 'slug' => 'slasher-films'],
            
            // Documentary
            ['category_id' => $documentaryCategory->id, 'name' => 'Nature & Wildlife', 'slug' => 'nature-wildlife'],
            ['category_id' => $documentaryCategory->id, 'name' => 'True Crime', 'slug' => 'true-crime'],
            ['category_id' => $documentaryCategory->id, 'name' => 'Science & Technology', 'slug' => 'science-technology'],
            ['category_id' => $documentaryCategory->id, 'name' => 'History', 'slug' => 'history'],
            
            // Animation
            ['category_id' => $animationCategory->id, 'name' => 'Anime', 'slug' => 'anime'],
            ['category_id' => $animationCategory->id, 'name' => 'Family Animation', 'slug' => 'family-animation'],
            ['category_id' => $animationCategory->id, 'name' => 'Adult Animation', 'slug' => 'adult-animation'],
            ['category_id' => $animationCategory->id, 'name' => 'Stop Motion', 'slug' => 'stop-motion'],
        ];

        foreach ($interests as $interest) {
            Interest::firstOrCreate(
                ['slug' => $interest['slug']],
                [
                    'uuid' => Str::uuid(),
                    'category_id' => $interest['category_id'],
                    'name' => $interest['name'],
                    'slug' => $interest['slug'],
                    'is_active' => true,
                ]
            );
        }

        $this->command->info('Interests seeded successfully!');
    }
}
