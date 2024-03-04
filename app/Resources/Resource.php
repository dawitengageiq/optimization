<?php

namespace App\Resources;

use App\Http\Services\Helpers\Reflection;
use Illuminate\Container\Container;
use Illuminate\Database;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support;

class Resource
{
    protected $resource;

    protected $item;

    protected $with = [];

    protected $additional = [
        'type' => '',
        'status' => 200,
        'error' => false,
        'errorCode' => '',
        'message' => [
            'userMessage' => 'Success.',
            'developerMessage' => 'Success.',
            'moreInfo' => '',
        ],
    ];

    /**
     * Create a new resource instance.
     *
     * @param  mixed  $resource
     * @return void
     */
    public function __construct($resource)
    {
        $this->resource = $resource;

        // $reflection = new Reflection;
        // $reflection->printDetails($resource);
    }

    /**
     * Transform the resource into an HTTP response.
     */
    public function response(int $statusCode = null): JsonResponse
    {
        return $this->toResponse($statusCode);
    }

    /**
     * Create an HTTP response that represents the object.
     */
    public function toResponse(int $statusCode = null): JsonResponse
    {
        $this->request = Container::getInstance()->make('request');

        return tap(response()->json(
            $this->wrap(
                $this->resolve($this->request),
                $this->with($this->request),
                $this->additional
            ),
            $this->calculateStatus($statusCode)
        ), function ($response) {
            $this->withResponse($response);
        });
    }

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     */
    public function toArray($request): array
    {
        return $this->item;
    }

    /**
     * Get any additional data that should be returned with the resource array.
     */
    public function with(Request $request): array
    {
        return $this->with;
    }

    /**
     * Add additional meta data to the resource response.
     */
    public function additional(array $data): static
    {
        $this->additional = array_merge($this->additional, $data);

        return $this;
    }

    /**
     * Resolve the resource to an array.
     */
    protected function resolve(): array
    {
        // if ($this->resource instanceof Collection) {
        $newCollection = [];
        $this->resource->each(function ($item, $index) use (&$newCollection) {
            $this->item = $item;
            $newCollection[$index] = $this->toArray($this->request);
        });
        $this->item = null;

        return collect($newCollection);
        // }
    }

    protected function withResponse($response)
    {
        // dd($response);
        return $response;
    }

    /**
     * Calculate the appropriate status code for the response.
     */
    protected function calculateStatus(int $statusCode = null): int
    {
        if ($statusCode) {
            return $statusCode;
        }

        return $this->resource instanceof Model &&
               $this->resource->wasRecentlyCreated ? 201 : 200;
    }

    /**
     * Wrap the given data if necessary.
     */
    protected function wrap(array $data, array $with = [], array $additional = []): array
    {
        if ($data instanceof Database\Eloquent\Collection
        || $data instanceof Support\Collection) {
            $data = $data->all();
        }

        $data = ['data' => $data];

        return array_merge_recursive($data, $with, $additional);
    }

    /**
     * [getValue description]
     *
     * @param  [type] $field [description]
     * @return [type]        [description]
     */
    protected function getValue($field)
    {
        if (! $this->item) {
            return '';
        }

        if (($this->resource instanceof Database\Eloquent\Collection
        || $this->resource instanceof Support\Collection) && ! is_array($this->item)) {
            return $this->item->$field;
        }

        return array_key_exists($field, $this->item) ? $this->item[$field] : '';
    }
}
