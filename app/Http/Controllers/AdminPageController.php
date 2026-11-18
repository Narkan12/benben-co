<?php

namespace App\Http\Controllers;

use App\Models\PageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AdminPageController extends Controller
{
    public function index()
    {
        $this->authorizeAdmin();

        $savedPages = PageContent::query()->get()->keyBy('page_key');
        $pages = collect(PageContent::DEFINITIONS)->map(function (array $definition, string $pageKey) use ($savedPages) {
            return [
                'key' => $pageKey,
                'label' => $definition['label'],
                'content' => $savedPages->get($pageKey),
            ];
        });

        return view('admin.pages.index', compact('pages'));
    }

    public function edit(string $pageKey)
    {
        $this->authorizeAdmin();
        $definition = $this->definition($pageKey);
        $page = PageContent::query()->firstOrNew(['page_key' => $pageKey]);

        return view('admin.pages.edit', compact('page', 'pageKey', 'definition'));
    }

    public function update(Request $request, string $pageKey)
    {
        $this->authorizeAdmin();
        $this->definition($pageKey);

        $data = $request->validate([
            'eyebrow' => ['required', 'string', 'max:120'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string', 'max:3000'],
            'status' => ['required', 'in:draft,published'],
            'gambar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $page = PageContent::query()->firstOrNew(['page_key' => $pageKey]);
        $oldImagePath = $page->image_path;
        $page->fill([
            'eyebrow' => $data['eyebrow'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
        ]);

        if ($request->hasFile('gambar')) {
            $page->image_path = $request->file('gambar')->store('page-content', 'public');
        }

        $page->save();

        if (
            $oldImagePath
            && $page->image_path !== $oldImagePath
            && str_starts_with($oldImagePath, 'page-content/')
        ) {
            Storage::disk('public')->delete($oldImagePath);
        }

        return redirect()
            ->route('admin.pages.edit', $pageKey)
            ->with('success', $data['status'] === 'published'
                ? 'Halaman berhasil dipublikasikan.'
                : 'Perubahan berhasil disimpan sebagai draft.');
    }

    public function preview(string $pageKey)
    {
        $this->authorizeAdmin();
        $definition = $this->definition($pageKey);
        $page = PageContent::query()->firstOrNew(['page_key' => $pageKey]);

        return view('admin.pages.preview', compact('page', 'pageKey', 'definition'));
    }

    private function definition(string $pageKey): array
    {
        abort_unless(isset(PageContent::DEFINITIONS[$pageKey]), 404);

        return PageContent::DEFINITIONS[$pageKey];
    }

    private function authorizeAdmin(): void
    {
        abort_unless(Auth::user()?->role === 'admin', 403);
    }
}
