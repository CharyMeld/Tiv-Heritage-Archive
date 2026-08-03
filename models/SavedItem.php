<?php
/**
 * SavedItem Model — generic per-user "save to collection" bookmarks
 */

require_once BASE_PATH . '/core/Model.php';

class SavedItem extends Model
{
    protected string $table = 'saved_items';

    protected array $fillable = ['user_id', 'item_table', 'item_id'];

    public function isSaved(int $userId, string $itemTable, int $itemId): bool
    {
        $stmt = $this->db->prepare(
            'SELECT 1 FROM saved_items WHERE user_id = ? AND item_table = ? AND item_id = ? LIMIT 1'
        );
        $stmt->execute([$userId, $itemTable, $itemId]);
        return (bool) $stmt->fetchColumn();
    }

    /** Adds the save if absent, removes it if present. Returns the resulting state. */
    public function toggle(int $userId, string $itemTable, int $itemId): bool
    {
        if ($this->isSaved($userId, $itemTable, $itemId)) {
            $stmt = $this->db->prepare(
                'DELETE FROM saved_items WHERE user_id = ? AND item_table = ? AND item_id = ?'
            );
            $stmt->execute([$userId, $itemTable, $itemId]);
            return false;
        }

        $this->create(['user_id' => $userId, 'item_table' => $itemTable, 'item_id' => $itemId]);
        return true;
    }

    /** All saved rows for a user, joined against a single item table (batch per table like KnowledgeLink). */
    public function getForUserByTable(int $userId, string $itemTable): array
    {
        $stmt = $this->db->prepare(
            "SELECT item_id FROM saved_items WHERE user_id = ? AND item_table = ? ORDER BY created_at DESC"
        );
        $stmt->execute([$userId, $itemTable]);
        $savedIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        if (empty($savedIds)) return [];

        // item_table is only ever populated from a fixed internal whitelist (never user input),
        // so interpolating it into the table position here is safe.
        $placeholders = implode(',', array_fill(0, count($savedIds), '?'));
        $stmt = $this->db->prepare("SELECT * FROM {$itemTable} WHERE id IN ({$placeholders})");
        $stmt->execute($savedIds);
        return $stmt->fetchAll();
    }

    public function countForUser(int $userId): int
    {
        return $this->countWhere('user_id', $userId);
    }
}
