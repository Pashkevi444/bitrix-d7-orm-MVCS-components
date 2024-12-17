<?php

namespace Dto;


final readonly class IndexTestDataDTO
{
    /**
     * @param int $id
     * @param string $name
     * @param int $favoriteId
     * @param array $testMultipleProperty
     * @param string $testEnumValue
     */
    public function __construct(
        public int $id,
        public string $name,
        public int $favoriteId,
        public array $testMultipleProperty,
        public string $testEnumValue
    ) {
        if (empty($name)) {
            throw new \InvalidArgumentException("Name cannot be empty.");
        }
    }
}
