<?php

namespace App\Observers;

use App\Models\Asset;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AssetObserver
{
    /**
     * Handle the Asset "created" event.
     */
    public function created(Asset $asset): void
    {
        $this->logActivity($asset, 'created', null, $asset->getAttributes());
    }

    /**
     * Handle the Asset "updated" event.
     */
    public function updated(Asset $asset): void
    {
        $changes = $asset->getChanges();
        $original = array_intersect_key($asset->getOriginal(), $changes);

        // Ignorišemo updated_at
        unset($changes['updated_at'], $original['updated_at']);

        if (!empty($changes)) {
            $this->logActivity($asset, 'updated', $original, $changes);
        }
    }

    /**
     * Handle the Asset "deleted" event.
     */
    public function deleted(Asset $asset): void
    {
        $this->logActivity($asset, 'deleted', $asset->getAttributes(), null);
    }

    private function logActivity(Asset $asset, string $event, ?array $oldValues, ?array $newValues): void
    {
        $user = Auth::user();

        AuditLog::create([
            'tenant_id'      => $asset->tenant_id, // Uvek primenjujemo tenant sa aseta
            'user_id'        => $user?->id,
            'event'          => $event,
            'auditable_type' => Asset::class,
            'auditable_id'   => $asset->id,
            'old_values'     => $oldValues,
            'new_values'     => $newValues,
            'ip_address'     => Request::ip(),
            'user_agent'     => Request::userAgent(),
        ]);
    }
}
