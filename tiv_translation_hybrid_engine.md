# Hybrid Tiv → English Translation Engine (PHP, No Model Needed)

This guide explains how to build a Tiv → English translation engine in PHP using a hybrid method: **phrase lookup, dictionary lookup, and simple grammar rules**.

---

## 1️⃣ Database Structure

### a) `translation_phrases`
For multi-word phrases.

| id | tiv_text           | english_text                 |
|----|------------------|-----------------------------|
| 1  | M ngu zan kasua    | I am going to the market    |
| 2  | Ngu yaven        | He is sleeping              |
| 3  | Saan mo iyol       | I am happy                  |

### b) `translation_dictionary`
For single-word fallback.

| id | tiv_word | english_word |
|----|----------|--------------|
| 1  | M        | I            |
| 2  | ngu      | am           |
| 3  | kasua    | market       |
| 4  | zan      | going        |
| 5  | Msaaniyol| happy        |

---

## 2️⃣ Translation Workflow

1. Check for **exact phrase match** in `translation_phrases`.
2. If no match, fallback to **word-by-word translation** from `translation_dictionary`.
3. Apply **grammar rules** (word order, auxiliary verbs, articles, tenses).
4. Output the final English sentence.

---

## 3️⃣ PHP Function Example

```php
function translateTiv($tiv_sentence, $db) {
    // Normalize input
    $tiv_sentence = mb_strtolower(trim($tiv_sentence), 'UTF-8');

    // Step 1: Exact phrase match
    $phrase_sql = "SELECT english_text FROM translation_phrases WHERE tiv_text = ?";
    $stmt = $db->prepare($phrase_sql);
    $stmt->execute([$tiv_sentence]);
    $phrase_result = $stmt->fetchColumn();
    if ($phrase_result) return $phrase_result;

    // Step 2: Word-by-word translation
    $words = explode(' ', $tiv_sentence);
    $translated_words = [];
    foreach ($words as $word) {
        $dict_sql = "SELECT english_word FROM translation_dictionary WHERE tiv_word = ?";
        $stmt = $db->prepare($dict_sql);
        $stmt->execute([$word]);
        $english_word = $stmt->fetchColumn();
        $translated_words[] = $english_word ?: $word;
    }

    $english_sentence = implode(' ', $translated_words);

    // Step 3: Apply grammar rules
    $english_sentence = applyGrammarRules($english_sentence);

    return $english_sentence;
}
```

---

## 4️⃣ Grammar Rules Function

```php
function applyGrammarRules($sentence) {
    $patterns = [
        '/\bI go market\b/u' => 'I am going to the market',
        '/\bSaan mo iyol\b/u' => 'I am happy',
        '/\bNgu yaven\b/u' => 'He is sleeping'
    ];

    foreach ($patterns as $pattern => $replacement) {
        $sentence = preg_replace($pattern, $replacement, $sentence);
    }

    // Capitalize first letter
    $sentence = ucfirst($sentence);

    return $sentence;
}
```

> **Tip:** Expand patterns gradually as you collect more common Tiv phrases.

---

## 5️⃣ Developer Best Practices

- **Sort phrases by length**: match longer phrases first.
- **Use regex boundaries**: avoid partial matches breaking translation.
- **Cache frequent translations**: improve performance.
- **Fallback gracefully**: leave unknown words as-is.
- **Iteratively expand grammar rules**: cover most common sentence patterns.

---

## 6️⃣ Outcome

- Sentences read naturally instead of jumbled word-by-word translation.
- Works **entirely in PHP**, no GPU or ML model required.
- Can later integrate a small ML model for rare or complex sentences.

---

This hybrid engine is **suitable for low-resource languages like Tiv**, producing usable translations immediately while remaining expandable over time.

