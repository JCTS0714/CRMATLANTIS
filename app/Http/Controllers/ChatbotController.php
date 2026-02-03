<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class ChatbotController extends BaseController
{
    /**
     * Simple POC: search `docs/` for query terms and return top snippets.
     * POST /api/chatbot/query { q: string }
     */
    public function query(Request $request)
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'max:1000'],
        ]);
        $query = $validated['q'];

        // If a FAISS index exists (built by scripts/index_docs.py), use the Python query tool.
        $scriptsDir = base_path('scripts') . DIRECTORY_SEPARATOR;
        $userIndexPath = $scriptsDir . 'index_user.faiss';
        $indexPath = $scriptsDir . 'index.faiss';
        $pyScript = base_path('scripts') . DIRECTORY_SEPARATOR . 'query_docs.py';

        // prefer user-only index if present
        $useScript = $pyScript;
        if (file_exists($userIndexPath)) {
            $useIndex = $userIndexPath;
        } else {
            $useIndex = $indexPath;
        }

        if (file_exists($useIndex) && file_exists($useScript)) {
            $python = env('PYTHON', 'python');
            $escapedQuery = escapeshellarg($query);
            $escapedScript = escapeshellarg($useScript);
            $escapedK = escapeshellarg('5');
            $escapedIndex = escapeshellarg($useIndex);
            // metadata path: index stem + '_metadata.json'
            $metaPath = preg_replace('/\.faiss$/', '_metadata.json', $useIndex);
            $escapedMeta = escapeshellarg($metaPath);
            // call: query_docs.py "query" k index_path meta_path
            $cmd = $python . ' ' . $escapedScript . ' ' . $escapedQuery . ' ' . $escapedK . ' ' . $escapedIndex . ' ' . $escapedMeta;

            try {
                $output = shell_exec($cmd);
                if ($output) {
                    $data = json_decode($output, true);
                    if (is_array($data) && isset($data['results'])) {
                        // Build a single, user-friendly answer by concatenating top snippets
                        $snippets = array_map(function($it){ return preg_replace('/\s+/', ' ', trim(strip_tags($it['snippet']))); }, array_slice($data['results'], 0, 3));
                        $answer = implode("\n\n", $snippets);
                        // shorten to a reasonable length for a single reply
                        if (mb_strlen($answer) > 800) {
                            $answer = mb_substr($answer, 0, 780) . '...';
                        }
                        return response()->json(['answer' => $answer, 'data' => $data['results']]);
                    }
                }
            } catch (\Exception $e) {
                // fall through to simple search fallback
            }
        }

        // Fallback: simple token search across docs (original POC)
        $qLower = mb_strtolower($query);
        $tokens = preg_split('/\P{L}+/u', $qLower, -1, PREG_SPLIT_NO_EMPTY);
        $tokens = array_filter(array_map('trim', $tokens));
        if (count($tokens) === 0) {
            return response()->json(['data' => []]);
        }

        $docsDir = base_path('docs');
        $files = [];
        if (is_dir($docsDir)) {
            $it = new \DirectoryIterator($docsDir);
            foreach ($it as $fileinfo) {
                if ($fileinfo->isFile()) {
                    $ext = strtolower($fileinfo->getExtension());
                    if (in_array($ext, ['md', 'txt', 'html'])) {
                        $files[] = $fileinfo->getPathname();
                    }
                }
            }
        }

        $results = [];
        foreach ($files as $f) {
            $content = mb_strtolower(file_get_contents($f));

            $score = 0;
            foreach ($tokens as $t) {
                if (mb_strlen($t) < 2) continue;
                $score += substr_count($content, $t);
            }

            if ($score <= 0) continue;

            // extract snippet around first match
            $firstPos = null;
            foreach ($tokens as $t) {
                $pos = mb_strpos($content, $t);
                if ($pos !== false) {
                    $firstPos = $pos;
                    break;
                }
            }

            if ($firstPos === null) continue;

            $start = max(0, $firstPos - 120);
            $snippet = mb_substr($content, $start, 240);
            // clean up whitespace
            $snippet = preg_replace('/\s+/', ' ', $snippet);

            $results[] = [
                'file' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $f),
                'score' => $score,
                'snippet' => $snippet,
            ];
        }

        usort($results, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $top = array_slice($results, 0, 5);

        // Synthesize a single natural answer from top snippet(s)
        if (!empty($top)) {
            $snippets = array_map(function($it){ return preg_replace('/\s+/', ' ', trim($it['snippet'])); }, array_slice($top, 0, 2));
            $answer = implode("\n\n", $snippets);
            if (mb_strlen($answer) > 800) {
                $answer = mb_substr($answer, 0, 780) . '...';
            }
            return response()->json(['answer' => $answer, 'data' => $top]);
        }

        return response()->json(['data' => $top]);
    }
}
