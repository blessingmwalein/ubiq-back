<?php

namespace App\Console\Commands;

use App\Models\ContentItem;
use Illuminate\Console\Command;

class PublishAllContent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'content:publish-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish all content items (set visibility=public and published_at=now)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Publishing all content items...');
        
        $updated = ContentItem::query()
            ->where(function ($query) {
                $query->whereNull('visibility')
                    ->orWhere('visibility', '!=', 'public')
                    ->orWhereNull('published_at');
            })
            ->update([
                'visibility' => 'public',
                'published_at' => now(),
            ]);
        
        $this->info("✅ Updated {$updated} content items");
        
        // Show summary
        $this->newLine();
        $this->info('Content Summary:');
        $this->table(
            ['Type', 'Total', 'Published', 'Unpublished'],
            [
                [
                    'Movies',
                    ContentItem::where('type', 'movie')->count(),
                    ContentItem::where('type', 'movie')->published()->count(),
                    ContentItem::where('type', 'movie')->whereNull('published_at')->count(),
                ],
                [
                    'Shows',
                    ContentItem::where('type', 'show')->count(),
                    ContentItem::where('type', 'show')->published()->count(),
                    ContentItem::where('type', 'show')->whereNull('published_at')->count(),
                ],
                //skits
                
                [
                    'All Content',
                    ContentItem::count(),
                    ContentItem::published()->count(),
                    ContentItem::whereNull('published_at')->count(),
                ],
            ]
        );
        
        return Command::SUCCESS;
    }
}
