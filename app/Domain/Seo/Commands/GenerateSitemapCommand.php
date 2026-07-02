<?php

namespace App\Domain\Seo\Commands;

use App\Domain\Seo\Jobs\GenerateSitemapJob;
use App\Domain\Seo\Services\SitemapGenerator;
use Illuminate\Console\Command;
use Throwable;

final class GenerateSitemapCommand extends Command
{
    protected $signature = 'seo:generate-sitemap
        {--sync : Выполнить генерацию синхронно и сразу записать файл}';

    protected $description = 'Сгенерировать sitemap.xml в очередь или синхронно';

    public function handle(SitemapGenerator $generator): int
    {
        $queue = (string) config('sitemap.queue');
        $targetPath = $this->targetPath();

        if ((bool) $this->option('sync')) {
            return $this->runSynchronously($generator);
        }

        GenerateSitemapJob::dispatch();

        $this->info('GenerateSitemapJob поставлен в очередь.');
        $this->line('Queue: '.$queue);
        $this->line('Target: '.$targetPath);
        $this->line('Mode: queued');

        return self::SUCCESS;
    }

    private function runSynchronously(SitemapGenerator $generator): int
    {
        try {
            $result = $generator->generate();
        } catch (Throwable $exception) {
            $this->error('Генерация sitemap завершилась с ошибкой: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Sitemap успешно сгенерирован.');
        $this->line('URLs: '.$result->urlCount);
        $this->line('Categories: '.$result->categoryCount);
        $this->line('Products: '.$result->productCount);
        $this->line('Shipments: '.$result->shipmentCount);
        $this->line('File: '.$result->filePath);
        $this->line('Mode: sync');

        return self::SUCCESS;
    }

    private function targetPath(): string
    {
        return \Storage::disk((string) config('sitemap.disk'))
            ->path((string) config('sitemap.path'));
    }
}
