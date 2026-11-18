<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ThemeModeViewsTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_and_auth_pages_include_the_shared_theme_toggle(): void
    {
        foreach ([
            route('home'),
            route('products'),
            route('login'),
            route('register'),
            route('admin.login'),
        ] as $url) {
            $this->get($url)
                ->assertOk()
                ->assertSee('data-theme-toggle', false)
                ->assertSee('js/theme.js')
                ->assertSee('css/theme.css');
        }
    }
}
