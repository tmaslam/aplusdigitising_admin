<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Attachment;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use ZipArchive;

class BulkZipDownload
{
    /**
     * Build a ZIP from a collection of attachments and return a download Response.
     *
     * @param  Collection<int, Attachment>  $attachments
     * @param  string  $zipName  Base filename for the ZIP (without .zip)
     * @param  callable(Attachment): string|null  $pathFormatter  Optional fn to build entry path inside ZIP
     * @return Response
     */
    /**
     * Check if any attachment in the collection has a physical file on disk.
     *
     * @param  Collection<int, Attachment>  $attachments
     */
    public static function hasExistingFiles(Collection $attachments): bool
    {
        foreach ($attachments as $attachment) {
            if (is_file(CustomerAttachmentAccess::absolutePath($attachment))) {
                return true;
            }
        }
        return false;
    }

    public static function build(Collection $attachments, string $zipName, ?callable $pathFormatter = null): Response
    {
        $zipPath = self::tempZipPath();
        $zip = new ZipArchive();

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Unable to create ZIP archive.');
        }

        $added = 0;
        foreach ($attachments as $attachment) {
            $fullPath = CustomerAttachmentAccess::absolutePath($attachment);
            if (! is_file($fullPath)) {
                continue;
            }

            $entryName = $pathFormatter
                ? $pathFormatter($attachment)
                : self::defaultEntryName($attachment);

            if ($entryName === null) {
                continue;
            }

            // Avoid duplicate entry names
            $uniqueEntryName = self::makeUniqueEntryName($zip, $entryName);
            $zip->addFile($fullPath, $uniqueEntryName);
            $added++;
        }

        $zip->close();

        if ($added === 0) {
            @unlink($zipPath);
            abort(404, 'No files were found for the selected criteria.');
        }

        $response = response()->download($zipPath, self::safeFileName($zipName) . '.zip', [
            'Content-Type' => 'application/zip',
            'X-Content-Type-Options' => 'nosniff',
        ]);

        // Delete temp file after response is sent
        $response->deleteFileAfterSend(true);

        return $response;
    }

    private static function tempZipPath(): string
    {
        $dir = storage_path('framework/cache');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $dir . '/bulk-download-' . uniqid('', true) . '.zip';
    }

    private static function defaultEntryName(Attachment $attachment): string
    {
        $fileName = (string) ($attachment->file_name ?: basename((string) $attachment->file_name_with_date));

        return $fileName !== '' ? $fileName : 'file-' . $attachment->id;
    }

    private static function makeUniqueEntryName(ZipArchive $zip, string $name): string
    {
        if ($zip->locateName($name) === false) {
            return $name;
        }

        $info = pathinfo($name);
        $base = $info['filename'];
        $ext = isset($info['extension']) ? '.' . $info['extension'] : '';
        $dir = ($info['dirname'] ?? '.') !== '.' ? $info['dirname'] . '/' : '';

        $counter = 1;
        do {
            $candidate = $dir . $base . '-' . $counter . $ext;
            $counter++;
        } while ($zip->locateName($candidate) !== false);

        return $candidate;
    }

    private static function safeFileName(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9._-]/', '-', $name);
    }
}
