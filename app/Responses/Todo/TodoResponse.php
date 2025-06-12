<?php
declare(strict_types=1);

namespace App\Responses\Todo;

use Beauty\Http\Response\AbstractJsonResource;
use DateTimeInterface;

class TodoResponse extends AbstractJsonResource
{
    protected array $fields = ['id', 'title', 'description', 'isCompleted', 'dueDate'];

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