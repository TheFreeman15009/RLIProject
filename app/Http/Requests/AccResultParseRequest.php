<?php

namespace App\Http\Requests;

use App\Rules\AttributeExists;
use App\Rules\FileEncodingUTF8;
use Illuminate\Foundation\Http\FormRequest;

class AccResultParseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [

            'race' => [ 'required', 'file' , new FileEncodingUTF8() ],
            'quali' => [ 'required', 'file' , new FileEncodingUTF8() ],
            'round' => [ 'required', 'gte:0' ],
            'season' => [ 'required', new AttributeExists('App\\Season', 'id') ],
            'points' => [ 'required', new AttributeExists('App\\Points', 'id') ]
        ];
    }
}
