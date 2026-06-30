<?php

namespace Tests\Feature;

use App\Domain\Banner\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BannerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_banners_endpoint_returns_active_banners_in_sort_order(): void
    {
        Banner::query()->create([
            'title' => 'Inactive banner',
            'sort_order' => 1,
            'is_active' => false,
        ]);

        Banner::query()->create([
            'image' => 'banners/second.jpg',
            'title' => 'Second banner',
            'text' => 'Second text',
            'button_one_text' => 'Open second',
            'button_one_url' => '/second',
            'sort_order' => 20,
            'is_active' => true,
        ]);

        Banner::query()->create([
            'image' => 'banners/first.jpg',
            'title' => 'First banner',
            'text' => 'First text',
            'button_one_text' => 'Open first',
            'button_one_url' => '/first',
            'button_two_text' => 'Contact us',
            'button_two_url' => '/contacts',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/banners');

        $response
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.title', 'First banner')
            ->assertJsonPath('0.image', 'banners/first.jpg')
            ->assertJsonPath('0.button_one_text', 'Open first')
            ->assertJsonPath('0.button_two_url', '/contacts')
            ->assertJsonPath('1.title', 'Second banner');
    }
}
