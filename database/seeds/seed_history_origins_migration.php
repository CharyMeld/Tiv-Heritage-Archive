<?php
/**
 * Seeds two narrative content_items entries for the pre-existing
 * (previously empty) History -> Origins and History -> Migration
 * placeholder pages, using real cited research (WebSearch/WebFetch),
 * building on/expanding the Timeline feature's Swem/migration/clan
 * research from earlier this session.
 *
 * Saved as status='draft' — matches this project's established
 * pattern of draft-first review before publishing new content.
 *
 * Idempotent — skips if a content_items row with the same title
 * already exists in that section/subcategory.
 *
 * Run: php database/seeds/seed_history_origins_migration.php
 */

define('BASE_PATH', dirname(__DIR__, 2));

require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';
require_once BASE_PATH . '/core/Model.php';
require_once BASE_PATH . '/models/ContentItem.php';

$model = new ContentItem();
$db    = Database::getInstance();

$items = [

    [
        'section' => 'history',
        'subcategory' => 'origins',
        'title' => 'The Origins of the Tiv People',
        'excerpt' => "The Tiv trace descent from a single ancestor, Tiv, whose two sons founded the Ichongo and Ipusu lineages that make up the entire ethnic group today. Linguistically, Tiv belongs to the Tivoid group of Southern Bantoid (Benue-Congo) languages — one of the northernmost outposts of the broader Bantu-speaking world, spoken by roughly 2.5 million people mostly in Benue State, Nigeria.",
        'content' => <<<TEXT
Every Tiv person's identity begins with a single genealogical claim: descent from an ancestor named Tiv, whose two sons — Ichongo ("circumcised") and Ipusu ("uncircumcised") — founded the two great lineages that, subdividing repeatedly down to the small ipaven kin-group, account for every Tiv clan alive today. This is not framed as legend in the way outsiders might use the word; it is a genealogical charter that continues to organize land rights, marriage, and dispute resolution, and it made the Tiv one of the most closely studied examples of a segmentary lineage society in 20th-century anthropology (Bohannan & Bohannan, 1953).

Where that ancestor's people came from before settling in the Benue Valley is a genuinely open question, and it is worth being honest about that rather than presenting oral tradition as settled fact. Tiv oral tradition holds that the people migrated from the southeast, ultimately tracing back toward the Congo basin, before settling for a long period near a site called Swem — a mountain on what is now the Nigeria-Cameroon border, located near the source of the River Katsina-Ala. Swem occupies a dual place in Tiv culture: remembered as an ancestral homeland along the migration route, and also functioning as a sacred oath-site, historically invoked to prove innocence or cleanse the land of wrongdoing (Nomishan, 2022).

Academic historiography treats this chronology with real caution. Archaeological investigation in the Benue Valley has not, to date, been able to confirm the Congo/Swem homeland thesis with independent physical evidence, and researchers have noted that Tiv oral tradition and the archaeological record diverge in ways not yet resolved. What can be said with more confidence comes from linguistics: Tiv belongs to the Tivoid group, part of Southern Bantoid within the wider Benue-Congo family — placing the Tiv language among the northernmost extensions of the Bantu-related language world, alongside roughly eighteen other, much smaller Tivoid languages spoken along the Nigeria-Cameroon borderland. With around 2.5 million speakers today, Tiv is by far the largest language in that group, though the group as a whole remains one of the least thoroughly documented branches of Bantoid (Tivoid languages, Wikipedia; Britannica, "Benue-Congo languages").

Taken together, the linguistic evidence and the oral tradition point in the same general direction — a homeland to the southeast, related to the broader Bantu-speaking world — even where the archaeological record cannot yet confirm the specific route or dates. That combination of a strong internal genealogical tradition, a real but still poorly mapped linguistic relationship, and thin archaeological confirmation is the honest state of knowledge on Tiv origins.

Sources & Further Reading
Bohannan, P. & Bohannan, L. (1953). The Tiv of Central Nigeria. International African Institute (Ethnographic Survey of Africa: Western Africa, Part VIII).
Nomishan, T. S. (2022). Swem: The Tangible and Intangible Cultural Heritage of the Tiv of Central Nigeria. Tourism and Heritage Journal, 3, 56-67. https://doi.org/10.1344/THJ.2021.3.5
Tivoid languages. Wikipedia. https://en.wikipedia.org/wiki/Tivoid_languages
Benue-Congo languages. Encyclopaedia Britannica. https://www.britannica.com/topic/Benue-Congo-languages
Bantu Migration and the Tiv Myth of Crossing the River Congo. ResearchGate. https://www.researchgate.net/publication/397840461 (author/year not independently confirmed this pass — page could not be fetched directly; included as a named academic-adjacent source for further verification, not a sole basis for any specific claim above.)

See also the Timeline entries "Tiv Migration Tradition and the Sacred Site of Swem" and "Formation of the Ichongo-Ipusu Segmentary Lineage System" for dated-event framing of this same material.
TEXT,
        'is_featured' => 1,
    ],

    [
        'section' => 'history',
        'subcategory' => 'migration',
        'title' => 'The Tiv Migration into the Benue Valley',
        'excerpt' => "From the Swem homeland on today's Nigeria-Cameroon border, Tiv communities moved into the Middle Benue Valley in successive waves across the 17th-18th centuries, favouring defensible hilltop settlements, and — after the collapse of the Kwararafa Confederacy removed a major regional check on their expansion — pushed outward to occupy most of the territory Tiv communities hold today, including areas now in Taraba State.",
        'content' => <<<TEXT
Tiv oral tradition describes the migration into the Benue Valley as a long, gradual process rather than a single crossing. Accounts describe the ancestors of today's Tiv moving out of the Congo basin, through the area of the present Central African Republic, into northern Cameroon, and settling for a substantial period at Swem — a mountainous site near the source of the River Katsina-Ala, on what is now the Nigeria-Cameroon border. From Swem, communities are described as pushing further into the Middle Benue Valley of Nigeria, a movement some accounts place as beginning at least five hundred years ago, with a particularly well-attested phase around the 1600s-1700s CE. Push factors named in the oral tradition include pressure from neighbouring groups, population growth, and the search for land suited to farming (Perspectives on the Origin, Genealogical Narration, Early Migrations and Settlement Morphology of the Tiv of Central Nigeria; Archaeological Perspectives on the Origins and Migrations of the Tiv in the Benue Valley of Nigeria).

Settlement, once communities reached the Benue Valley, followed a distinctive pattern: hilltops were preferred as defensible bases, both for protection from conflict and for control of surrounding farmland, with dispersed compounds ("ikyar-ya") rather than large nucleated towns (Tiv Pre-Colonial Settlement Patterns, MAJOP journal). Migration from hilltop to hilltop over generations gradually extended Tiv settlement across much of the valley.

A major turning point came with the collapse of the Kwararafa Confederacy, the regional power that had previously constrained the territory available to Tiv and neighbouring groups. Following that collapse and the defeat of the Chamba (Ugenyi) in battle, Tiv communities expanded significantly through the second half of the 18th century, pushing into much of the Middle Benue Valley. Clans including Ukum, Shitile and Ugondo are specifically attested reaching the area of present-day Taraba State in the same period as the Chamba migration, roughly 1750-1800 CE, and effective Tiv presence is documented as far as Keana, Doma and Awe by the 18th century (Chia, R., Historical Ecology of Tiv Migration and Conflicts in the Benue Valley of Nigeria; I am Benue, "Tiv people of Taraba State and Benue State").

As with Tiv origins more broadly, it's worth being clear about the limits of the evidence here: this reconstruction rests primarily on oral tradition and a still-developing body of archaeological and historical-ecology research, not on a continuous documentary record. Researchers working on the Benue Valley have explicitly noted that oral accounts and archaeological findings do not yet align cleanly on dates or exact routes. What the sources agree on is the broad shape: a long southward and westward movement from a Cameroon-border homeland, hilltop-based settlement for defence, and a major 18th-century expansion enabled by the fall of Kwararafa — the process that produced the geographic footprint of Tiv settlement recognized today.

Sources & Further Reading
Chia, R. Historical Ecology of Tiv Migration and Conflicts in the Benue Valley of Nigeria: Implications for Food Security. The Digital Archaeological Record (tDAR). https://core.tdar.org/document/430273/
Tiv Pre-Colonial Settlement Patterns. MAJOP (Owl Journal of Philosophy), Vol. 1, No. 1 — Benue State University. https://bsum.edu.ng/journals/majop/vol1n1/files/9.pdf (author attribution not independently verified this pass)
Perspectives On The Origin, Genealogical Narration, Early Migrations And Settlement Morphology Of The Tiv Of Central Nigeria. ResearchGate/Academia.edu.
Archaeological Perspectives on the Origins and Migrations of the Tiv in the Benue Valley of Nigeria. Attributed in search indexing to Ndera, J. D. (2022) — author/year not independently confirmed this pass (source page could not be fetched directly).
Tiv people of Taraba State and Benue State; Any link? I am Benue. https://www.iambenue.com/the-tiv-people-of-taraba-state-any-link-to-benue/
Kwararafa Confederacy. Wikipedia. https://en.wikipedia.org/wiki/Kwararafa_Confederacy

See also the Timeline entries "Tiv Migration Tradition and the Sacred Site of Swem" and "Expansion of Major Tiv Clans Across the Benue Valley" for dated-event framing of this same material.
TEXT,
        'is_featured' => 1,
    ],
];

$inserted = 0;
$skipped  = 0;

foreach ($items as $item) {
    $stmt = $db->prepare(
        'SELECT id FROM content_items WHERE section = ? AND subcategory = ? AND title = ? LIMIT 1'
    );
    $stmt->execute([$item['section'], $item['subcategory'], $item['title']]);
    if ($stmt->fetch()) {
        echo "Skipping (already exists): {$item['title']}\n";
        $skipped++;
        continue;
    }

    $item['status'] = 'draft';
    $id = $model->create($item);
    echo "Inserted content item -> id {$id}: {$item['title']} ({$item['section']}/{$item['subcategory']})\n";
    $inserted++;
}

echo "\nDone. Inserted {$inserted} item(s), skipped {$skipped} already-existing.\n";
