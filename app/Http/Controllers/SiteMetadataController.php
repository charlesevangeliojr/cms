<?php

namespace App\Http\Controllers;

use App\Models\SiteMetadata;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteMetadataController extends Controller
{
    public function index()
    {
        return view('backend.metadata.index', ['metadata' => SiteMetadata::current()]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'site_name' => ['required', 'string', 'max:100', 'not_regex:/[<>]/'],
            'title' => ['required', 'string', 'max:150', 'not_regex:/[<>]/'],
            'description' => ['required', 'string', 'max:320', 'not_regex:/[<>]/'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'favicon' => ['nullable', 'file', 'mimes:png,ico', 'max:1024'],
            'keywords' => ['nullable', 'array', 'max:30'],
            'keywords.*' => ['nullable', 'string', 'max:80', 'not_regex:/[<>,]/'],
            'og_title' => ['nullable', 'string', 'max:150', 'not_regex:/[<>]/'],
            'og_description' => ['nullable', 'string', 'max:320', 'not_regex:/[<>]/'],
        ]);

        $metadata = SiteMetadata::current();
        $validated['keywords'] = collect($validated['keywords'] ?? [])
            ->map(fn ($keyword) => trim($keyword ?? ''))
            ->filter(fn ($keyword) => $keyword !== '')
            ->uniqueStrict()->values()->all();
        $uploaded = [];
        $replaced = [];

        try {
            foreach (['image', 'favicon'] as $field) {
                unset($validated[$field]);
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store('', 'metadata');
                    $uploaded[] = $path;
                    $replaced[] = $metadata->{$field};
                    $validated[$field] = 'uploads/metadata/'.$path;
                }
            }
            $metadata->fill($validated)->save();
        } catch (\Throwable $exception) {
            Storage::disk('metadata')->delete($uploaded);
            throw $exception;
        }

        foreach ($replaced as $path) {
            if (is_string($path) && str_starts_with($path, 'uploads/metadata/') && basename($path) === substr($path, strlen('uploads/metadata/'))) {
                Storage::disk('metadata')->delete(basename($path));
            }
        }

        return redirect()->route('seo-metadata.index')->with('success', 'SEO metadata saved successfully.');
    }
}
