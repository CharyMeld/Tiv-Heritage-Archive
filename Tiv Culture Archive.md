Good — archive-card-modern has base styles but no category-specific variants.
  I also need to check what CSS exists for auth-glass-title, auth-glass-sub,    
  auth-glass-logo, home-video-card, home-video-thumb, etc.:  




# Tiv Culture Archive

## Wisdom of the Tiv People

### UI / UX Design & Interface Specification

---

# 1. Project Vision

The **Tiv Culture Archive** is a digital cultural heritage platform designed to preserve and promote the traditions, language, and history of the Tiv people of Nigeria.

The platform should feel like a **modern digital museum** — clean, elegant, engaging, and easy to explore.

Design principles:

* Cultural authenticity
* Simplicity and clarity
* Visual storytelling
* Fast and mobile-friendly experience
* Accessibility for researchers, students, and the Tiv community

---

# 2. Global Design System

## Color Palette

Primary (Heritage Brown)

```
#5C3A21
```

Accent (Cultural Gold)

```
#C8A951
```

Background

```
#F7F4EE
```

Text

```
#1F1F1F
```

Status Colors

```
Success: #2E7D32
Error:   #C62828
Warning: #ED6C02
```

---

## Typography

Headings

```
Georgia, serif
```

Body Text

```
Segoe UI, system-ui, sans-serif
```

UI Labels

```
Inter, sans-serif
```

---

# 3. Navigation Structure

## Desktop Navigation

```
Home
Explore
Learn Tiv
Contribute
Community
About
Search
```

## Mobile Navigation

Bottom navigation bar:

```
Home | Explore | Learn | Profile | Menu
```

---

# 4. Complete UI Wireframe

## Homepage Wireframe

```
---------------------------------------------------
| Logo | Home | Explore | Learn | Contribute | 🔍 |
---------------------------------------------------

HERO SECTION
---------------------------------------------------
| Wisdom of the Tiv People                        |
| Preserving Language • Culture • Heritage        |
|                                                 |
| [Explore the Archive]  [Learn Tiv Language]     |
---------------------------------------------------

CATEGORY DISCOVERY
---------------------------------------------------
|  Tiv Names    |  Proverbs      |  Foods         |
|  Festivals    |  Plants        |  Dictionary    |
---------------------------------------------------

DAILY TIV WORD
---------------------------------------------------
| Word: Aondo                                     |
| Meaning: God                                    |
| Example: Aondo kpa u sha                        |
| [🔊 Listen]                                     |
---------------------------------------------------

FEATURED PROVERBS
---------------------------------------------------
| Card | Card | Card                               |
---------------------------------------------------

LEARNING VIDEOS
---------------------------------------------------
| Beginner | Intermediate | Advanced               |
---------------------------------------------------

FOOTER
---------------------------------------------------
About | Contact | Contribute | Privacy | GitHub
---------------------------------------------------
```

---

# 5. Archive Interface Structure

## Archive Category Page

```
---------------------------------------------------
| Category Title (Example: Tiv Proverbs)          |
---------------------------------------------------

SEARCH AND FILTER BAR
---------------------------------------------------
| Search field | Category filter | Sort dropdown  |
---------------------------------------------------

CONTENT GRID
---------------------------------------------------
| Proverb Card | Proverb Card | Proverb Card      |
| Proverb Card | Proverb Card | Proverb Card      |
---------------------------------------------------

PAGINATION
---------------------------------------------------
| Previous | 1 | 2 | 3 | Next                     |
---------------------------------------------------
```

---

## Archive Content Card

```
---------------------------------
Proverb Title

Translation:
A hardworking woman succeeds.

[Read More]

❤️ 45 views
---------------------------------
```

---

# 6. Content Detail Page

```
---------------------------------------------------
TITLE
Kwase u a lu kpa laa
---------------------------------------------------

TRANSLATION
A hardworking woman will succeed.

MEANING
Encourages diligence and perseverance.

CULTURAL CONTEXT
Used to praise hardworking women.

PRONUNCIATION
[🔊 Play Audio]

RELATED CONTENT
• Related proverb
• Related proverb
• Related proverb

SHARE
Facebook | Twitter | WhatsApp
---------------------------------------------------
```

---

# 7. Learning Section UI

```
---------------------------------------------------
Learn Tiv Language
---------------------------------------------------

LEVELS

Beginner
Basic greetings and words

Intermediate
Common expressions and proverbs

Advanced
Cultural storytelling and language mastery

---------------------------------------------------

VIDEO GRID

| Video | Video | Video |
| Video | Video | Video |
```

---

# 8. Contribution Interface

## Contribute Page

```
---------------------------------------------------
Submit Cultural Content
---------------------------------------------------

Select Category
[ Proverbs ▼ ]

Tiv Text
[____________________]

English Translation
[____________________]

Meaning
[____________________]

Audio Pronunciation
[Upload]  [Record]

[Submit Contribution]
---------------------------------------------------
```

---

# 9. Museum-Quality Archive Experience

To achieve a museum-grade digital archive, include the following elements:

## Visual Storytelling

* Cultural photography
* Traditional attire
* Tiv landscapes
* Festival imagery

## Contextual Information

Each item should include:

* Cultural significance
* Historical context
* Related entries
* Audio pronunciation where possible

## Cross-linking

Content should link to related items:

```
Proverb → Related Proverbs
Plant → Medicinal uses
Festival → Location + history
Food → Ingredients and cultural context
```

---

# 10. Visual Dashboard Design (Admin)

## Admin Dashboard Overview

```
---------------------------------------------------
ADMIN DASHBOARD
---------------------------------------------------

STATS
---------------------------------------------------
Total Archive Items        1,204
Pending Submissions        24
Registered Users           302
Videos Uploaded            86
---------------------------------------------------

CHARTS
---------------------------------------------------
Submissions per Month

Approved vs Rejected

Category Distribution
---------------------------------------------------

RECENT ACTIVITY
---------------------------------------------------
User A submitted a proverb
Moderator B approved entry
Admin updated site settings
---------------------------------------------------
```

---

## Submission Moderation Interface

```
---------------------------------------------------
Pending Submissions
---------------------------------------------------

| Title | Category | Author | Date | Actions |

Actions:
Approve
Reject
View
---------------------------------------------------
```

---

# 11. UX Enhancements

## Micro Interactions

Add subtle interactions such as:

* Card hover animations
* Smooth transitions
* Audio waveform animations
* Skeleton loading placeholders

---

## Performance Optimization

Best practices:

* Lazy-load images
* Compress audio files
* Use CSS variables for themes
* Cache API responses

---

# 12. Progressive Web App (PWA)

To enable installable app behavior:

Required files:

```
manifest.json
service-worker.js
```

Benefits:

* Installable on phones
* Offline viewing
* Faster performance

---

# 13. SEO Structure

Recommended meta tags:

```
Open Graph metadata
Twitter cards
Structured data (schema.org)
```

Example schema types:

```
CreativeWork
Article
Dataset
```

---

# 14. Future Enhancements

Possible upgrades for the platform:

* Tiv oral storytelling recordings
* Cultural map of Tivland
* Advanced search ranking
* AI-powered translation assistance
* Community discussions
* Bookmarking system
* Research citation tools

---

# 15. Final Vision

The Tiv Culture Archive should evolve into a **global digital heritage platform** preserving Tiv language, knowledge, and traditions for future generations.

The platform will serve:

* Tiv communities
* Cultural researchers
* Linguists
* Students
* Historians

By combining **modern UX design with cultural authenticity**, the archive becomes a **living digital museum of Tiv heritage**.

---

