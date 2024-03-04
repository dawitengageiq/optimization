<?php

namespace App\Resources;

class UserActionLogDetails extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     */
    public function toArray($request): array
    {
        return [
            'section_id' => $this->getValue('section_id'),
            'reference_id' => $this->getValue('reference_id'),
            'summary' => $this->getValue('summary'),
            'old_value' => $this->getValue('old_value'),
            'new_value' => $this->getValue('new_value'),
            'created_at' => $this->getValue('created_at')->toFormattedDateString(),
        ];

    }
}
