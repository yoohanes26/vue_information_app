<?php

namespace App\Http\Controllers\Api;

use App\Http\Helper\ApiFormat;
use App\Models\AsmsInformation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class InformationController {
    public function index(Request $request){
        $information = AsmsInformation::searchInformation($request);

        if($request->sortField){
            if($request->sortField == 'enable_ymd'){
                if ($request->sortOrder == 1) {
                    $information = $information->orderBy('enable_start_ymd')->orderBy('enable_end_ymd');
                } else if ($request->sortOrder == -1) {
                    $information = $information->orderBy('enable_start_ymd', 'desc')->orderBy('enable_end_ymd', 'desc');
                }
            } else {
                if ($request->sortOrder == 1) {
                    $information = $information->orderBy($request->sortField);
                } else if ($request->sortOrder == -1) {
                    $information = $information->orderBy($request->sortField, 'desc');
                }
            }
        } else {
            $information = $information->orderByDesc('create_time');
        }

        $information = $information->paginate($request->rows);

        $format = ApiFormat::transform($information);

        return $format;
    }

    public function create(Request $request){
        $data = $request['params'];

        $data['keisai_ymd'] = str_replace('-', '', explode('T', $data['keisai_ymd'])[0]);
        $data['enable_start_ymd'] = str_replace('-', '', explode('T', $data['enable_start_ymd'])[0]);
        $data['enable_end_ymd'] = str_replace('-', '', explode('T', $data['enable_end_ymd'])[0]);

        $validator = Validator::make($data,[
            'information_title' => 'required|string',
            'information_kbn' => 'required|boolean',
            'keisai_ymd' => 'required|date_format:Ymd',
            'enable_start_ymd' => 'required|date_format:Ymd',
            'enable_end_ymd' => 'required|date_format:Ymd',
            'information_naiyo' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => 'データが無効です。もう一度送信してください。',
            ]);
        }

        $data['create_user_cd'] = 1;
        $data['create_time'] = Carbon::now();
        $data['update_user_cd'] = 1;
        $data['update_time'] = Carbon::now();

        $modelInformation = AsmsInformation::create($data);

        if($modelInformation)
            return response()->json([
                'status' => 'success',
                'message' => 'お知らせ新規登録が成功しました。',
            ]);

        return response()->json([
            'status' => 'error',
            'message' => '登録処理が失敗した。もう一度送信してください。',
        ]);
    }

    public function edit(Request $request){
        $data = $request['params'];

        $data['keisai_ymd'] = str_replace('-', '', explode('T', $data['keisai_ymd'])[0]);
        $data['enable_start_ymd'] = str_replace('-', '', explode('T', $data['enable_start_ymd'])[0]);
        $data['enable_end_ymd'] = str_replace('-', '', explode('T', $data['enable_end_ymd'])[0]);

        $validator = Validator::make($data,[
            'information_id' => 'required|exists:asms_informations,information_id',
            'information_title' => 'required|string',
            'information_kbn' => 'required|boolean',
            'keisai_ymd' => 'required|date_format:Ymd',
            'enable_start_ymd' => 'required|date_format:Ymd',
            'enable_end_ymd' => 'required|date_format:Ymd',
            'information_naiyo' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => 'データが無効です。もう一度送信してください。',
            ]);
        }

        $modelInformation = AsmsInformation::find($data['information_id']);

        $data['update_user_cd'] = 1;
        $data['update_time'] = Carbon::now();

        if($modelInformation->update($data))
            return response()->json([
                'status' => 'success',
                'message' => 'お知らせ変更が成功しました。',
            ]);

        return response()->json([
            'status' => 'error',
            'message' => '変更処理が失敗した。もう一度送信してください。',
        ]);
    }

    public function delete(Request $request){
        $validator = Validator::make($request->all(),[
            'information_id' => 'required|exists:asms_informations,information_id',
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => 'error',
                'message' => '削除するお知らせが見つかりませんでした。',
            ]);
        }

        $modelInformation = AsmsInformation::find($request['information_id']);

        if($modelInformation->delete()){
            return response()->json([
                'status' => 'success',
                'message' => 'お知らせ削除が成功しました。',
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => '変更処理が失敗した。もう一度送信してください。',
        ]);
    }
}
