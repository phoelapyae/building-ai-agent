<?php

namespace App\Ai\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class CompactAfter
{
    public function __construct(
        public readonly int $threshold,
    ) {}
}
