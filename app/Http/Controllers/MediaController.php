<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{

    public function media(Request $request, $path)
    {
        $fullPath = public_path($path);
        
        if (!file_exists($fullPath)) {
            abort(404, 'Media not found');
        }

        $mimeType = mime_content_type($fullPath);

        if (strpos($mimeType, 'video') !== false) {

            $fileSize = filesize($fullPath);
            $start = 0;
            $length = $fileSize;

            if (!$request->headers->has('Range')) {
                return response()->file($fullPath, [
                    'Content-Type' => mime_content_type($fullPath),
                    'Content-Length' => $fileSize,
                    'Accept-Ranges' => 'bytes',
                ]);
            }

            $range = $request->header('Range');
            preg_match('/bytes=(\d+)-(\d+)?/', $range, $matches);

            $start = intval($matches[1]);
            $end = isset($matches[2]) ? intval($matches[2]) : $fileSize - 1;
            $length = $end - $start + 1;

            $headers = [
                'Content-Type' => mime_content_type($fullPath),
                'Content-Length' => $length,
                'Accept-Ranges' => 'bytes',
                'Content-Range' => "bytes {$start}-{$end}/{$fileSize}",
                'Access-Control-Allow-Origin', '*',
                'Access-Control-Allow-Methods', 'GET, POST, OPTIONS',
            ];

            return new StreamedResponse(function () use ($fullPath, $start, $length) {
                $handle = fopen($fullPath, 'rb');
                fseek($handle, $start);
                echo fread($handle, $length);
                fclose($handle);
            }, 206, $headers);

        } else {
            return response()->file($fullPath);
        }
    }
}