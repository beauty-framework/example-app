<?php
declare(strict_types=1);

namespace App\Requests\Todo;

use Beauty\Http\Request\AbstractValidatedRequest;

class CreateOrUpdateRequest extends AbstractValidatedRequest
{
    use HasUserTrait;

    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'title' => ['required'],
            'description' => ['nullable'],
            'due_date' => ['nullable', 'date'],
            'is_completed' => ['nullable', 'boolean'],
        ];
    }
}
