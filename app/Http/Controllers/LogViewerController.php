<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;

class LogViewerController extends Controller
{
    public function index()
    {
        $logFile = storage_path('logs/laravel.log');
        $content = '';

        if (File::exists($logFile)) {
            $size = File::size($logFile);
            $maxSize = 500 * 1024;

            if ($size > $maxSize) {
                $handle = fopen($logFile, 'r');
                fseek($handle, -$maxSize, SEEK_END);
                fgets($handle);
                $content = fread($handle, $maxSize);
                fclose($handle);
            } else {
                $content = File::get($logFile);
            }
        }

        // Parse log entries
        $entries = $this->parseLogEntries($content);

        return view('logs.index', ['entries' => $entries]);
    }

    public function clear()
    {
        $logFile = storage_path('logs/laravel.log');
        File::put($logFile, '');

        return redirect('/logs');
    }

    private function parseLogEntries($content)
    {
        if (empty($content)) {
            return [];
        }

        // Split by log entry pattern
        $pattern = '/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.*?)(?=\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]|$)/s';
        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        $entries = [];
        foreach ($matches as $match) {
            $message = trim($match[4]);
            $tag = null;

            // Extract tag like [ThirdPartyInvoice]
            if (preg_match('/^\[([^\]]+)\]\s*(.*)$/s', $message, $tagMatch)) {
                $tag = $tagMatch[1];
                $message = $tagMatch[2];
            }

            // Try to extract JSON context - find JSON object at end of message
            $context = null;
            $mainMessage = $message;

            // Find position of first { that could start a JSON object
            if (preg_match('/^(.+?)\s+(\{.+\})\s*$/s', $message, $jsonMatch)) {
                $potentialJson = $jsonMatch[2];
                $decoded = json_decode($potentialJson, true);
                if ($decoded !== null) {
                    $context = $decoded;
                    $mainMessage = trim($jsonMatch[1]);
                }
            }

            $entries[] = [
                'timestamp' => $match[1],
                'environment' => $match[2],
                'level' => $match[3],
                'tag' => $tag,
                'message' => $mainMessage,
                'context' => $context,
            ];
        }

        return array_reverse($entries);
    }
}
