<?php
declare(strict_types=1);

namespace App\Requests\Todo;

use Beauty\Http\Request\AbstractValidatedRequest;

class UpdateStatusRequest extends AbstractValidatedRequest
{
    use HasUserTrait;

    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'is_completed' => ['required', 'boolean'],
        ];
    }
}
