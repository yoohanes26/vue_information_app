<?php

namespace App\Http\Helper;


class ApiFormat
{
    public static function transform($data, $format = 'format', $params = ''){
        $items = $data->toArray();

        $items['data'] = $data->getCollection()->map(function($item) use ($format, $params) {
            return $item->$format($params);
        });

        return $items;
//        return response()->json([
//            'success' => true,
//            'meta' => [
//                'count' => (int)$items['per_page'],
//                'total' => $items['total'],
//                'total_page' => $items['last_page'],
//                'current_page' => $items['current_page'],
//            ],
//            'links' => [
//                'first' => urldecode($items['first_page_url']),
//                'last' => urldecode($items['last_page_url']),
//                'next' => urldecode($items['next_page_url']),
//                'prev' => urldecode($items['prev_page_url']),
//            ],
//            'data' => array_merge($info, [
//                'items' => $data->getCollection()->map(function($item) use ($format, $params) {
//                    return $item->$format($params);
//                })
//            ])
//        ], 200);
    }
}
