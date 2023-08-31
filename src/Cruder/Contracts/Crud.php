<?php

namespace Shipu\Cruder\Contracts;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

interface Crud
{
    /**
     * Return the Eloquent model from the service
     */
    public function raw(): Model;

    /**
     * Include your where statement here with variable parameters
     *
     * @param  mixed  $params
     */
    public function where(...$params);

    /**
     * Get a single item or a collection of items.
     * Alias of find method when used with ID
     */
    public function get(int $id = null): Model|Collection|null;

    /**
     * Returns the first row of the selected resource
     */
    public function first(): ?Model;

    /**
     * Get a single item
     */
    public function find(int $id): Model;

    /**
     * Paginate collection result
     *
     * @param  int  $perPage defines the number of items per page
     */
    public function paginate(int $perPage);

    /**
     * Get single item or all items from trash if ID is null
     */
    public function getTrash(int $id = null): Model|Collection;

    /**
     * Get single trashed item
     */
    public function getTrashedItem(int $id): Model;

    /**
     * Set the related data that should be eager loaded
     */
    public function setRelation(array $relation): Crud;

    /**
     * Synonymous for setRelation but accepts strings as well as arrays
     */
    public function with(array|string $relations): Crud;

    /**
     * Use ordering in your query
     *
     * @param  string  $field ordering field
     * @param  string  $order ordering direction asc is default
     */
    public function orderBy(string $field, string $order = 'asc');

    /**
     * Create new entry
     */
    public function create(array $data): Model;

    /**
     * Update model. Make sure fillable is set on the model
     *
     * @param  int  $id of model you want to update
     * @param  array  $data of model data that should be updated
     * @param  bool  $return_model set to true if you need a model instance back
     */
    public function update(int $id, array $data, bool $return_model = false): Model|bool;

    /**
     * Delete item either by softdelete or harddelete
     */
    public function delete(int $id, bool $hardDelete = false): bool;

    /**
     * Restore a soft deleted model
     */
    public function restore(int $id): bool;

    /**
     * Set hasMany relationship by adding the related model, data and
     * relation name
     */
    public function withHasMany(array $data, string $relatedModel, string $relation): Crud;

    /**
     * Add the belongsToMany relationship data to be synced and define
     * the relationship name
     */
    public function withBelongsToMany(array $data, string $relation): Crud;

    /**
     * Handle a file or photo upload
     *
     * @param  string  $field_name upload field name
     * @param  string  $folder storage folder
     * @param  string  $storage_disk storage disk to be used
     * @param  bool  $randomize to randomize the filename
     * @return string filename
     */
    public function handleUpload(Request $request, string $field_name = 'photo', string $folder = 'images', string $storage_disk = 'public', bool $randomize = true): string;

    /**
     * Handle uploaded file object
     *
     * @return string $filename
     */
    public function handleUploadedFile(UploadedFile $file, string $folder = 'images', string $storage_disk = 'public', bool $randomize = true): string;
}
