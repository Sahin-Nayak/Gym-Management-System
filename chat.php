<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!isset($input['message']) || empty(trim($input['message']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Message is required']);
    exit;
}

$userMessage         = trim($input['message']);
$conversationHistory = isset($input['history']) ? $input['history'] : [];

$groqApiKey = ''; // <-- REPLACE WITH YOUR GROQ API KEY
// ============================================================

// ─── SYSTEM PROMPT ───────────────────────────────────────────
$systemPrompt = "You are FitFuel AI, an expert diet and nutrition consultant built into a Gym Management System.\n\n"
. "CORE EXPERTISE:\n"
. "- Diet & Nutrition: macros, micros, caloric needs, TDEE, meal timing, hydration\n"
. "- Gym Diet Plans: bulking, cutting, body-recomposition, high-protein, endurance\n"
. "- Meal Plans: Indian, Western, Keto, Vegan, Vegetarian, budget-friendly\n"
. "- Calculations: BMI, BMR, protein targets, caloric intake\n"
. "- Special Diets: Keto, Intermittent Fasting, Vegan bodybuilding, Gluten-free\n"
. "- Supplements: whey, casein, creatine, BCAAs, pre-workouts, vitamins\n\n"
. "PERSONALITY:\n"
. "- Friendly, motivating, encouraging; use relevant emojis\n"
. "- Give specific, actionable advice with bullet points\n"
. "- Format meal plans as neat bullet lists\n"
. "- Ask clarifying questions when needed (goals, weight, dietary restrictions)\n\n"
. "CONFIDENCE SIGNALLING — end EVERY reply with exactly one of these tags on its own line:\n"
. "  [CONFIDENT]\n"
. "  [SEARCH_NEEDED: <3-6 word query>]\n\n"
. "Use [SEARCH_NEEDED] when:\n"
. "- Asked about specific brands, products, or recent news\n"
. "- The topic is niche or rapidly changing\n"
. "- User says 'search', 'look it up', or 'find online'\n\n"
. "For medical conditions always advise consulting a healthcare professional.\n"
. "Only discuss diet, nutrition, fitness, and supplement topics.";

// ─── HELPER: Call Groq API (FREE) ────────────────────────────
// Uses llama-3.3-70b — very powerful, completely free on Groq
function callGroq(string $apiKey, string $systemPrompt, array $history, string $userMsg): array {

    // Build messages array (OpenAI-compatible format)
    $messages = [['role' => 'system', 'content' => $systemPrompt]];

    foreach ($history as $msg) {
        if (!isset($msg['role'], $msg['content'])) continue;
        $messages[] = ['role' => $msg['role'], 'content' => $msg['content']];
    }
    $messages[] = ['role' => 'user', 'content' => $userMsg];

    $body = json_encode([
        'model'       => 'llama-3.3-70b-versatile',
        'messages'    => $messages,
        'max_tokens'  => 1024,
        'temperature' => 0.7,
    ]);

    $ch = curl_init('https://api.groq.com/openai/v1/chat/completions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $body,
        CURLOPT_TIMEOUT        => 30,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $apiKey,
        ],
    ]);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) return ['error' => 'Connection error: ' . $curlError];

    $data = json_decode($response, true);

    if ($httpCode !== 200) {
        $msg = $data['error']['message'] ?? 'Groq API error (HTTP ' . $httpCode . ')';
        return ['error' => $msg];
    }

    $text = $data['choices'][0]['message']['content'] ?? '';
    if (empty($text)) return ['error' => 'Empty response from Groq'];

    return ['text' => $text];
}

// ─── HELPER: DuckDuckGo Search (FREE, no key needed) ─────────
function duckDuckGoSearch(string $query): array {
    $url = 'https://api.duckduckgo.com/?' . http_build_query([
        'q'             => $query,
        'format'        => 'json',
        'no_html'       => 1,
        'skip_disambig' => 1,
    ]);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_USERAGENT      => 'FitFuelAI-Chatbot/1.0',
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTPHEADER     => ['Accept: application/json'],
    ]);

    $response  = curl_exec($ch);
    $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError || $httpCode !== 200 || empty($response)) return [];

    $data    = json_decode($response, true);
    $results = [];

    // Main abstract
    if (!empty($data['AbstractText'])) {
        $results[] = [
            'title'   => $data['Heading'] ?? $query,
            'snippet' => $data['AbstractText'],
            'link'    => $data['AbstractURL'] ?? 'https://duckduckgo.com/?q=' . urlencode($query),
            'source'  => $data['AbstractSource'] ?? 'DuckDuckGo',
        ];
    }

    // Related topics
    foreach (($data['RelatedTopics'] ?? []) as $topic) {
        if (count($results) >= 5) break;
        if (isset($topic['Text'], $topic['FirstURL'])) {
            $results[] = [
                'title'   => $topic['Name'] ?? substr($topic['Text'], 0, 60),
                'snippet' => $topic['Text'],
                'link'    => $topic['FirstURL'],
                'source'  => 'DuckDuckGo',
            ];
        }
        if (isset($topic['Topics'])) {
            foreach ($topic['Topics'] as $sub) {
                if (count($results) >= 5) break;
                if (isset($sub['Text'], $sub['FirstURL'])) {
                    $results[] = [
                        'title'   => substr($sub['Text'], 0, 60),
                        'snippet' => $sub['Text'],
                        'link'    => $sub['FirstURL'],
                        'source'  => 'DuckDuckGo',
                    ];
                }
            }
        }
    }

    // Quick answer box fallback
    if (empty($results) && !empty($data['Answer'])) {
        $results[] = [
            'title'   => 'Quick Answer',
            'snippet' => $data['Answer'],
            'link'    => 'https://duckduckgo.com/?q=' . urlencode($query),
            'source'  => 'DuckDuckGo',
        ];
    }

    return array_slice($results, 0, 5);
}

// ─── STEP 1: Ask Groq ────────────────────────────────────────
$groqResult = callGroq($groqApiKey, $systemPrompt, $conversationHistory, $userMessage);

if (isset($groqResult['error'])) {
    echo json_encode(['error' => $groqResult['error']]);
    exit;
}

$groqText = $groqResult['text'];

// ─── STEP 2: Parse confidence tag ────────────────────────────
$needsSearch = false;
$searchQuery = '';
$cleanText   = $groqText;

if (preg_match('/\[SEARCH_NEEDED:\s*(.+?)\]\s*$/s', $groqText, $m)) {
    $needsSearch = true;
    $searchQuery = trim($m[1]);
    $cleanText   = trim(preg_replace('/\[SEARCH_NEEDED:\s*.+?\]\s*$/', '', $groqText));
} elseif (preg_match('/\[CONFIDENT\]\s*$/s', $groqText)) {
    $cleanText = trim(preg_replace('/\[CONFIDENT\]\s*$/', '', $groqText));
}

// ─── STEP 3: DuckDuckGo search (if needed) ───────────────────
$searchResults   = [];
$searchPerformed = false;

if ($needsSearch) {
    $fullQuery     = $searchQuery . ' diet nutrition gym fitness';
    $searchResults = duckDuckGoSearch($fullQuery);
    $searchPerformed = true;

    // ─── STEP 4: Groq synthesises the results ────────────────
    if (!empty($searchResults)) {
        $snippets = '';
        foreach ($searchResults as $i => $r) {
            $snippets .= ($i + 1) . ". [{$r['title']}]: {$r['snippet']}\n";
        }

        $synthMsg = "The user asked: \"{$userMessage}\"\n\n"
                  . "Web search results for \"{$searchQuery}\":\n{$snippets}\n\n"
                  . "Using these results AND your own knowledge, write a clear, helpful, well-structured answer. "
                  . "Use emojis and bullet points. Do NOT add any [CONFIDENT] or [SEARCH_NEEDED] tag.";

        $synthResult = callGroq($groqApiKey, $systemPrompt, [], $synthMsg);
        if (!isset($synthResult['error']) && !empty($synthResult['text'])) {
            // Strip any accidental tags from synthesis
            $synthText = preg_replace('/\[(CONFIDENT|SEARCH_NEEDED)[^\]]*\]\s*$/', '', $synthResult['text']);
            $cleanText = trim($synthText);
        }
    } else {
        $cleanText .= "\n\n🔍 *I searched online but couldn't find additional results. The answer above is based on my knowledge.*";
    }
}

// ─── RESPONSE ────────────────────────────────────────────────
echo json_encode([
    'success'        => true,
    'message'        => $cleanText,
    'role'           => 'assistant',
    'searched'       => $searchPerformed,
    'search_query'   => $searchQuery,
    'search_results' => $searchResults,
]);