<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TelegramWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // Берем только те поля, которые реально есть в исходном запросе
        $data = [
            'message_id' => $this->input('message.message_id'),
            'user_id' => $this->input('message.from.id'),
            'date' => $this->input('message.date'),
        ];

        if ($this->has('message.photo')) {
            $data['photo'] = $this->input('message.photo');
        }

        if ($this->has('message.document')) {
            $data['document'] = $this->input('message.document');
        }

        $this->merge($data);
    }

    public function rules(): array
    {
        return [
            'message_id' => 'required|integer',
            'user_id' => 'required|integer',
            'date' => 'required|integer',
            'photo' => 'array|nullable',
            'document' => 'array|nullable',
            'document.mime_type' => 'required_with:document|in:application/pdf'
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (!$this->has('document') && !$this->has('photo')) {
                $validator->errors()->add('files', 'Сообщение не содержит файлов');
                return;
            }

            if ($this->has('document') && $this->has('photo')) {
                $validator->errors()->add('files', 'Нельзя отправлять PDF и изображения одновременно');
                return;
            }

            if ($this->has('document')) {
                // В Telegram API документ всегда приходит как один файл,
                // но добавим проверку для большей надёжности
                if (is_array($this->input('document')) && count($this->input('document')) > 1) {
                    $validator->errors()->add('files', 'Можно загрузить только один PDF файл');
                }
            }
        });
    }
}
