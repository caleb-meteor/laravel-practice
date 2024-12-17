<?php

namespace Caleb\Practice;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response as BaseResponse;

trait Response
{

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function success(mixed $data = null)
    {
        return $this->jsonResponse($data, message: trans('practice::messages.system.success'));
    }

    /**
     * @return JsonResponse
     */
    public function error(string $errorMessage, int $errorCode = BaseResponse::HTTP_SERVICE_UNAVAILABLE, mixed $data = null)
    {
        return $this->jsonResponse($data, $errorCode, $errorMessage);
    }

    /**
     * @return JsonResponse
     */
    public function jsonResponse(mixed $data = null, int $code = BaseResponse::HTTP_OK, string $message = '', array $headers = [])
    {
        $formatData = [
            'code' => $code,
            'msg' => $message,
            'data' => $data,
        ];

        if(Context::has('request_id')){
            $formatData['request_id'] = Context::get('request_id');
        }

        return response()->json($formatData, headers: $headers);
    }
}
