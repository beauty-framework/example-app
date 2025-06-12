<?php
declare(strict_types=1);

namespace App\Requests\Todo;

use Beauty\Http\Request\AbstractValidatedRequest;

class GetRequest extends AbstractValidatedRequest
{
    use HasUserTrait;

    /**
     * @return array
     */
    public function rules(): array
    {
        return [

        ];
    }
}
