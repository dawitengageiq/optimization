<?php

namespace App\Resources;

class UserActionLogDatatable extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     */
    public function toArray($request): array
    {
        return [
            // $this->getValue('user_id'),
            ($user = $this->getValue('user')) ? $user->full_name : '',
            $this->getValue('section_id'),
            $this->getValue('sub_section_id'),
            $this->getValue('reference_id'),
            $this->getValue('action'),
            $this->getValue('created_at')->diffForHumans(),
            $this->getValue('created_at')->toDateString(),
            // null
        ];
    }

    public function with($request)
    {
        return [
            'draw' => $request->get('draw'),
            'recordsTotal' => $request->get('count'),
            'recordsFiltered' => $request->get('count'),
        ];
    }
}
