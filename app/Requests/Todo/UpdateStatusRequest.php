<?php
declare(strict_types=1);

namespace App\Requests\Todo;

use Beauty\Http\Request\AbstractValidatedRequest;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'TodoStatusRequest',
    required: ['is_completed'],
    properties: [
        new OAT\Property(property: 'is_completed', type: 'bool'),
    ]
)]
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
