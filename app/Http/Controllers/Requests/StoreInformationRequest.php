<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInformationRequest extends FormRequest{
    public function authorize(){
        return true;
    }

    public function rules() {
        return [
            'information_title' => 'required|string',
            'information_kbn' => 'required|boolean',
            'keisai_ymd' => 'required|date_format:Ymd',
            'enable_start_ymd' => 'required|date_format:Ymd',
            'enable_end_ymd' => 'required|date_format:Ymd',
            'information_naiyo' => 'required|string',
        ];
    }
}
