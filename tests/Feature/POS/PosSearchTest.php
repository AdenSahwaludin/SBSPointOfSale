<?php
$nama = 'Minyak Gandarpura Cap Daun 60 mL';
$sku = 'SKU001';
$kategori = 'Herbal';
$barcode = '';
$searchTerm = 'http://127.0.0.1:8000';

$score = 0;
$matched = false;

// 1. Exact match
if ($barcode === $searchTerm) { $score += 1000; $matched = true; }
if ($sku === $searchTerm) { $score += 900; $matched = true; }
if ($nama === $searchTerm) { $score += 800; $matched = true; }

// 2. Starts with
if (str_starts_with($barcode, $searchTerm)) { $score += 700; $matched = true; }
if (str_starts_with($sku, $searchTerm)) { $score += 600; $matched = true; }
if (str_starts_with($nama, $searchTerm)) { $score += 500; $matched = true; }

// 3. Contains
if (str_contains($barcode, $searchTerm)) { $score += 400; $matched = true; }
if (str_contains($sku, $searchTerm)) { $score += 300; $matched = true; }
if (str_contains($nama, $searchTerm)) { $score += 200; $matched = true; }
if (str_contains($kategori, $searchTerm)) { $score += 50; }

// 4. Word-by-word
$searchWords = array_filter(explode(' ', $searchTerm));
foreach ($searchWords as $word) {
    if (strlen($word) > 2) {
        if (str_contains($nama, $word)) { $score += 60; $matched = true; }
        if (str_contains($sku, $word)) { $score += 40; $matched = true; }
    }
}

// 5. Fuzzy
if (!$matched && strlen($searchTerm) > 3) {
    $nameWords = array_filter(explode(' ', $nama));
    foreach ($nameWords as $nameWord) {
        if (strlen($nameWord) > 3) {
            $percent = 0.0;
            similar_text($searchTerm, $nameWord, $percent);
            if ($percent >= 70) {
                $score += (int) round($percent);
                $matched = true;
                break;
            }
        }
    }
}

echo "Score: $score, Matched: " . ($matched ? 'true' : 'false') . PHP_EOL;
