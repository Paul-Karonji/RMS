<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'account_type' => $this->account_type,
            'status' => $this->status,
            'tenant_id' => $this->tenant_id,
            'tenant' => $this->when($this->relationLoaded('tenant'), function () {
                return [
                    'id' => $this->tenant->id,
                    'company_name' => $this->tenant->company_name,
                    'pricing_model' => $this->tenant->pricing_model,
                    'status' => $this->tenant->status,
                ];
            }),
            'must_change_password' => $this->must_change_password,
            'first_login_at' => $this->first_login_at,
            'last_login_at' => $this->last_login_at,
            'created_at' => $this->created_at,
        ];
    }
}
