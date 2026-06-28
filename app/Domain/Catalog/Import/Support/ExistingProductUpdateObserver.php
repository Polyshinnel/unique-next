<?php

namespace App\Domain\Catalog\Import\Support;

use App\Domain\Catalog\Models\ProductImport;
use Carbon\CarbonImmutable;
use Closure;
use Throwable;

final class ExistingProductUpdateObserver
{
    /**
     * @param null|callable(string): void $consoleOutput
     */
    public function __construct(
        private readonly ?ProductImport $productImport = null,
        ?callable $consoleOutput = null,
    ) {
        $this->consoleOutput = $consoleOutput !== null
            ? Closure::fromCallable($consoleOutput)
            : null;
    }

    private readonly ?Closure $consoleOutput;

    public static function createQueued(string $url, string $queue): ProductImport
    {
        return ProductImport::query()->create([
            'status' => 'queued',
            'message' => sprintf(
                'Queued existing product update. queue=%s; url=%s',
                $queue,
                $url,
            ),
        ]);
    }

    /**
     * @param array<string, mixed> $context
     */
    public function __invoke(string $event, array $context = []): void
    {
        match ($event) {
            'started' => $this->markRunning($context),
            'feed_downloaded' => $this->markFeedDownloaded($context),
            'progress' => $this->updateProgress($context, $this->progressMessage($context)),
            'failed_item' => $this->updateProgress($context, $this->failedItemMessage($context)),
            'finished' => $this->markFinished($context),
            default => null,
        };
    }

    public function markFailed(Throwable $exception): void
    {
        $message = 'Existing product update failed: '.$exception->getMessage();

        $this->persist([
            'status' => 'failed',
            'finished_at' => now(),
            'message' => $this->truncate($message),
        ]);

        $this->writeConsole($message);
    }

    public function id(): ?int
    {
        return $this->productImport?->getKey();
    }

    /**
     * @param array<string, mixed> $context
     */
    private function markRunning(array $context): void
    {
        $message = sprintf(
            'Existing product update started. url=%s',
            (string) ($context['url'] ?? ''),
        );

        $this->persist([
            'status' => 'running',
            'started_at' => now(),
            'message' => $this->truncate($message),
        ]);

        $this->writeConsole($message);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function markFeedDownloaded(array $context): void
    {
        $exportDate = $this->parseExportDate($context['export_date'] ?? null);
        $message = 'Existing product feed downloaded.';

        if ($exportDate !== null) {
            $message .= ' export_date='.$exportDate->format('Y-m-d H:i:s');
        }

        $this->persist(array_filter([
            'feed_export_date' => $exportDate,
            'message' => $this->truncate($message),
        ], static fn (mixed $value): bool => $value !== null));

        $this->writeConsole($message);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function updateProgress(array $context, string $message): void
    {
        $this->persist([
            'created_count' => (int) ($context['updated'] ?? 0),
            'skipped_count' => (int) ($context['skipped'] ?? 0),
            'failed_count' => (int) ($context['failed'] ?? 0),
            'message' => $this->truncate($message),
        ]);

        $this->writeConsole($message);
    }

    /**
     * @param array<string, mixed> $context
     */
    private function markFinished(array $context): void
    {
        $message = $this->finishedMessage($context);

        $this->persist([
            'status' => 'completed',
            'created_count' => (int) ($context['updated'] ?? 0),
            'skipped_count' => (int) ($context['skipped'] ?? 0),
            'failed_count' => (int) ($context['failed'] ?? 0),
            'finished_at' => now(),
            'message' => $this->truncate($message),
        ]);

        $this->writeConsole($message);
    }

    private function progressMessage(array $context): string
    {
        $message = sprintf(
            'Existing product update progress. processed=%d; updated=%d; skipped=%d; failed=%d',
            (int) ($context['processed'] ?? 0),
            (int) ($context['updated'] ?? 0),
            (int) ($context['skipped'] ?? 0),
            (int) ($context['failed'] ?? 0),
        );

        if (isset($context['advertisement_external_id'])) {
            $message .= '; advertisement_external_id='.(int) $context['advertisement_external_id'];
        }

        if (isset($context['sku']) && is_string($context['sku']) && $context['sku'] !== '') {
            $message .= '; sku='.$context['sku'];
        }

        return $message;
    }

    private function failedItemMessage(array $context): string
    {
        $message = $this->progressMessage($context);

        if (isset($context['error']) && is_string($context['error']) && $context['error'] !== '') {
            $message .= '; error='.$context['error'];
        }

        return $message;
    }

    private function finishedMessage(array $context): string
    {
        $message = sprintf(
            'Existing product update finished. processed=%d; updated=%d; skipped=%d; failed=%d',
            (int) ($context['processed'] ?? 0),
            (int) ($context['updated'] ?? 0),
            (int) ($context['skipped'] ?? 0),
            (int) ($context['failed'] ?? 0),
        );

        if (isset($context['export_date']) && is_string($context['export_date']) && $context['export_date'] !== '') {
            $message .= '; export_date='.$context['export_date'];
        }

        return $message;
    }

    /**
     * @param array<string, mixed> $attributes
     */
    private function persist(array $attributes): void
    {
        if ($this->productImport === null) {
            return;
        }

        $this->productImport->forceFill($attributes)->save();
    }

    private function writeConsole(string $message): void
    {
        if ($this->consoleOutput !== null) {
            ($this->consoleOutput)($message);
        }
    }

    private function parseExportDate(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function truncate(string $message): string
    {
        return mb_substr($message, 0, 65535);
    }
}
