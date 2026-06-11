<?php

// app/Console/Commands/PublishScheduledNews.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\News;

class PublishScheduledNews extends Command
{
    protected $signature = 'news:publish-scheduled';
    protected $description = 'Tự động đăng các bài tin tức đã lên lịch khi đến giờ';

    public function handle()
    {
        $news = News::where('status', 'scheduled')
                     ->where('published_at', '<=', now())
                     ->get();

        foreach ($news as $item) {
            $item->status = 'published';
            $item->save();
        }

        $this->info($news->count() . ' bài viết đã được tự động đăng.');
    }
}
