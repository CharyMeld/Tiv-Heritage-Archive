# Tiv Heritage Archive

A digital preservation platform for Tiv language and culture — live at [tivheritage.com](https://tivheritage.com).

## What it does

- **Cultural archive** — proverbs, names, plants, festivals, foods, animals, historical figures, and a timeline of Tiv history, each with rich metadata and public detail pages
- **Local-LLM translation engine** — a Tiv ↔ English translation system built from a custom dictionary (with IPA, tone, and root/derived word data), grammar rules, and the alphabet, wired into a local Ollama model rather than an external API
- **Archive Intelligence** — a local RAG (retrieval-augmented generation) search/assistant layer over the archive's own content, so questions get answered from real archive data with no external AI API involved
- **AI content marketing engine** — generates on-brand social posts from archive content using the local model, with prompt templates, a review/approval pipeline, scheduling, image generation, and social publishing (Facebook/Instagram)
- **Community system** — member applications, contributions, and a suggestions/feedback loop
- **Outreach tooling** — an influential-persons database, nomination flow, and email campaign system used for real backlink/awareness outreach
- **SEO pipeline** — slugs, sitemap generation, structured metadata, and an image optimization pipeline

## Stack

PHP (custom MVC core, no framework), MySQL, vanilla JS, Ollama for all AI features (translation, RAG search, content generation) — deliberately zero dependency on external AI APIs.

## Notable engineering details

- The translation engine composes dictionary lookups, grammar rules, and alphabet/tone data rather than relying on a single black-box model call — see the translation services under `services/`.
- The marketing engine's content generator, scheduler, and social publishers run as scheduled CLI jobs (`bin/`) independent of the web app.
- `config/database.php` and any real API keys are excluded from version control; see `config/database.example.php` for the expected shape.
