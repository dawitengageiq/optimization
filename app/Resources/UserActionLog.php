<?php

namespace App\Resources;

class UserActionLog extends Resource
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
            'sub_section_id' => $this->getValue('sub_section_id'),
            'reference_id' => $this->getValue('reference_id'),
            'action' => $this->getValue('action'),
            'diff_for_humans' => $this->getValue('created_at')->diffForHumans(),
            'created_at' => $this->getValue('created_at')->toFormattedDateString(),
            'user_id' => $this->getValue('user_id'),
            'user_full_name' => ($user = $this->getValue('user')) ? $user->full_name : '',
        ];
    }
}
