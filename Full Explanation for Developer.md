# Full Explanation for Developer: Add Tiv Translation Engine to Existing Tiv Heritage App

We already have cultural content in the app, including:

- **Names** — 293
- **Proverbs** — 49
- **Plants** — 102
- **Festivals** — 8
- **Foods** — 15
- **Words** — 403
- **Animals** — 48

These existing records should become the **foundation data** for a new **Tiv Translation Engine v1**.

The goal is **not** to train a full AI model yet.

The goal is to build a **database-driven, rule-based Tiv ↔ English translation system** that works on **Hostinger shared hosting** using **PHP and MySQL**, and can later support **AI/API integration** if needed.

---

## 1. Main Idea

Instead of creating a completely separate translation system, the new translator should **reuse and expand the current app content**.

That means:

- **Words** should become the main dictionary source
- **Proverbs** should support proverb translation and meaning explanation
- **Plants, Foods, Festivals, Names, and Animals** should act as cultural vocabulary sources
- New phrase and translation rule tables should be added
- The translation engine should combine all these sources when translating user input

---

## 2. How the Existing Content Should Be Used

### Words
The **Words** section should become the core dictionary table for translation.

Each word entry should support:
- Tiv word
- English meaning
- alternate meaning
- part of speech
- example usage
- pronunciation
- category
- related words

If the current words table does not contain all of these fields, it should be upgraded.

### Proverbs
The **Proverbs** section should be used for:
- exact proverb translation
- literal translation
- cultural meaning
- interpretation/explanation

When a user enters a Tiv proverb or English equivalent, the system should search the proverb records first before trying word-by-word translation.

### Plants, Foods, Festivals, Names, and Animals
These should be treated as **special vocabulary categories** inside the translation system.

That means:
- if a user enters the name of a Tiv food, plant, festival, animal, or name, the translator should recognize it
- the translation result should include not just the literal English label, but also the category and meaning where available

Examples:
- a plant can return botanical or cultural meaning
- a food can return local context
- a festival can return meaning and usage context
- a name can return meaning and origin if available
- an animal can return the English equivalent, category, and symbolic meaning if available

So these sections should be searchable by the translation engine as category-based vocabulary sources.

---

## 3. What New Translation Features Should Be Added

The developer should add a new module called:

## Tiv Translation Engine v1

This module should support:

- Tiv to English translation
- English to Tiv translation
- exact phrase matching
- dictionary word matching
- word-by-word fallback translation
- proverb lookup
- category-aware translation using existing cultural records
- confidence score
- translation history
- payment-enabled access
- optional future API integration

---

## 4. How the Translation Engine Should Work

When a user submits text for translation, the engine should follow this order:

### Step 1: Normalize the Input
The input should be cleaned before matching:
- trim spaces
- normalize case
- remove unnecessary punctuation for search
- preserve original text for display

### Step 2: Check if Input Matches a Proverb
Search the existing **Proverbs** records first.

If found, return:
- translated version
- literal meaning
- actual meaning
- cultural explanation

This is important because proverbs should not be translated word by word first.

### Step 3: Check Exact Phrase Match
Add a new phrases table for common Tiv and English expressions such as:
- greetings
- common questions
- daily expressions
- common responses

This table should be checked before word-by-word translation.

### Step 4: Check Exact Word Match
Search the **Words** table for direct Tiv ↔ English word translation.

### Step 5: Search Category Content
If no exact word is found, search:
- Names
- Plants
- Foods
- Festivals
- Animals

These should behave like vocabulary extension tables.

Example:
If the user enters a Tiv festival name, food name, animal name, or plant name, the translator should return:
- English equivalent or explanation
- category
- meaning/context

### Step 6: Word-by-Word Fallback
If no exact phrase exists, split the sentence into tokens and search each token across:
- Words
- Names
- Plants
- Foods
- Festivals
- Animals

Then rebuild the translation using found matches.

### Step 7: Apply Translation Rules
After rebuilding a sentence, apply rule-based corrections for:
- phrase ordering
- common helper words
- known Tiv/English structure adjustments
- replacement of awkward literal translations

### Step 8: Assign Confidence Score
The system should score results based on match quality:
- exact proverb/phrase match = highest confidence
- direct dictionary match = high confidence
- category vocabulary match = medium/high confidence
- word-by-word rebuilt sentence = lower confidence

### Step 9: Save Translation Log
Store:
- input text
- output text
- source language
- target language
- engine used
- confidence score
- user id
- payment status
- timestamp

---

## 5. Recommended Database Approach

The developer should **reuse existing tables where possible** and only add what is missing.

### Existing Tables to Integrate
Use the current content tables as translation sources:

- words
- proverbs
- names
- plants
- foods
- festivals
- animals

### New Tables to Add

#### translation_phrases
For common sentence pairs and expressions.

Fields:
- id
- source_text
- source_language
- target_text
- target_language
- context_tag
- confidence_score
- status
- created_at

#### translation_rules
For grammar and phrase correction rules.

Fields:
- id
- rule_name
- source_language
- target_language
- pattern_text
- replacement_text
- rule_type
- priority_score
- status
- created_at

#### translation_logs
For all user translation activity.

Fields:
- id
- user_id
- source_text
- translated_text
- source_language
- target_language
- engine_used
- confidence_score
- payment_status
- created_at

#### translation_feedback
For user correction and quality improvement.

Fields:
- id
- translation_log_id
- user_id
- rating
- suggested_correction
- admin_review_status
- created_at

#### translation_payments or subscriptions
For monetization.

Fields:
- id
- user_id
- payment_ref
- amount
- payment_type
- status
- created_at

---

## 6. How Existing Heritage Modules Should Connect to Translation

The translation engine should not treat cultural modules as just display pages.

They should also act as **translation knowledge sources**.

### Words Module
Use for:
- direct dictionary lookup
- synonyms
- part of speech
- word examples

### Proverbs Module
Use for:
- exact proverb translation
- meaning explanation
- cultural interpretation

### Foods Module
Use for:
- food name translation
- food meaning
- category-aware explanation

### Plants Module
Use for:
- plant name translation
- English equivalent
- symbolic/cultural/medicinal notes where available

### Festivals Module
Use for:
- festival name translation
- significance
- contextual explanation

### Names Module
Use for:
- name meaning lookup
- translation-like interpretation
- origin and category support

### Animals Module
Use for:
- animal name translation
- English equivalent
- category classification where available
- symbolic meaning where available

This makes the translator smarter without needing AI training at launch.

---

## 7. Suggested Translation Engine Logic for Developer

The translation service should use a layered matching system.

### Match Priority
1. Proverbs
2. Exact phrases
3. Words
4. Category tables: foods, plants, festivals, names, animals
5. Word-by-word rebuild
6. Rule correction
7. Future optional API fallback

This priority order is important for quality.

---

## 8. Example Functional Behavior

### Example 1: Proverb Input
If the user enters a known Tiv proverb:
- system searches Proverbs table
- returns English equivalent
- also shows actual cultural meaning

### Example 2: Single Word Input
If the user enters a Tiv word:
- system searches Words table
- returns English translation
- shows category, pronunciation, and example if available

### Example 3: Cultural Item Input
If the user enters a food, festival, plant, name, or animal:
- system searches the relevant category table
- returns meaning/explanation
- shows cultural category

### Example 4: Sentence Input
If the user enters a sentence:
- search exact phrase first
- if not found, break into words
- search each word across all relevant tables
- rebuild sentence
- apply translation rules
- return best possible result with confidence score

---

## 9. Admin Requirements

The admin panel should be extended to include a **Translation Management** section.

This section should allow admin to:

- manage translation phrases
- manage translation rules
- review translation logs
- review failed translations
- approve user corrections
- convert approved corrections into words or phrases
- monitor which queries users search most

This will help the system improve continuously.

---

## 10. Payment-Enabled Access

The translation module should be monetized without requiring paid AI APIs.

Possible access model:

### Free Users
- limited daily translations
- short text only
- no advanced explanation for some results

### Paid Users
- more translations
- longer text input
- access to full explanations
- access to proverb meaning details
- access to saved history

The developer should integrate this with the existing user/payment system or add one if not yet present.

Recommended support:
- subscription plans
- credit system
- payment gateway integration like Paystack or Flutterwave

---

## 11. API-Optional Architecture

Even though version 1 should work without AI API costs, the structure should be future-ready.

The developer should build the engine so that:

- local database/rule-based translation runs first
- if confidence is low, the app can later call an external API
- API integration can be switched on later without changing the whole system

So the architecture should separate:
- translation engine
- data sources
- rule engine
- optional external API service

---

## 12. Developer Implementation Note

Because the app is on **Hostinger shared hosting**, the solution should stay lightweight and PHP/MySQL based.

Do not build version 1 as:
- Python model training
- GPU inference
- always-on AI server
- heavy NLP service on shared hosting

Instead, build:
- local translation engine in PHP
- MySQL-driven matching
- rule-based correction
- future API connector as optional service

---

## 13. Suggested Developer Task Breakdown

The developer should implement the work in this order:

### Phase 1: Data Preparation
- review existing tables for words, proverbs, names, foods, plants, festivals, and animals
- add missing fields needed for translation
- standardize Tiv and English values
- make these tables searchable by the translation engine

### Phase 2: New Translation Tables
- create translation_phrases
- create translation_rules
- create translation_logs
- create translation_feedback
- create payments/subscription support if needed

### Phase 3: Translation Service
- build a TranslationEngine service/class
- connect all existing heritage modules as searchable sources
- implement layered match priority
- implement confidence scoring
- save translation history

### Phase 4: Frontend
- create translation input page
- add source/target language selection
- display translation result
- display category and explanation
- add confidence badge
- add feedback button

### Phase 5: Admin Tools
- add phrase management
- add rule management
- add feedback review
- add logs/reports

### Phase 6: Monetization
- implement free and paid translation limits
- integrate payment
- connect plan/credit usage with translator access

---

## 14. Short Version to Send Directly to Developer

We already have heritage data in the app: Names, Proverbs, Plants, Festivals, Foods, Words, and Animals. I want you to build a **Tiv Translation Engine v1** on top of these existing modules instead of starting from scratch.

The translator should use:
- Words as the core dictionary
- Proverbs for proverb translation and meaning
- Plants, Foods, Festivals, Names, and Animals as category-based vocabulary sources

Please add a translation module that supports:
- Tiv ↔ English translation
- exact proverb match
- exact phrase match
- exact word match
- category-aware lookup from existing heritage records
- word-by-word fallback translation
- rule-based output correction
- confidence score
- translation logs
- user feedback/correction
- payment-enabled access
- optional future API integration

Please also add new tables for:
- translation phrases
- translation rules
- translation logs
- translation feedback
- translation payments/subscriptions

The system should work fully on PHP + MySQL on Hostinger shared hosting without requiring paid AI at launch.

---

## 15. Final Note

This version should be treated as **Tiv Translation Engine v1**.

It should not depend on paid AI APIs to function.

Instead, it should use:
- the existing Tiv heritage database
- new phrase mappings
- rule-based translation logic
- user feedback for continuous improvement

Later, if needed, an external AI API or custom translation model can be added as an optional fallback for low-confidence translations.

This approach is the most affordable, realistic, and scalable for the current hosting environment.
