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

    public function test_it_maps_advertisements_from_nested_advertisements_container(): void
    {
        $path = $this->writeTempXml(<<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<advertisements_export export_date="2026-06-28 20:20:01">
    <categories>
        <category id="2">
            <name>16К20 и аналоги</name>
        </category>
    </categories>
    <advertisement_statuses>
        <status id="1">
            <name>Ревизия</name>
        </status>
    </advertisement_statuses>
    <check_statuses />
    <install_statuses />
    <advertisements>
        <advertisement id="1">
            <title>Токарно-винторезный 1К62, РМЦ1000</title>
            <sku>ЧЛБ-022-13112025-1739</sku>
            <category id="2">16К20 и аналоги</category>
            <status id="1">Ревизия</status>
            <product_state id="1">Б.У</product_state>
            <product_available id="1">В наличии</product_available>
            <price>
                <adv_price>180000.00</adv_price>
                <show_price>1</show_price>
            </price>
            <location>
                <product_address>г. Челябинск</product_address>
                <regions>
                    <region id="18">
                        <name>Челябинская область</name>
                    </region>
                </regions>
            </location>
            <manager>
                <product_owner id="6">
                    <name>Семенкин Кирилл</name>
                    <email>kirill.semenkin@uniqset.com</email>
                    <phone>+79611215565</phone>
                    <role>Менеджер</role>
                </product_owner>
            </manager>
            <media>
                <media_item id="1">
                    <file_name>IMG-20251112-WA0036.jpg</file_name>
                    <file_url>https://panel.uniqset.com/storage/example.jpg</file_url>
                    <mime_type>image/jpeg</mime_type>
                    <file_size>176019</file_size>
                    <sort_order>0</sort_order>
                    <is_main_image>1</is_main_image>
                </media_item>
            </media>
            <tags>
                <tag>1К62</tag>
                <tag>токарник</tag>
            </tags>
            <dates>
                <published_at>2026-05-26 10:08:25</published_at>
            </dates>
        </advertisement>
    </advertisements>
</advertisements_export>
XML);

        try {
            $parser = new FeedParser();
            $advertisements = [...$parser->advertisements($path)];

            self::assertCount(1, $advertisements);
            self::assertSame(1, $advertisements[0]->externalId);
            self::assertSame('Токарно-винторезный 1К62, РМЦ1000', $advertisements[0]->name);
            self::assertSame('ЧЛБ-022-13112025-1739', $advertisements[0]->sku);
            self::assertSame(2, $advertisements[0]->categoryExternalId);
            self::assertSame(1, $advertisements[0]->statusExternalId);
            self::assertSame(['1К62', 'токарник'], $advertisements[0]->tags);
            self::assertCount(1, $advertisements[0]->media);
        } finally {
            @unlink($path);
        }
    }

    public function test_it_preserves_html_in_text_blocks_and_trims_surrounding_whitespace(): void
    {
        $path = $this->writeTempXml(<<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<advertisements_export export_date="2026-06-28 20:20:01">
    <advertisement_statuses>
        <status id="1">
            <name>Ревизия</name>
        </status>
    </advertisement_statuses>
    <check_statuses />
    <install_statuses />
    <advertisement id="1">
        <title>Токарный 1К62</title>
        <sku>sku-1</sku>
        <status id="1">Ревизия</status>
        <price>
            <adv_price>180000.00</adv_price>
            <show_price>1</show_price>
            <adv_price_comment><![CDATA[ Цена с НДС. ]]></adv_price_comment>
        </price>
        <technical_characteristics><![CDATA[ <p><span style="color: rgb(0, 0, 0);">⚙️ Технические характеристики:</span></p><p>над станиной 400</p> ]]></technical_characteristics>
    </advertisement>
</advertisements_export>
XML);

        try {
            $parser = new FeedParser();
            $advertisements = [...$parser->advertisements($path)];

            self::assertCount(1, $advertisements);

            // HTML tags preserved verbatim, but surrounding whitespace trimmed.
            self::assertSame(
                '<p><span style="color: rgb(0, 0, 0);">⚙️ Технические характеристики:</span></p><p>над станиной 400</p>',
                $advertisements[0]->technicalCharacteristics,
            );

            // Price comment keeps its surrounding whitespace (not an HTML block).
            self::assertSame(' Цена с НДС. ', $advertisements[0]->priceComment);
        } finally {
            @unlink($path);
        }
    }

    private function writeTempXml(string $xml): string
    {
        $path = tempnam(sys_get_temp_dir(), 'feed_parser_');

        if ($path === false) {
            self::fail('Unable to create temporary XML fixture.');
        }

        file_put_contents($path, $xml);

        return $path;
    }
}
