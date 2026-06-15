<?php

namespace Pancake423\StaticFormRequest;

use ReflectionClass;
use ReflectionProperty;
use Pancake423\StaticFormRequest\Validate;
use Pancake423\StaticFormRequest\Message;
use Pancake423\StaticFormRequest\FormWrapper;
use Illuminate\Support\Facades\App;

/**
 * a more explicit alternative to FormRequests.
 *
 * @example github.com/pancake423/laravel-sfr/README.md
 */
class StaticFormRequest
{
    protected FormWrapper $_wrapper;

    public function __construct()
    {
        FormWrapper::bind($this);
        $this->_wrapper = App::make(FormWrapper::class);
        $this->validated();
    }

    /**
     * gets all public properties of this object that have a reflection attribute
     * of class $class.
     *
     * @param class-string $class
     *
     * @return ReflectionProperty[]
     */
    private function getPropsWithAttr($class)
    {
        $props = [];
        $ref = new ReflectionClass($this);
        foreach ($ref->getProperties(ReflectionProperty::IS_PUBLIC) as $prop) {
            if (sizeof($prop->getAttributes($class)) > 0) {
                $props[] = $prop;
            }
        }
        return $props;
    }

    public function rules()
    {
        $rules = [];
        // iterate over the public properies of this class
        $ref = new ReflectionClass($this);
        foreach ($this->getPropsWithAttr(Validate::class) as $prop) {
            $name = $prop->getName();
            $attributes = $prop->getAttributes(Validate::class);

            $rule = [];
            foreach ($attributes as $attr) {
                $args = $attr->getArguments();
                foreach ($args as $arg) {
                    if (is_array($arg)) {
                        $rule = [...$rule, ...$arg];
                    } else {
                        $rule = $arg;
                    }
                }
            }

            $rules[$name] = $rule;
        }
        return $rules;
    }

    public function authorize()
    {
        return true;
    }

    public function messages()
    {
        $messages = [];
        // iterate over the public properies of this class
        $ref = new ReflectionClass($this);
        foreach ($this->getPropsWithAttr(Message::class) as $prop) {
            $name = $prop->getName();
            $attributes = $prop->getAttributes(Message::class);

            foreach ($attributes as $attr) {
                $args = $attr->getArguments();
                foreach ($args as $arg) {
                    $messages = array_merge($messages, $arg);
                }
            }
        }

        return $messages;
    }

    public function validated()
    {
        $validated = $this->_wrapper->validated();
        $ref = new ReflectionClass($this);

        // TODO: error handling.
        // the type of the validation rule doesn't match the type of the variable

        foreach ($validated as $key => $value) {
            $ref->getProperty($key)->setValue($this, $value);
        }
        return $this;
    }

    /**
     * returns the array representation of this object.
     */
    public function asArray(): mixed
    {
        return $this->_wrapper->validated();
    }

    /**
     * get the underlying form instance for this object.
     * useful if you need methods that exist on the FormRequest directly.
     */
    public function form(): FormWrapper
    {
        return $this->_wrapper;
    }
}
