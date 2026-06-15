<?php

namespace Pancake423\StaticFormRequest;

use Attribute;

/**
 * A set of validation rules for the given property. Same as a normal
 * FormRequest, either a single string joined with |, or an array of rules.
 */
#[Attribute]
class Validate
{
    /**
     * @param string|string[] $rule
     */
    public function __construct(public $rule) {}
}
