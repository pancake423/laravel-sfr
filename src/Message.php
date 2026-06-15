<?php

namespace Pancake423\StaticFormRequest;

use Attribute;

/**
 * an array of validation messages for the given property. Format is exactly like
 * the messages() function in normal FormRequests: an array of
 * ["property.rule" => "custom message"]
 */
#[Attribute]
class Message
{
    /**
     * @param array<string, string> $message
     */
    public function __construct(public $message) {}
}
