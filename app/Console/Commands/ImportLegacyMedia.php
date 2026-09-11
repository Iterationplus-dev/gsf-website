<?php

namespace App\Console\Commands;

use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

/**
 * Brings the organisation's own photographs and certificate scans out of the
 * legacy backup and into the media library.
 *
 * Imported files arrive unapproved and without alt text on purpose. Someone has
 * to look at each one and say what it shows, who is in it, and whether there is
 * consent to publish it — none of which can be inferred from a filename. Until
 * that happens the file is stored but never rendered.
 */
#[Signature('gsf:import-legacy-media {--path=backup/assets/images : Directory to import from} {--dry-run : List what would be imported without uploading}')]
#[Description('Import photographs and scans from the legacy website backup into the media library')]
class ImportLegacyMedia extends Command
{
    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /** Theme artwork shipped with the old template, not organisational media. */
    private const SKIP_PATTERNS = [
        'bear_donate', 'blog-post-home', 'events-img', 'charity1', 'favicon',
    ];

    public function handle(MediaService $media): int
    {
        $directory = base_path($this->option('path'));

        if (! is_dir($directory)) {
            $this->components->error('Directory not found: '.$directory);

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');

        $files = Finder::create()
            ->files()
            ->in($directory)
            ->name('/\.('.implode('|', self::EXTENSIONS).')$/i')
            ->sortByName();

        $imported = 0;
        $skipped = 0;

        foreach ($files as $file) {
            $title = 'Legacy: '.$file->getFilename();

            if ($this->shouldSkip($file) || Media::where('title', $title)->exists()) {
                $skipped++;

                continue;
            }

            if ($dryRun) {
                $this->line('  would import '.$file->getRelativePathname());
                $imported++;

                continue;
            }

            try {
                // `test: true` because the file comes from disk rather than an
                // HTTP upload, and there is no upload error status to check.
                $upload = new UploadedFile(
                    $file->getRealPath(),
                    $file->getFilename(),
                    mime_content_type($file->getRealPath()) ?: null,
                    null,
                    test: true,
                );

                $media->store($upload, 'legacy', [
                    'title' => $title,
                    'credit' => 'Global Support Foundation archive',
                ]);

                $imported++;
                $this->components->twoColumnDetail($file->getRelativePathname(), '<fg=green>imported</>');
            } catch (\Throwable $exception) {
                $skipped++;
                $this->components->twoColumnDetail($file->getRelativePathname(), '<fg=red>failed</>');
                report($exception);
            }
        }

        $this->newLine();
        $this->components->info(sprintf(
            '%s %d file(s); skipped %d.',
            $dryRun ? 'Would import' : 'Imported',
            $imported,
            $skipped,
        ));

        if ($imported > 0 && ! $dryRun) {
            $this->components->warn('Imported media is unapproved and has no alt text. Review each file in the administration panel, describe what it shows, confirm consent to publish images of identifiable people, and approve it before use.');
        }

        return self::SUCCESS;
    }

    private function shouldSkip(SplFileInfo $file): bool
    {
        return Str::contains(Str::lower($file->getFilename()), self::SKIP_PATTERNS);
    }
}
