<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class AuditService
{
    public function record(Request $request, ?int $tenantId, string $action, ?Model $resource = null, array $before = [], array $after = []): void
    {
        AuditLog::create([
            'actor_id' => $request->user()?->id,
            'tenant_id' => $tenantId,
            'action' => $action,
            'metadata' => array_filter([
                'resource_type' => $resource ? $resource::class : null,
                'resource_id' => $resource?->getKey(),
                'before' => $this->sanitize($before),
                'after' => $this->sanitize($after),
                'support_mode' => (bool) $request->header('X-Support-Mode'),
            ]),
            'ip_address' => $request->ip(),
        ]);
    }

    private function sanitize(array $values): array
    {
        return collect($values)->except(['password','password_confirmation','remember_token','token'])->all();
    }
}
