<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    // Ta co the them du lieu bang construct cung dc

    protected $statusText, $statusCode, $token;
    public function __construct($resource, $statusCode = '200', $statusText = 'success', $token = null)
    {
        parent::__construct($resource);         // $resource = chinh la $users dau vao. Bay gio ta truyen $users vao trong class parents
                                                // la ResourceCollection, roi den JsonResource
        $this->statusText = $statusText;
        $this->statusCode = $statusCode;
        $this->token = $token;
    }
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    public function toArray($request)       //  su dung $request de them du lieu tu ben ngoai vao
    {
        // return parent::toArray($request);
        return [
            'status'            => $this->statusCode,
            'message'           => $this->statusText,
            // 'status'           => $request->status,        // de co dc du lieu thi ben controller phai gan du lieu bang merge()
            'user'              => $this->collection,
            'token'             => $this->token
        ];
    }
}
