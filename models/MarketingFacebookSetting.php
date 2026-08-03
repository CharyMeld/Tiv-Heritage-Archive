<?php
require_once BASE_PATH . '/core/Model.php';

/**
 * Singleton settings row (id always 1) — a Facebook Page connection is
 * either configured or not, there's only ever one.
 */
class MarketingFacebookSetting extends Model
{
    protected string $table = 'marketing_facebook_settings';

    protected array $fillable = [
        'app_id', 'app_secret_encrypted', 'page_id', 'page_access_token_encrypted',
        'instagram_business_account_id', 'instagram_connection_status',
        'instagram_last_checked_at', 'instagram_last_error',
        'connection_status', 'last_checked_at', 'last_error', 'updated_by',
    ];

    public function get(): array
    {
        $row = $this->find(1);
        if ($row) return $row;

        $this->db->exec("INSERT IGNORE INTO {$this->table} (id) VALUES (1)");
        return $this->find(1) ?? [
            'id' => 1, 'app_id' => null, 'app_secret_encrypted' => null,
            'page_id' => null, 'page_access_token_encrypted' => null,
            'instagram_business_account_id' => null, 'instagram_connection_status' => 'disconnected',
            'instagram_last_checked_at' => null, 'instagram_last_error' => null,
            'connection_status' => 'disconnected', 'last_checked_at' => null, 'last_error' => null,
        ];
    }

    public function save(array $data): void
    {
        $this->get(); // ensure row 1 exists
        $this->update(1, $data);
    }

    public function isConnected(): bool
    {
        return $this->get()['connection_status'] === 'connected';
    }

    public function markConnected(): void
    {
        $this->update(1, [
            'connection_status' => 'connected',
            'last_checked_at'   => date('Y-m-d H:i:s'),
            'last_error'        => null,
        ]);
    }

    public function markError(string $message): void
    {
        $this->update(1, [
            'connection_status' => 'error',
            'last_checked_at'   => date('Y-m-d H:i:s'),
            'last_error'        => $message,
        ]);
    }

    public function isInstagramConnected(): bool
    {
        return $this->get()['instagram_connection_status'] === 'connected';
    }

    public function markInstagramConnected(): void
    {
        $this->update(1, [
            'instagram_connection_status' => 'connected',
            'instagram_last_checked_at'   => date('Y-m-d H:i:s'),
            'instagram_last_error'        => null,
        ]);
    }

    public function markInstagramError(string $message): void
    {
        $this->update(1, [
            'instagram_connection_status' => 'error',
            'instagram_last_checked_at'   => date('Y-m-d H:i:s'),
            'instagram_last_error'        => $message,
        ]);
    }
}
