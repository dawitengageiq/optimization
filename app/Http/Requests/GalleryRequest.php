<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Request;
use Storage;
use Validator;

// use Log;

class GalleryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        Validator::extend('image_exists', function ($attribute, $value, $parameters) {
            if (Request::get($parameters[1]) == 1) {
                $ext = Request::file($parameters[0])->getClientOriginalExtension();

            } else {
                $url = Request::get($parameters[0]);
                $ext = pathinfo($url, PATHINFO_EXTENSION);
            }
            $image = $value.'.'.$ext;
            $exists = Storage::disk('public')->has('images/gallery/'.$image);
            // Log::info($image .' - '.$exists);
            if ($exists == 1) {
                return false;
            } else {
                return true;
            }
        });

        Validator::extend('check_if_valid_image_url', function ($attribute, $value, $parameters) {
            if (Request::get($parameters[0]) == 1) {
                return true;
            } else {
                $curl = curl_init($value);
                curl_setopt($curl, CURLOPT_NOBODY, true);
                curl_exec($curl);
                $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                if ($code == 200) {
                    $status = true;
                } else {
                    $status = false;
                }
                curl_close($curl);

                return $status;
            }
        });

        return [
            'name' => 'required|image_exists:image,img_type',
            'image' => 'required|check_if_valid_image_url:img_type',
            'img_type' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'name.image_exists' => 'Image already exists.',
            'image.check_if_valid_image_url' => 'Url is invalid.',
        ];
    }
}
