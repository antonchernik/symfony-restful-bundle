<?php

declare(strict_types=1);

namespace RestfulBundle\Dictionary;

use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Unique;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Uuid;

class ValidationMessages extends Messages
{
    public const VALIDATION__GENERAL = 'validation.errors';
    public const VALIDATION__NOT_BLANK = 'validation.not_blank';
    public const VALIDATION__NOT_NULL = 'validation.not_null';
    public const VALIDATION__TYPE = 'validation.type';
    public const VALIDATION__LENGTH__MIN = 'validation.length.min';
    public const VALIDATION__LENGTH__MAX = 'validation.length.max';
    public const VALIDATION__CHOICE = 'validation.choice';
    public const VALIDATION__UUID = 'validation.uuid';
    public const VALIDATION__URL = 'validation.url';
    public const VALIDATION__COUNT__MIN = 'validation.count.min';
    public const VALIDATION__COUNT__MAX = 'validation.count.max';
    public const VALIDATION__POSITIVE_OR_ZERO = 'validation.positive_or_zero';
    public const VALIDATION__POSITIVE = 'validation.positive';
    public const VALIDATION__SHOULD_NOT_BE_EQUAL = 'validation.should_not_be_equal_to';
    public const VALIDATION__RANGE__MAX = 'validation.range.max';
    public const VALIDATION__RANGE__MIN = 'validation.range.min';
    public const VALIDATION__RANGE__NOT_IN_RANGE = 'validation.range.not_in_range';
    public const VALIDATION__RANGE__INVALID_NUMBER = 'validation.range.invalid_number';
    public const VALIDATION__COLLECTION__NOT_UNIQUE = 'validation.collection.not_unique';

    public const MESSAGE_MAP = [
        NotBlank::IS_BLANK_ERROR => self::VALIDATION__NOT_BLANK,
        NotNull::IS_NULL_ERROR => self::VALIDATION__NOT_NULL,
        Count::TOO_FEW_ERROR => self::VALIDATION__COUNT__MIN,
        Count::TOO_MANY_ERROR => self::VALIDATION__COUNT__MAX,
        Length::TOO_SHORT_ERROR => self::VALIDATION__LENGTH__MIN,
        Length::TOO_LONG_ERROR => self::VALIDATION__LENGTH__MAX,
        Range::TOO_HIGH_ERROR => self::VALIDATION__RANGE__MAX,
        Range::TOO_LOW_ERROR => self::VALIDATION__RANGE__MIN,
        Range::NOT_IN_RANGE_ERROR => self::VALIDATION__RANGE__NOT_IN_RANGE,
        Range::INVALID_CHARACTERS_ERROR => self::VALIDATION__RANGE__INVALID_NUMBER,
        Choice::NO_SUCH_CHOICE_ERROR => self::VALIDATION__CHOICE,
        Uuid::TOO_SHORT_ERROR => self::VALIDATION__UUID,
        Uuid::TOO_LONG_ERROR => self::VALIDATION__UUID,
        Uuid::INVALID_CHARACTERS_ERROR => self::VALIDATION__UUID,
        Uuid::INVALID_HYPHEN_PLACEMENT_ERROR => self::VALIDATION__UUID,
        Uuid::INVALID_VERSION_ERROR => self::VALIDATION__UUID,
        Uuid::INVALID_VARIANT_ERROR => self::VALIDATION__UUID,
        PositiveOrZero::TOO_LOW_ERROR => self::VALIDATION__POSITIVE_OR_ZERO,
        Positive::TOO_LOW_ERROR => self::VALIDATION__POSITIVE,
        NotEqualTo::IS_EQUAL_ERROR => self::VALIDATION__SHOULD_NOT_BE_EQUAL,
        Type::INVALID_TYPE_ERROR => self::VALIDATION__TYPE,
        Unique::IS_NOT_UNIQUE => self::VALIDATION__COLLECTION__NOT_UNIQUE,
        Url::INVALID_URL_ERROR => self::VALIDATION__URL,
    ];
}
