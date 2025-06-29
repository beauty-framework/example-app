<?php
declare(strict_types=1);

namespace App\Responses\Todo;

use Beauty\Http\Response\AbstractJsonResource;
use DateTimeInterface;
use OpenApi\Attributes as OAT;

#[OAT\Schema(
    schema: 'TodoResponse',
    required: ['id', 'title', 'description', 'is_complete', 'due_date'],
    properties: [
        new OAT\Property(property: 'id', type: 'int'),
        new OAT\Property(property: 'title', type: 'string'),
        new OAT\Property(property: 'description', type: 'string'),
        new OAT\Property(property: 'is_complete', type: 'bool'),
        new OAT\Property(property: 'due_date', type: 'datetime'),
    ]
)]
class TodoResponse extends AbstractJsonResource
{
    /**
     * @var array|string[]
     */
    protected array $fields = ['id', 'title', 'description', 'isCompleted', 'dueDate'];

    /**
     * @param int $id
     * @param string $title
     * @param string|null $description
     * @param bool $isCompleted
     * @param DateTimeInterface|null $dueDate
     */
    public function __construct(
        public int $id,
        public string $title,
        public string|null $description,
        public bool $isCompleted,
        public DateTimeInterface|null $dueDate
    )
    {
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'is_completed' => $this->isCompleted,
            'due_date' => $this->dueDate?->format('Y-m-d H:i:s'),
        ];
    }
}