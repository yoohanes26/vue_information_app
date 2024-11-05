<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasRelationships;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsmsInformation extends Model
{
    use HasFactory, HasRelationships;

    // エンティティのテーブル名
    protected $table = 'asms_informations';

    public $timestamps = false;

    protected $primaryKey = 'information_id';

    // 入力できるフィールドをここに定義
    protected $fillable = [
        'information_title',
        'information_kbn', // 0：重要、1：情報
        'keisai_ymd',
        'enable_start_ymd',
        'enable_end_ymd',
        'information_naiyo',
        'delete_flg',
        'create_user_cd',
        'create_time',
        'update_user_cd',
        'update_time'
    ];

    public function newQuery($excludeDeleted = true) {
        return parent::newQuery($excludeDeleted)
            ->where('delete_flg', '=', 0);
    }

    //　本エンティティと関連するユーザのエンティティと関係づける（登録者ユーザコードに基づく）
    public function create_user(){
        return $this->belongsTo(User::class, 'create_user_cd');
    }

    //　本エンティティと関連するユーザのエンティティと関係づける（更新者ユーザコードに基づく）
    public function update_user(){
        return $this->belongsTo(User::class, 'update_user_cd');
    }

    public function scopeSearchInformation($query, $request){
        if($request->information_title)
            $query = $this->searchInformationTitle($query, $request->information_title);

        $query = $this->searchInformationKbn($query, $request->information_kbn);

        if($request->keisai_ymd)
            $query = $this->searchInformationKeisaiYmd($query, $request->keisai_ymd);

        if($request->enable_start_ymd || $request->enable_end_ymd)
            $query = $this->searchInformationEnableYmd($query, $request->enable_start_ymd, $request->enable_end_ymd);

        return $query;
    }

    public function searchInformationTitle($query, $input = null) {
        if(!empty($input)){
            return $query->where('information_title', 'like', '%'.$input.'%');
        }
        return $query;
    }

    public function searchInformationKbn($query, $input) {
        if($input != 2){
            return $query->where('information_kbn', $input);
        }
        return $query;
    }

    public function searchInformationKeisaiYmd($query, $input = null) {
        if(!empty($input)){
            return $query->whereDate('keisai_ymd', $input);
        }
        return $query;
    }

    public function searchInformationEnableYmd($query, $start = null, $end = null) {
        if(!empty($start && empty($end))){
            return $query->whereDate('enable_start_ymd', '<=', $start)->whereDate('enable_end_ymd', '>=', $start);
        } else if(empty($start) && !empty($end)){
            return $query->whereDate('enable_start_ymd', '<=', $end)->whereDate('enable_end_ymd', '>=', $end);
        } else if(!empty($start) && !empty($end)){
            return $query->where(function($q) use ($start, $end){
                // IF THE SEARCH SCOPE IS GREATER THAN RESULTS
                $q->whereDate('enable_start_ymd', '>=', $start)->whereDate('enable_end_ymd', '<=', $end);
            })->orWhere(function($q) use ($start, $end){
                // IF THE SEARCH SCOPE IS SMALLER AND INSIDE THE RESULTS
                $q->whereDate('enable_start_ymd', '<=', $start)->whereDate('enable_end_ymd', '>=', $end);
            })->orWhere(function($q) use ($start, $end){
                // IF ONLY THE START DATE INSIDE THE RESULT
                $q->whereDate('enable_start_ymd', '>=', $start)->whereDate('enable_start_ymd', '<=', $end);
            })->orWhere(function($q) use ($start, $end){
                // IF ONLY THE END DATE INSIDE THE RESULT
                $q->whereDate('enable_end_ymd', '>=', $start)->whereDate('enable_end_ymd', '<=', $end);
            });
        }
        return $query;
    }

    // データをJSONフォーマットで返す
    public function format(){
        return [
            'information_id' => $this->information_id,
            'information_title' => $this->information_title,
            'information_kbn' => $this->information_kbn,
            'information_kbn_txt' => $this->information_kbn ? '情報' : '重要',
            'keisai_ymd' => Carbon::parse($this->keisai_ymd)->format('Y/m/d'),
            'enable_ymd' => Carbon::parse($this->enable_start_ymd)->format('Y/m/d') . " ～ " . Carbon::parse($this->enable_end_ymd)->format('Y/m/d'),
            'enable_start_ymd' => Carbon::parse($this->enable_start_ymd)->format('Y/m/d'),
            'enable_end_ymd' => Carbon::parse($this->enable_end_ymd)->format('Y/m/d'),
            'information_naiyo' => $this->information_naiyo,
        ];
    }
}
