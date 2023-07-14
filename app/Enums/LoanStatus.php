<?php

declare(strict_types=1);

namespace App\Enums;

use BenSampo\Enum\Enum;

final class LoanStatus extends Enum
{
    const PENDING = 'pending';

    const CANCEL = 'cancel';

    const APPROVED = 'approved';

    const PAID = 'paid';

    const REJECTED = 'rejected';
}
