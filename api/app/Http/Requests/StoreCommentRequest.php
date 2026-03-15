<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCommentRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
          'parent_id' => ['nullable', 'exists:comments,id'],
          'body'      => ['nullable', 'string', 'max:1000'],
          'media'     => ['nullable', 'array', 'max:4'],
          'media.*'   => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
        ];
    }

  public function withValidator($validator): void
  {
    $validator->after(function ($validator) {
      $body  = trim((string) $this->input('body', ''));
      $media = $this->file('media', []);

      if ($body === '' && empty($media)) {
        $validator->errors()->add('body', 'Body or media is required.');
      }
    });
  }

}
