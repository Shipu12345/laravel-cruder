<?php

namespace Shipu\Cruder\Contracts;

interface Slugger
{
    /**
     * Get the ID of the Model being translated
     *
     * @param string $slug
     * @return integer
     */
    public function getResourceIdFromTranslatedSlug(string $slug): int;

    /**
     * Get translated slug based on resource ID and language ID
     *
     * @param integer $id
     * @param integer $language_id
     * @return string
     */
    public function getTranslatedSlugFromResourceId(int $id, int $language_id): string;

    /**
     * Get resource ID from a non-translated slug
     *
     * @param string $slug
     * @return integer
     */
    public function getResourceIdFromSlug(string $slug): int;

    /**
     * Get the slug of a non-translated resource ID
     *
     * @param integer $id
     * @return string
     */
    public function getSlugFromResourceId(int $id): string;
}
