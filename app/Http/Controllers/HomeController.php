<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

class HomeController extends Controller
{
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'avif', 'gif', 'svg'];

    public function __invoke(): View
    {
        return view('home', [
            'testimonials' => $this->testimonials(),
        ]);
    }

    /**
     * Every image in the configured testimonials folder, in natural file-name order. Placeholder
     * artwork never reaches the page, even if it's left in the folder.
     */
    private function testimonials(): Collection
    {
        $relative = trim(config('mosaic.testimonials_path'), '/');
        $directory = public_path($relative);

        if (! File::isDirectory($directory)) {
            return collect();
        }

        return collect(File::files($directory))
            ->filter(fn (SplFileInfo $file) => in_array(strtolower($file->getExtension()), self::IMAGE_EXTENSIONS))
            ->reject(fn (SplFileInfo $file) => str_starts_with(strtolower($file->getFilename()), 'placeholder'))
            ->sort(fn (SplFileInfo $a, SplFileInfo $b) => strnatcasecmp($a->getFilename(), $b->getFilename()))
            ->values()
            ->map(function (SplFileInfo $file, int $index) use ($relative) {
                [$width, $height] = $this->dimensions($file);

                return [
                    'src' => asset($relative.'/'.rawurlencode($file->getFilename())).'?v='.$file->getMTime(),
                    'alt' => 'Member testimonial '.($index + 1),
                    'width' => $width,
                    'height' => $height,
                ];
            });
    }

    /**
     * Intrinsic size of an image, so the wall can reserve space before it loads.
     *
     * @return array{0: int, 1: int}
     */
    private function dimensions(SplFileInfo $file): array
    {
        if (strtolower($file->getExtension()) === 'svg') {
            $svg = (string) file_get_contents($file->getPathname(), length: 2048);

            if (preg_match('/viewBox\s*=\s*["\'][\d.\-]+[\s,]+[\d.\-]+[\s,]+([\d.]+)[\s,]+([\d.]+)/i', $svg, $match)) {
                return [(int) round((float) $match[1]), (int) round((float) $match[2])];
            }

            return [9, 16];
        }

        $size = @getimagesize($file->getPathname());

        return $size ? [$size[0], $size[1]] : [9, 16];
    }
}
