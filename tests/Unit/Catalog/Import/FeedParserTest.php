<?php

namespace Tests\Unit\Catalog\Import;

use App\Domain\Catalog\Import\Services\FeedParser;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

final class FeedParserTest extends TestCase
{
    private string $fixturePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixturePath = base_path('tests/Fixtures/advertisements.xml');
    }

    public function test_it_reads_export_date_and_top_level_reference_dtos(): void
    {
        Log::spy();

        $parser = new FeedParser();

        self::assertSame('2026-06-28 10:11:12', $parser->exportDate($this->fixturePath));

        $categories = [...$parser->categories($this->fixturePath)];
        $advertisementStatuses = [...$parser->advertisementStatuses($this->fixturePath)];
        $checkStatuses = [...$parser->checkStatuses($this->fixturePath)];
        $installStatuses = [...$parser->installStatuses($this->fixturePath)];

        self::assertCount(1, $categories);
        self::assertSame(10, $categories[0]->externalId);
        self::assertSame('Экскаваторы', $categories[0]->name);
        self::assertSame(2, $categories[0]->parentExternalId);

        self::assertCount(1, $advertisementStatuses);
        self::assertSame(4, $advertisementStatuses[0]->externalId);
        self::assertSame('#00ff00', $advertisementStatuses[0]->color);

        self::assertCount(1, $checkStatuses);
        self::assertSame('Проверено', $checkStatuses[0]->name);

        self::assertCount(1, $installStatuses);
        self::assertSame('Готово к погрузке', $installStatuses[0]->name);

        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $message, array $context): bool => str_contains($message, 'category') && isset($context['error']))
            ->once();
    }

    public function test_it_maps_advertisements_and_skips_invalid_nodes(): void
    {
        Log::spy();

        $parser = new FeedParser();
        $advertisements = [...$parser->advertisements($this->fixturePath)];

        self::assertCount(1, $advertisements);

        $advertisement = $advertisements[0];

        self::assertSame(101, $advertisement->externalId);
        self::assertSame('CASE CX210', $advertisement->name);
        self::assertSame('cx210-001', $advertisement->sku);
        self::assertSame(10, $advertisement->categoryExternalId);
        self::assertSame(4, $advertisement->statusExternalId);
        self::assertSame(15, $advertisement->stateExternalId);
        self::assertSame('Б/у', $advertisement->stateName);
        self::assertSame(21, $advertisement->availabilityExternalId);
        self::assertSame('В наличии', $advertisement->availabilityName);
        self::assertSame('12500000', $advertisement->price);
        self::assertTrue($advertisement->showPrice);
        self::assertSame('С НДС', $advertisement->priceComment);
        self::assertSame('Москва, склад 1', $advertisement->productAddress);
        self::assertSame('2026-06-20 09:00:00', $advertisement->publishedAt);

        self::assertNotNull($advertisement->manager);
        self::assertSame(501, $advertisement->manager->externalId);
        self::assertSame('Иван Иванов', $advertisement->manager->name);

        self::assertCount(1, $advertisement->regions);
        self::assertSame(77, $advertisement->regions[0]->externalId);
        self::assertSame(['лизинг', 'в наличии'], $advertisement->tags);

        self::assertCount(2, $advertisement->media);
        self::assertTrue($advertisement->media[0]->isMain);
        self::assertFalse($advertisement->media[1]->isMain);

        self::assertNotNull($advertisement->check);
        self::assertSame('Проверено', $advertisement->check->name);
        self::assertSame('Осмотр завершен', $advertisement->check->comment);

        self::assertNotNull($advertisement->loading);
        self::assertSame('Погрузка завтра', $advertisement->loading->comment);

        self::assertNotNull($advertisement->removal);
        self::assertSame('Демонтаж не требуется', $advertisement->removal->name);

        self::assertSame('Основные <b>характеристики</b>', $advertisement->mainCharacteristics);
        self::assertSame('Полная комплектация', $advertisement->complectation);
        self::assertSame('Технические характеристики', $advertisement->technicalCharacteristics);
        self::assertSame('Главная информация', $advertisement->mainInfo);
        self::assertSame('Дополнительная информация', $advertisement->additionalInfo);

        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $message, array $context): bool => str_contains($message, 'media') && isset($context['error']))
            ->once();

        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $message, array $context): bool => str_contains($message, 'advertisement') && isset($context['error']))
            ->once();
    }
}
