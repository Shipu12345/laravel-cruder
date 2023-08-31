<?php

namespace Shipu\Cruder\Contracts;

interface Slugger
{
    /**
     * Get the ID of the Model being translated
     */
    public function getResourceIdFromTranslatedSlug(string $slug): int;

    /**
     * Get translated slug based on resource ID and language ID
     */
    public function getTranslatedSlugFromResourceId(int $id, int $language_id): string;

    /**
     * Get resource ID from a non-translated slug
     */
    public function getResourceIdFromSlug(string $slug): int;

    /**
     * Get the slug of a non-translated resource ID
     */
    public function getSlugFromResourceId(int $id): string;
}
