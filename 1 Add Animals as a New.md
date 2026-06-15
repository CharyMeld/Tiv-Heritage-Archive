🔷 1. Add Animals as a New Content Table

Since you already have:

tiv_proverbs

tiv_foods

tiv_plants

tiv_festivals

tiv_words

tiv_names

👉 Just add:

CREATE TABLE tiv_animals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    tiv_name VARCHAR(255),
    description TEXT,
    cultural_use TEXT,
    source_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
🔷 2. No Change Needed to knowledge_links ✅

This is the beauty of your system 👇

knowledge_links
- source_table
- source_id
- target_table
- target_id
- relation_type

👉 It already supports ANY table, so animals will plug in naturally.

🔷 3. Add Animal Relationships

Now you can create powerful connections like:

🐐 Example: Goat
Animal → used_in → Food
Animal → used_in → Festival
Animal → appears_in → Proverb
Animal → symbol_of → Cultural meaning
Example SQL entries:
INSERT INTO knowledge_links 
(source_table, source_id, relation_type, target_table, target_id)
VALUES
('tiv_animals', 1, 'used_in_food', 'tiv_foods', 3),
('tiv_animals', 1, 'used_in_festival', 'tiv_festivals', 2),
('tiv_animals', 1, 'mentioned_in_proverb', 'tiv_proverbs', 12);
🔷 4. Update Source Tracking (IMPORTANT)

Add source_id just like others:

ALTER TABLE tiv_animals ADD COLUMN source_id INT;
🔷 5. Update Your UI Categories

Now simply add Animals to your category list:

$categories = [
    'proverbs',
    'names',
    'plants',
    'festivals',
    'foods',
    'animals' // ✅ added
];
🔷 6. Animal Page = Same Pattern as Others
Example: Animal Page (Goat)
🐐 Goat (Ibyer)

Description:
A commonly used domestic animal in Tiv culture.

Cultural Use:
Used in food, marriage rites, and sacrifices.

🔗 Related Cultural Knowledge

🍲 Foods: Goat Meat Pepper Soup

🎭 Festivals: Traditional Marriage

📜 Proverbs: “A goat does not forget its owner”

👉 This uses the SAME logic:

SELECT *
FROM knowledge_links
WHERE source_table='tiv_animals'
AND source_id=?;
🔷 7. Reverse Linking (VERY IMPORTANT)

Also allow:

Proverb → mentions → Animal
Food → contains → Animal
Festival → uses → Animal

👉 Example:

('tiv_proverbs', 12, 'mentions_animal', 'tiv_animals', 1)
🔷 8. Update “Related Knowledge” Section (Small Upgrade)

Right now you show:

Foods

Festivals

Proverbs

👉 Add:

🐐 Animals related to this item

So now every page can show animals too.

🔷 9. Admin Panel Update

In your graph linking form, just include:

Source Item Dropdown:

Proverbs

Foods

Plants

Festivals

Words

Names

Animals ✅ NEW

Target Item Dropdown:

Same list (including animals)

🔷 10. Example Full Knowledge Flow (Now Stronger)

User journey becomes:

Proverb → Animal → Food → Festival → Plant

Example:

“A goat does not forget its owner”
        ↓
Animal: Goat
        ↓
Food: Goat Pepper Soup
        ↓
Festival: Marriage Rite
        ↓
Plant: Spices used

🔥 This is now a TRUE knowledge graph.

🔷 11. Why Your System Still Works Perfectly

You don’t need to redesign anything because:

✅ knowledge_links is dynamic
✅ source_id already exists
✅ UI pattern already built
✅ Only adding one table + UI entry

🔷 12. Final Summary

👉 What you add:

tiv_animals table ✅

Add animals to UI categories ✅

Use knowledge_links (no change) ✅

Add relationships involving animals ✅

If you want next step, I can help you:

✅ Build animals.php (listing + details page)
✅ Create admin form to add animals
✅ Auto-fetch related knowledge (PHP code)
✅ Add icons + UI styling for Animals section

Just say:
👉 “Let’s implement animals UI and backend step by step”

so i only want you to give me explanation for my developer to add it

Got it 👍 — here’s a clear, professional explanation you can give directly to your developer.

🧩 Adding “Animals” to Tiv Cultural Knowledge Graph (Developer Guide)
1. Objective

We want to add “Animals” as a new content type to the Tiv Cultural Knowledge Graph.

It should:

Appear as a new category in the UI

Behave like existing entities (Proverbs, Foods, Plants, Festivals, Names, Words)

Be fully integrated into the knowledge graph (knowledge_links table)

2. Database Changes
✅ Create New Table: tiv_animals

Add a new table following the same structure pattern as existing content tables:

CREATE TABLE tiv_animals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255),
    tiv_name VARCHAR(255),
    description TEXT,
    cultural_use TEXT,
    source_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
✅ Add Source Support

Ensure source_id works the same way as other tables:

ALTER TABLE tiv_animals ADD COLUMN source_id INT;

This links animals to the existing sources table.

3. Knowledge Graph Integration (No Structural Change Needed)

The existing knowledge_links table already supports dynamic linking:

knowledge_links (
    source_table,
    source_id,
    target_table,
    target_id,
    relation_type
)

👉 No modification required.

4. Supported Relationships for Animals

Animals must participate in the graph like other entities.

Examples:

Animal → used_in_food → Food

Animal → used_in_festival → Festival

Animal → mentioned_in_proverb → Proverb

Animal → related_to_plant → Plant

Proverb → mentions_animal → Animal

Food → contains_animal → Animal

👉 These should be stored in knowledge_links.

5. Admin Panel Update

Update the knowledge graph linking interface:

Source Item Dropdown:

Include:

tiv_proverbs

tiv_foods

tiv_plants

tiv_festivals

tiv_words

tiv_names

tiv_animals (NEW)

Target Item Dropdown:

Same list (including animals)

No special logic needed—just include the new table.

6. UI Integration
✅ Add New Category

Add “Animals” to the archive categories list:

Proverbs

Tiv Names

Plants

Festivals

Foods

Animals (NEW)

✅ Animal Listing Page

Create a page similar to other categories:

List all animals

Display name, Tiv name, and short description

✅ Animal Detail Page

Follow the same structure as other content pages:

Sections:

Name + Tiv Name

Description

Cultural Use

Source (join with sources table)

✅ Related Knowledge Section

Use existing logic:

SELECT *
FROM knowledge_links
WHERE source_table = 'tiv_animals'
AND source_id = ?;

Then resolve targets dynamically.

✅ Also Update Other Pages

On all existing content pages (proverbs, foods, etc.), include:

👉 “Related Animals”

Fetch with:

WHERE target_table = 'tiv_animals'
OR source_table = 'tiv_animals'
7. Routing / Controller Update

Add support for animals in routing logic:

animals list page

animal detail page

Follow same pattern used for other entities.

8. Key Design Principle

Animals must be treated as:

✅ A UI category

✅ A full graph node (entity)

❌ NOT just a static category label

9. Expected Outcome

After implementation:

Animals appear as a category in the archive

Each animal has its own page

Animals connect to:

Proverbs

Foods

Festivals

Plants

The knowledge graph becomes more complete and interactive

10. No Breaking Changes

This addition:

Does NOT affect existing tables

Does NOT require modifying knowledge_links

Fully aligns with the current architecture
