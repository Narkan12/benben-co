<?php

namespace Tests\Feature;

use App\Models\PageContent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageContentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_save_page_as_draft_without_changing_public_content(): void
    {
        $admin = $this->createUser('admin');

        $this->actingAs($admin)
            ->put(route('admin.pages.update', 'home'), [
                'eyebrow' => 'Cerita dari dapur',
                'title' => 'Judul Draft Beranda',
                'description' => 'Deskripsi draft.',
                'status' => 'draft',
            ])
            ->assertRedirect(route('admin.pages.edit', 'home'));

        $this->assertDatabaseHas('page_contents', [
            'page_key' => 'home',
            'title' => 'Judul Draft Beranda',
            'status' => 'draft',
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('Judul Draft Beranda');
    }

    public function test_published_page_content_is_used_on_the_public_page(): void
    {
        $admin = $this->createUser('admin');

        $this->actingAs($admin)
            ->put(route('admin.pages.update', 'contact'), [
                'eyebrow' => 'Kami siap membantu',
                'title' => 'Hubungi Tim Bakery',
                'description' => 'Silakan hubungi kami untuk informasi pesanan.',
                'status' => 'published',
            ])
            ->assertRedirect(route('admin.pages.edit', 'contact'));

        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('Hubungi Tim Bakery')
            ->assertSee('Silakan hubungi kami untuk informasi pesanan.');

        $this->assertSame('published', PageContent::query()->where('page_key', 'contact')->value('status'));
    }

    public function test_gallery_is_a_separate_page_and_active_nav_item(): void
    {
        $this->get(route('gallery'))
            ->assertOk()
            ->assertSee('profile-gallery-grid', false)
            ->assertSee('href="'.route('gallery').'"', false)
            ->assertSee('class="active">Gallery', false);

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('id="gallery"', false)
            ->assertDontSee('profile-gallery-grid', false)
            ->assertDontSee('#gallery');
    }

    public function test_cashier_cannot_manage_cms_pages(): void
    {
        $cashier = $this->createUser('kasir');

        $this->actingAs($cashier)
            ->get(route('admin.pages.index'))
            ->assertForbidden();
    }

    private function createUser(string $role): User
    {
        return User::query()->create([
            'nama' => ucfirst($role).' Test',
            'username' => $role.'@example.com',
            'password' => 'password-lama',
            'role' => $role,
        ]);
    }
}
