<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\BannerImage;
use App\Support\BannerSections;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        // One row per storefront section, each listing its banners.
        $bannersBySection = Banner::query()
            ->with('images')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get()
            ->groupBy('section');

        // Gallery categories have their own page (Admin → Gallery); Sections & Images lists the rest.
        $group = $request->query('group') === 'gallery' ? 'gallery' : null;
        $sections = array_filter(
            BannerSections::all(),
            fn ($key) => BannerSections::isGallery($key) === ($group === 'gallery'),
            ARRAY_FILTER_USE_KEY
        );

        return view('admin.banners.index', compact('bannersBySection', 'sections', 'group'));
    }

    public function create(Request $request)
    {
        return view('admin.banners.create', [
            'presetSection' => in_array($request->query('section'), BannerSections::keys(), true) ? $request->query('section') : null,
            'sections' => BannerSections::all(),
            'sectionOptions' => BannerSections::options(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($request, $data) {
            $banner = Banner::create($data);
            $this->storeImages($request, $banner);
        });
        if (($data['section'] ?? null) === 'instagram_post') {
            $this->syncInstagramImage(Banner::where('section', 'instagram_post')->latest('id')->first(), true);
        }

        return redirect(BannerSections::listUrl($data['section'] ?? null))->with('success', BannerSections::isGallery($data['section'] ?? null) ? 'Photo added successfully.' : 'Banner created successfully.');
    }

    public function edit(Banner $banner)
    {
        $banner->load('images');

        return view('admin.banners.edit', [
            'banner' => $banner,
            'sections' => BannerSections::all(),
            'sectionOptions' => BannerSections::options(),
        ]);
    }

    public function show(Banner $banner)
    {
        return redirect()->route('admin.banners.edit', $banner);
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $this->validated($request, $banner);
        $previousLink = $banner->button_link;

        try {
            DB::transaction(function () use ($request, $banner, $data) {
                $banner->update($data);
                $this->updateExistingImages($request, $banner);
                $this->storeImages($request, $banner);
                $this->deleteImages($request, $banner);
            });
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Save failed: '.$e->getMessage());
        }

        if ($banner->section === 'instagram_post') {
            $this->syncInstagramImage($banner->fresh('images'), $previousLink !== $banner->button_link);
        }

        return redirect(BannerSections::listUrl($banner->section))->with('success', BannerSections::isGallery($banner->section) ? 'Photo updated successfully.' : 'Banner updated successfully.');
    }

    public function destroy(Banner $banner)
    {
        foreach ($banner->images as $image) {
            $this->deleteImageFiles($image);
        }
        $section = $banner->section;
        $banner->delete();

        return redirect(BannerSections::listUrl($section))->with('success', BannerSections::isGallery($section) ? 'Photo deleted successfully.' : 'Banner deleted successfully.');
    }

    /** Admin → Gallery: rename a photo and/or move it to another gallery category, right from the list. */
    public function quickGallery(Request $request, Banner $banner)
    {
        abort_unless(BannerSections::isGallery($banner->section), 404);

        $data = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'section' => ['required', Rule::in(array_filter(BannerSections::keys(), [BannerSections::class, 'isGallery']))],
        ]);

        $moved = $data['section'] !== $banner->section;
        $banner->update([
            'title' => trim((string) ($data['title'] ?? '')),
            'section' => $data['section'],
        ]);

        return redirect(BannerSections::listUrl($banner->section))
            ->with('success', $moved ? 'Photo saved and moved to '.BannerSections::label($banner->section).'.' : 'Photo saved.');
    }

    public function toggleStatus(Banner $banner)
    {
        $banner->update(['is_active' => ! $banner->is_active]);

        return back()->with('success', 'Banner status updated.');
    }

    private function validated(Request $request, ?Banner $banner = null): array
    {
        // Media is required only for new banners in sections that display an image or video.
        $media = BannerSections::media((string) $request->input('section'));
        $imageRule = ($banner || $media === 'none' || $request->input('section') === 'instagram_post') ? ['nullable'] : ['required'];

        // Instagram posts: the admin pastes a post / reel link or the full embed code.
        $instagramPermalink = null;
        if ($request->input('section') === 'instagram_post') {
            $instagramPermalink = \App\Support\Instagram::permalink($request->input('instagram_embed'))
                ?? \App\Support\Instagram::permalink($request->input('button_link'));
            if (! $instagramPermalink) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'instagram_embed' => 'Please paste an Instagram post / reel link or its embed code.',
                ]);
            }
            if (trim((string) $request->input('title')) === '') {
                $request->merge(['title' => 'Instagram '.strtolower(\App\Support\Instagram::kind($instagramPermalink))]);
            }
        }

        $data = $request->validate([
            'title' => [BannerSections::isGallery($request->input('section')) ? 'nullable' : 'required', 'string', 'max:255'],
            'section' => ['required', Rule::in(BannerSections::keys())],
            'subtitle' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'button_link' => ['nullable', 'string', 'max:500'],
            'button_text_2' => ['nullable', 'string', 'max:100'],
            'button_link_2' => ['nullable', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'images' => array_merge($imageRule, ['array']),
            'images.*' => [
                'file',
                'mimes:png,jpg,jpeg,mp4,webm,ogg,mov',
                'max:51200',
                function (string $attribute, $value, \Closure $fail) use ($media) {
                    if ($media !== 'image_or_video' && str_starts_with((string) $value->getMimeType(), 'video/')) {
                        $fail('Videos can only be uploaded to the Home — Hero section.');
                    }
                },
            ],
            'mobile_images' => ['nullable', 'array'],
            'mobile_images.*' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:4096'],
            'image_titles' => ['nullable', 'array'],
            'image_titles.*' => ['nullable', 'string', 'max:255'],
            'image_subtitles' => ['nullable', 'array'],
            'image_subtitles.*' => ['nullable', 'string', 'max:255'],
            'image_button_texts' => ['nullable', 'array'],
            'image_button_texts.*' => ['nullable', 'string', 'max:100'],
            'image_button_links' => ['nullable', 'array'],
            'image_button_links.*' => ['nullable', 'string', 'max:500'],
            'existing_titles' => ['nullable', 'array'],
            'existing_subtitles' => ['nullable', 'array'],
            'existing_button_texts' => ['nullable', 'array'],
            'existing_button_links' => ['nullable', 'array'],
            'existing_sort' => ['nullable', 'array'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer'],
        ], [
            'images.required' => 'Please upload an image (or a video for the Home — Hero).',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        // Gallery photos may have no caption.
        $data['title'] = (string) ($data['title'] ?? '');
        if ($instagramPermalink) {
            $data['button_link'] = $instagramPermalink;
        }

        unset(
            $data['images'],
            $data['mobile_images'],
            $data['image_titles'],
            $data['image_subtitles'],
            $data['image_button_texts'],
            $data['image_button_links'],
            $data['existing_titles'],
            $data['existing_subtitles'],
            $data['existing_button_texts'],
            $data['existing_button_links'],
            $data['existing_sort'],
            $data['remove_images']
        );

        return $data;
    }

    private function storeImages(Request $request, Banner $banner): void
    {
        if (! $request->hasFile('images')) {
            return;
        }

        $sort = (int) $banner->images()->max('sort_order');
        $titles = $request->input('image_titles', []);
        $subtitles = $request->input('image_subtitles', []);
        $buttonTexts = $request->input('image_button_texts', []);
        $buttonLinks = $request->input('image_button_links', []);
        $mobileFiles = $request->file('mobile_images', []);

        foreach ($request->file('images') as $index => $file) {
            $sort++;
            $mobilePath = null;
            if (! empty($mobileFiles[$index])) {
                $mobilePath = $mobileFiles[$index]->store('banners/mobile', 'public');
            }

            $banner->images()->create([
                'image' => $file->store('banners', 'public'),
                'mobile_image' => $mobilePath,
                'title' => $titles[$index] ?? null,
                'subtitle' => $subtitles[$index] ?? null,
                'button_text' => $buttonTexts[$index] ?? null,
                'button_link' => $buttonLinks[$index] ?? null,
                'sort_order' => $sort,
                'is_active' => true,
            ]);
        }
    }

    private function updateExistingImages(Request $request, Banner $banner): void
    {
        $titles = $request->input('existing_titles', []);
        $subtitles = $request->input('existing_subtitles', []);
        $buttonTexts = $request->input('existing_button_texts', []);
        $buttonLinks = $request->input('existing_button_links', []);
        $sorts = $request->input('existing_sort', []);

        foreach ($banner->images as $image) {
            if (! array_key_exists($image->id, $titles) && ! array_key_exists($image->id, $sorts)) {
                continue;
            }

            $image->update([
                'title' => $titles[$image->id] ?? $image->title,
                'subtitle' => $subtitles[$image->id] ?? $image->subtitle,
                'button_text' => $buttonTexts[$image->id] ?? $image->button_text,
                'button_link' => $buttonLinks[$image->id] ?? $image->button_link,
                'sort_order' => isset($sorts[$image->id]) ? (int) $sorts[$image->id] : $image->sort_order,
            ]);
        }
    }

    private function deleteImages(Request $request, Banner $banner): void
    {
        $ids = $request->input('remove_images', []);
        if (! $ids) {
            return;
        }

        $images = $banner->images()->whereIn('id', $ids)->get();
        foreach ($images as $image) {
            $this->deleteImageFiles($image);
            $image->delete();
        }
    }

    private function deleteImageFiles(BannerImage $image): void
    {
        if ($image->image) {
            Storage::disk('public')->delete($image->image);
        }
        if ($image->mobile_image) {
            Storage::disk('public')->delete($image->mobile_image);
        }
    }

    /**
     * Instagram posts show the post's own image. Download it when the post is new, its link changed,
     * or it has no image yet — unless the admin uploaded a custom cover for this save.
     */
    private function syncInstagramImage(?Banner $banner, bool $linkChanged): void
    {
        if (! $banner || request()->hasFile('images')) {
            return;
        }
        $existing = $banner->images()->orderBy('sort_order')->first();
        if ($existing && ! $linkChanged) {
            return;
        }
        $path = \App\Support\Instagram::downloadThumbnail((string) $banner->button_link);
        if (! $path) {
            return;
        }
        if ($existing) {
            if ($existing->image !== $path && str_starts_with((string) $existing->image, 'banners/instagram/') === false) {
                $this->deleteImageFiles($existing);
            }
            $existing->update(['image' => $path, 'is_active' => true]);
        } else {
            $banner->images()->create(['image' => $path, 'is_active' => true, 'sort_order' => 1]);
        }
    }
}
