<?php

namespace App\Resources;

class UserActionLogDetailsDatatable extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     */
    public function toArray($request): array
    {
        $sections = config('constants.USER_ACTION_SECTION_TYPE');

        return [
            $this->getValue('sub_section_id'),
            $this->getValue('reference_id'),
            $this->sanitizefieldName($this->getValue('summary'), $this->getValue('action'), $sections[$this->getValue('section_id')]),
            $this->getValue('old_value'),
            $this->getValue('new_value'),
            (is_object($this->getValue('created_at'))) ? $this->getValue('created_at')->toDateTimeString() : $this->getValue('created_at'),
        ];

    }

    public function with($request)
    {
        return [
            'draw' => 1,
            'recordsTotal' => count($this->resource),
            'recordsFiltered' => count($this->resource),
        ];
    }

    /**
     * [sanitizefieldName description]
     *
     * @param  [type] $fieldName [description]
     * @param  [type] $event     [description]
     * @param  [type] $section   [description]
     * @return [type]            [description]
     */
    protected function sanitizefieldName($fieldName, $event, $section)
    {
        $fieldName = str_replace($event.' '.$section.'. Column:', '', $fieldName);

        return str_replace('.', '', $fieldName);
    }
}
