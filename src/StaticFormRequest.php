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
        // This is a hacky solution to ensure that the FormWrapper
        // knows about this object BEFORE validation gets run. there
        // is probably a "more correct" way to do this in a service
        // provider, but this works for now.
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
            $rule = [];
            foreach ($prop->getAttributes(Validate::class) as $attr) {
                foreach ($attr->getArguments() as $arg) {
                    if (is_array($arg)) {
                        $rule = [...$rule, ...$arg];
                    } else {
                        $rule = $arg;
                    }
                }
            }
            $rules[$prop->getName()] = $rule;
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
            foreach ($prop->getAttributes(Message::class) as $attr) {
                foreach ($attr->getArguments() as $arg) {
                    $messages = array_merge($messages, $arg);
                }
            }
        }

        return $messages;
    }

    public function validated()
    {
        $ref = new ReflectionClass($this);
        foreach ($this->_wrapper->validated() as $key => $value) {
            $ref->getProperty($key)->setValue($this, $value);
        }
        return $this;
    }

    /**
     * returns the array representation of this object.
     *
     * @return array<string, mixed>
     */
    public function toArray()
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
