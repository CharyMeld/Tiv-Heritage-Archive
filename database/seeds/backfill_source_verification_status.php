<?php
/**
 * Backfills `sources.verification_status` from data already in the
 * database, rather than guessing. Priority order per source:
 *
 *  1. If the source's OWN `notes` field carries an explicit caveat
 *     deliberately written during research (e.g. "SINGLE-SOURCED",
 *     "unconfirmed", "unverified") — that stands as-is: needs_corroboration.
 *     This is checked FIRST and is not overridden by unrelated
 *     corroboration elsewhere (a source can be legitimately uncertain
 *     on its own merits even if it happens to co-appear, for a
 *     different event, alongside some other unrelated source).
 *  2. Otherwise, for sources linked to one or more timeline_events
 *     (via the direct source_id FK or the timeline_event_sources
 *     pivot):
 *       - 'disputed' if any linked event's alternative_dates_notes
 *         documents an actual conflict between sources;
 *       - 'verified' if any linked event was corroborated by 2+ total
 *         sources (i.e. this source was cited alongside another for
 *         the same claim);
 *       - otherwise 'needs_corroboration' (only ever appeared as a
 *         single, uncorroborated citation).
 *  3. Otherwise (no notes caveat, no timeline_events linkage —
 *     pre-existing community/archive content such as proverbs,
 *     festivals, etc.): default 'verified'.
 *
 * Idempotent — safe to re-run; always recomputes from current data.
 *
 * Run: php database/seeds/backfill_source_verification_status.php
 */

define('BASE_PATH', dirname(__DIR__, 2));

require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/config/database.php';

$db = Database::getInstance();

$caveatKeywords = ['single-sourced', 'flagged for', 'not independently verified', 'unconfirmed', 'unverified'];
$conflictKeywords = ['disagree', 'conflict', 'dispute'];

$sources = $db->query('SELECT id, notes FROM sources')->fetchAll(PDO::FETCH_ASSOC);

$updated = ['verified' => 0, 'needs_corroboration' => 0, 'disputed' => 0];

$eventStmt = $db->prepare(
    "SELECT te.alternative_dates_notes,
            (SELECT COUNT(*) FROM timeline_event_sources tes2 WHERE tes2.event_id = te.id) AS source_count
     FROM timeline_events te
     WHERE te.source_id = :sid
        OR te.id IN (SELECT event_id FROM timeline_event_sources WHERE source_id = :sid2)"
);

foreach ($sources as $source) {
    $ownNotes = mb_strtolower($source['notes'] ?? '');
    $hasOwnCaveat = false;
    foreach ($caveatKeywords as $kw) {
        if ($ownNotes !== '' && str_contains($ownNotes, $kw)) { $hasOwnCaveat = true; break; }
    }

    if ($hasOwnCaveat) {
        $status = 'needs_corroboration';
    } else {
        $eventStmt->execute(['sid' => $source['id'], 'sid2' => $source['id']]);
        $events = $eventStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($events)) {
            $hasCorroboration = false;
            $hasConflict = false;

            foreach ($events as $event) {
                if ((int) $event['source_count'] >= 2) $hasCorroboration = true;

                $eventNotes = mb_strtolower($event['alternative_dates_notes'] ?? '');
                foreach ($conflictKeywords as $kw) {
                    if ($eventNotes !== '' && str_contains($eventNotes, $kw)) { $hasConflict = true; break; }
                }
            }

            $status = $hasConflict ? 'disputed' : ($hasCorroboration ? 'verified' : 'needs_corroboration');
        } else {
            $status = 'verified';
        }
    }

    $update = $db->prepare('UPDATE sources SET verification_status = ? WHERE id = ?');
    $update->execute([$status, $source['id']]);
    $updated[$status]++;

    echo "source {$source['id']} -> {$status}\n";
}

echo "\nDone. verified={$updated['verified']} needs_corroboration={$updated['needs_corroboration']} disputed={$updated['disputed']}\n";
