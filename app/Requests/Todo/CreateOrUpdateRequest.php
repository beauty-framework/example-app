<?php
declare(strict_types=1);

namespace App\Requests\Todo;

use Beauty\Http\Request\AbstractValidatedRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'TodoRequest',
    required: ['title'],
    properties: [
        new OAT\Property(property: 'title', type: 'string', format: 'string'),
        new OAT\Property(property: 'description', type: 'string', format: 'string'),
        new OAT\Property(property: 'due_date', type: 'string', format: 'date'),
        new OAT\Property(property: 'is_completed', type: 'bool'),
    ]
)]
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
