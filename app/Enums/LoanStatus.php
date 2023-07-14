<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * @method static static OptionOne()
 * @method static static OptionTwo()
 * @method static static OptionThree()
 */
final class LoanStatus extends Enum
{
    const PENDING = 'pending';

    const CANCEL = 'cancel';

    const APPROVED = 'approved';

    const PAID = 'paid';

    const REJECTED = 'rejected';
}
