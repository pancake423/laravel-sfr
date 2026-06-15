<?php

namespace Pancake423\StaticFormRequest;

use Illuminate\Foundation\Http\FormRequest;

/*
 * a wrapper object for a StaticFormRequest that allows it to behave like a FormRequest
 * without inheriting the form request directly. Don't use this class directly, only if
 * retrieved from StaticFormRequest->form().
 */

class FormWrapper extends FormRequest
{
    protected static ?StaticFormRequest $data_object = null;

    private function canCallOnParent(string $method): bool
    {
        if (self::$data_object == null) {
            return false;
        }
        return method_exists(self::$data_object, $method);
    }

    public static function bind(StaticFormRequest $data_object): void
    {
        self::$data_object = $data_object;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules()
    {
        if (!$this->canCallOnParent("rules")) {
            return [];
        }
        return self::$data_object->rules();
    }

    public function authorize(): bool
    {
        if (!$this->canCallOnParent("authorize")) {
            return true;
        }
        return self::$data_object->authorize();
    }

    /**
     * @return array<string, string>
     */
    public function messages()
    {
        if (!$this->canCallOnParent("messages")) {
            return [];
        }
        return self::$data_object->messages();
    }
}
