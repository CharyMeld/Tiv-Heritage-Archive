TIV CULTURE ARCHIVE
Mobile-First PHP Website Design Specification 
1. Overview

The Tiv Culture Archive is a mobile-first PHP website built to preserve, document, and celebrate Tiv cultural heritage in a structured, authoritative, and accessible way.

The platform provides a searchable, admin-moderated archive of:

Tiv personal names

Proverbs and meanings

Indigenous plants

Festivals

Traditional foods

Tiv language basics

The website follows a black-and-white museum aesthetic, prioritizing smartphones first, while scaling seamlessly to tablets and desktop devices.

2. Core Concept

Concept Statement

A bold, timeless digital archive inspired by museums and cultural monuments — authoritative, respectful, and deeply rooted in Tiv heritage.

Design Character

Archival, not decorative

Serious but welcoming

Content takes precedence over visuals

Built for longevity and accuracy

3. Technology Stack (Recommended)
Backend

PHP 8+

MySQL / MariaDB

MVC-style structure
(Custom MVC or Laravel, depending on team skill)

Frontend

HTML5 (semantic)

CSS3 (mobile-first, Flexbox/Grid)

Vanilla JavaScript (lightweight, fast)

Optional: Alpine.js (micro-interactions only)

Hosting

Shared hosting compatible (Hostinger, Namecheap, etc.)

Apache or Nginx

No VPS required at launch

4. Design Philosophy (Web Adaptation)

Cultural Authority
Serif headings, archive-style layout, strong hierarchy

Accessibility
High contrast, readable fonts, inclusive design

Mobile-First
Designed for phones before desktop

Thumb-Friendly Navigation
Bottom navigation for primary actions

Content-First
Minimal decoration, maximum clarity

5. Website Structure
Public Pages

Home

Archive (Browse)

Detail Pages:

Names

Proverbs

Plants

Festivals

Foods

Contribute

Profile (optional login)

About / Contact

Admin Pages (Protected)

Admin Dashboard

Pending Submissions

Content Management

User & Role Management

6. Global Layout (Mobile-First)
┌──────────────────────────┐
│ Header (Logo + Menu ☰)   │
├──────────────────────────┤
│ Page Content             │
│ (Scrollable)             │
│                          │
├──────────────────────────┤
│ Bottom Navigation        │
│ Home | Archive | + | Me  │
└──────────────────────────┘

7. Page Specifications
7.1 Home Page (index.php)

Purpose
Discovery hub and cultural gateway.

Content

Site title: Tiv Culture Archive

Tagline: “Wisdom of the Tiv People”

Featured category cards:

Proverbs

Names

Plants

Festivals

Daily Tiv Word

Tiv word + English meaning

Auto-rotates every 10 seconds (JavaScript)

Call-to-Action buttons:

Explore Archive

Contribute Knowledge

7.2 Archive Page (archive.php)

Purpose
Browse and search all approved content.

Features

Category tabs:

Names

Proverbs

Plants

Festivals

Search input with real-time filtering

Infinite scroll or pagination

List items show:

Tiv term

English meaning (preview)

Category label

7.3 Detail Pages (detail.php)
Names

Tiv name (large serif)

English meaning

Gender category

Cultural / historical context

Related names

Share button

Proverbs

Tiv proverb (italic serif)

English translation

Meaning explanation

Context / usage

Cultural significance

Share button

Plants

Tiv name

English name

Uses (medicine, food, rituals)

Traditional knowledge

Optional images

7.4 Contribution Page (contribute.php)

Purpose
Enable Tiv people worldwide to contribute knowledge.

Form Fields

Category selector

Tiv term

English meaning

Description / history

Source (elder, book, region)

Optional contributor name

Workflow

Submission saved as pending

Message displayed:

“All submissions are reviewed and approved by Admin.”

7.5 Admin Dashboard (admin/)

Access

Login required

Admin-only

Features

Pending submissions count

Review actions:

Approve

Reject

Edit

Category-based content management

User and role management

8. Database Structure (Simplified)
users
- id
- name
- email
- role

submissions
- id
- category
- tiv_term
- english_meaning
- description
- status (pending / approved / rejected)
- created_at

tiv_names
tiv_proverbs
tiv_plants
tiv_festivals
tiv_foods

9. Navigation (Mobile)
Bottom Navigation

Home

Archive

Contribute (+)

Profile

Header

Logo / Title

Hamburger menu

10. Color System (Black & White Only)
Usage	Color
Background	#FFFFFF
Primary Text	#000000
Secondary Text	#444444
Borders	#E5E7EB
Cards	#F5F5F5

Optional Dark Mode

Background: #151718

Text: #ECEDEE

11. Typography (Web-Safe)
Element	Font
Headings	Georgia (serif)
Body	Segoe UI / system-ui
Tiv Words	Georgia (italic)
12. Accessibility

WCAG AAA contrast

Scalable text

Keyboard accessible

Screen-reader friendly

Semantic HTML structure

13. Performance Targets

Page load < 2 seconds

Search response < 500ms

Optimized images

Minimal JavaScript

14. Future Enhancements

Audio pronunciation (HTML5 audio)

Offline caching (PWA)

Browser notifications

Multilingual support

Image galleries

Elder oral history recordings

15. Branding

Name: Tiv Culture Archive

Tagline: Wisdom of the Tiv People

Logo: Black monogram or Tiv cultural symbol

Style: Museum / Archive / Cultural Authority

Final Positioning

This platform is:

A digital cultural monument

A community-supported knowledge base

An authoritative reference for Tiv heritage

Scalable into a national or academic archive
