<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
//            'org_id' => $this->org_id,
//
            'name' => $this->name,
//            'description' => $this->description,
//
//            'price' => $this->price,
//            'currency' => $this->currency,
//
//            'good_name' => $this->good_name,
//            'good_tax' => $this->good_tax,
//            'good_type' => $this->good_type,
//
//            'archived' => $this->archived,
//            'data' => $this->data,
//            'yandex_type' => $this->yandex_type,
//
//            'remote_created_at' => $this->remote_created_at,
//            'remote_updated_at' => $this->remote_updated_at,
//
//            'created_at' => $this->created_at,
//            'updated_at' => $this->updated_at,
        ];
    }
}
