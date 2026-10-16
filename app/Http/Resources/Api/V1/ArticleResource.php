<?php

namespace App\Http\Resources\Api\V1;

use App\Support\PublicImageUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArticleResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id_artikel,
            'judul' => $this->judul,
            'isi' => $this->isi,
            'gambar' => PublicImageUrl::resolve($request, $this->gambar),
            'tanggal_publikasi' => $this->tanggal_publikasi?->toIso8601String(),
        ];
    }
}
