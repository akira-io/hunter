<?php

declare(strict_types=1);

namespace App\Http\Requests\Hunt;

use App\Rules\Rules\WithoutBlankCharactersRule;
use Illuminate\Foundation\Http\FormRequest;

final class CreateHuntRequest extends FormRequest
{
    /**
     * The validation rules that apply to the request.
     *
     * @return array<string,list<WithoutBlankCharactersRule|string>>
     */
    public function rules(): array
    {

        return [
            'content' => ['nullable', 'string', 'min:3', 'max:500', new WithoutBlankCharactersRule, 'required_without:image'],
            'is_reported' => ['boolean'],
            'is_pinned' => ['boolean'],
            'is_ignored' => ['boolean'],
            'image' => ['nullable', 'image', 'max:2048', 'mimes:jpg,jpeg,png', 'required_without:content'],
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

        return true;
    }
}
