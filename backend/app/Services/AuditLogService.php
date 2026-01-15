<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * Log an action.
     */
    public function log(
        User $user,
        string $action,
        ?string $modelType = null,
        ?string $modelId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $details = null,
        ?Request $request = null
    ): AuditLog {
        return AuditLog::create([
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
            'action' => $action,
            'model_type' => $modelType,
            'model_id' => $modelId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'details' => $details,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    /**
     * Log a model creation.
     */
    public function logCreated(User $user, $model, ?Request $request = null): AuditLog
    {
        return $this->log(
            user: $user,
            action: 'created',
            modelType: get_class($model),
            modelId: $model->id,
            newValues: $model->toArray(),
            request: $request
        );
    }

    /**
     * Log a model update.
     */
    public function logUpdated(User $user, $model, array $oldValues, ?Request $request = null): AuditLog
    {
        return $this->log(
            user: $user,
            action: 'updated',
            modelType: get_class($model),
            modelId: $model->id,
            oldValues: $oldValues,
            newValues: $model->toArray(),
            request: $request
        );
    }

    /**
     * Log a model deletion.
     */
    public function logDeleted(User $user, $model, ?Request $request = null): AuditLog
    {
        return $this->log(
            user: $user,
            action: 'deleted',
            modelType: get_class($model),
            modelId: $model->id,
            oldValues: $model->toArray(),
            request: $request
        );
    }

    /**
     * Log a custom action.
     */
    public function logAction(
        User $user,
        string $action,
        string $details,
        ?Request $request = null
    ): AuditLog {
        return $this->log(
            user: $user,
            action: $action,
            details: $details,
            request: $request
        );
    }

    /**
     * Get paginated audit logs with filters.
     */
    public function getLogs(
        User $user,
        array $filters = [],
        int $perPage = 50
    ): LengthAwarePaginator {
        $query = AuditLog::with(['user', 'tenant'])
            ->latest('created_at');

        // Tenant scoping
        if (!$user->hasRole('platform_owner')) {
            $query->where('tenant_id', $user->tenant_id);
        }

        // Filter by user
        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        // Filter by action
        if (!empty($filters['action'])) {
            $query->action($filters['action']);
        }

        // Filter by model type
        if (!empty($filters['model_type'])) {
            $query->modelType($filters['model_type']);
        }

        // Filter by date range
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->dateRange($filters['start_date'], $filters['end_date']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get logs for a specific model.
     */
    public function getLogsForModel(string $modelType, string $modelId, int $perPage = 50): LengthAwarePaginator
    {
        return AuditLog::where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->with('user')
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Get logs for a specific user.
     */
    public function getLogsForUser(User $targetUser, int $perPage = 50): LengthAwarePaginator
    {
        return AuditLog::where('user_id', $targetUser->id)
            ->latest('created_at')
            ->paginate($perPage);
    }

    /**
     * Export audit logs to array.
     */
    public function exportLogs(User $user, array $filters = []): Collection
    {
        $query = AuditLog::with(['user', 'tenant'])
            ->latest('created_at');

        // Apply same filters as getLogs
        if (!$user->hasRole('platform_owner')) {
            $query->where('tenant_id', $user->tenant_id);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $query->action($filters['action']);
        }

        if (!empty($filters['model_type'])) {
            $query->modelType($filters['model_type']);
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $query->dateRange($filters['start_date'], $filters['end_date']);
        }

        return $query->get();
    }
}
