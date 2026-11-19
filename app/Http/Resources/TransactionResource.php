<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'  => $this->id,
            'tid' => $this->tid,

            'terminal' => $this->whenLoaded('terminal', function () {
                return [
                    'id'   => $this->terminal?->id,
                    'name' => $this->terminal?->name,
                ];
            }),

            'terminalService' => $this->whenLoaded('terminalService', function () {
                return [
                    'id'        => $this->terminalService?->id,
                    'name'      => $this->terminalService?->name,
                    'good_name' => $this->terminalService?->good_name,
                ];
            }),
            'service' => $this->whenLoaded('service', function () {
                return [
                    'id'   => $this->service?->id,
                    'name' => $this->service?->name,
                ];
            }),

            'station_id'      => $this->station_id,
            'station_name'    => $this->station_name,

            'type' => $this->type,

            'price'          => $this->price,
            'inserted'       => $this->inserted,
            'dispensed'      => $this->dispensed,
            'price_returned' => $this->price_returned,
            'discount'       => $this->discount,
            'currency'       => $this->currency,

            'service_price'    => $this->service_price,
            'amount'           => $this->amount,
            'amount_dispensed' => $this->amount_dispensed,


            'printed'     => $this->printed,
            'sync'        => $this->sync,
            'no_troubles' => $this->no_troubles,
            'approved'    => $this->approved,

            'user_id' => $this->user_id,

            'remote_created_at' => $this->remote_created_at,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
