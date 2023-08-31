<?php

namespace Shipu\Cruder;

use Cocur\Slugify\Slugify;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Shipu\Cruder\Contracts\Sluggable;
use Shipu\Cruder\Exceptions\MissingRelationDataException;
use Shipu\Cruder\Exceptions\MissingSlugFieldException;

trait Crudable
{
    protected array $relation = [];

    protected $withHasMany;

    protected $withBelongsToMany;

    protected $model;

    public function raw(): Model
    {
        return $this->model;
    }

    /**
     * Get a single item or collection
     */
    public function get(int $id = null): Model|Collection
    {
        if (! is_null($id)) {
            return $this->find($id);
        }

        return $this->model->get();
    }

    /**
     * Returns the first row of the selected resource
     */
    public function first(): Model
    {
        return $this->model->first();
    }

    /**
     * Adds a chainable where statement
     *
     * @param  array|mixed  $params
     * @return $this self
     */
    public function where(...$params): static
    {
        $this->model = $this->model->where(...$params);

        return $this;
    }

    /**
     * Get paginated collection
     */
    public function paginate(int $perPage): Collection
    {
        return $this->model->paginate($perPage);
    }

    /**
     * Alias of model find
     */
    public function find(int $id): Model
    {
        return $this->model->find($id);
    }

    /**
     * Retrieve single trashed item or all
     */
    public function getTrash(int $id = null): Model|Collection
    {
        if (! is_null($id)) {
            return $this->getTrashedItem($id);
        }

        return $this->model->onlyTrashed()->get();
    }

    /**
     * Return single trashed item
     */
    public function getTrashedItem(int $id): Model
    {
        return $this->model->withTrashed()->find($id);
    }

    /**
     * Set relationship for retrieving model and relations
     *
     * @return self
     */
    public function setRelation(array $relation): static
    {
        $this->model = $this->model->with($relation);

        return $this;
    }

    /**
     * Same as setRelation but accepts strings and arrays
     */
    public function with(array|string $relations): Crudable
    {
        return $this->setRelation(is_string($relations) ? func_get_args() : $relations);
    }

    /**
     * Order the collection you pull
     *
     * @param  string  $order default asc
     * @return self
     */
    public function orderBy(string $field, string $order = 'asc'): static
    {
        $this->model = $this->model->orderBy(...func_get_args());

        return $this;
    }

    /**
     * Create new database entry including related models
     *
     * @throws MissingRelationDataException|MissingSlugFieldException
     */
    public function create(array $data): Model
    {
        $model = $this->model->create($this->checkForSlug($data));
        //check for hasMany
        if ($this->validateRelationData($this->withHasMany, 'many')) {
            $model->{$this->withHasMany['relation']}()->saveMany($this->withHasMany['data']);
        }
        //check for belongsToMany
        if ($this->validateRelationData($this->withBelongsToMany, 'tomany')) {
            $model->{$this->withBelongsToMany['relation']}()->sync($this->withBelongsToMany['data']);
        }

        return $model;
    }

    /**
     * Update Model
     *
     * @throws MissingSlugFieldException
     */
    public function update($id, array $data, $return_model = false): Model|bool
    {
        $model = $this->find($id);
        if ($return_model) {
            $model->update($this->checkForSlug($data));

            return $model;
        }

        return $model->update($this->checkForSlug($data));
    }

    /**
     * Delete model either soft or hard delete
     */
    public function delete(int $id, bool $hardDelete = false): bool
    {
        if ($hardDelete) {
            return $this->model->withTrashed()->find($id)->forceDelete($id);
        }

        return $this->model->find($id)->delete($id);
    }

    /**
     * Restore a previously soft deleted model
     */
    public function restore(int $id): bool
    {
        return $this->model->withTrashed()->find($id)->restore();
    }

    /**
     * Set related models that need to be created
     * for a hasMany relationship
     *
     * @return self
     */
    public function withHasMany(array $data, string $relatedModel, $relation_name): static
    {
        $this->withHasMany['relation'] = $relation_name;
        foreach ($data as $k => $v) {
            $this->withHasMany['data'][] = new $relatedModel($v);
        }

        return $this;
    }

    /**
     * Set related models for belongsToMany relationship
     *
     * @return self
     */
    public function withBelongsToMany(array $data, $relation): static
    {
        $this->withBelongsToMany = [
            'data' => $data,
            'relation' => $relation,
        ];

        return $this;
    }

    /**
     * Handle a file upload
     *
     * @param  string  $fieldname
     * @return string filename
     *
     * @throws Exception
     */
    public function handleUpload(Request $request, $fieldname = 'photo', string $folder = 'images', string $storage_disk = 'public', $randomize = true): string
    {
        if (is_null($request->file($fieldname)) || ! $request->file($fieldname)->isValid()) {
            throw new Exception(trans('crud.invalid_file_upload'));
        }
        //Get filename
        $basename = basename($request->file($fieldname)->getClientOriginalName(), '.'.$request->file($fieldname)->getClientOriginalExtension());
        if ($randomize) {
            $filename = Str::slug($basename).'_'.uniqid().'.'.$request->file($fieldname)->getClientOriginalExtension();
        } else {
            $filename = Str::slug($basename).'.'.$request->file($fieldname)->getClientOriginalExtension();
        }
        //Move file to location
        $request->file($fieldname)->storeAs($folder, $filename, $storage_disk);

        return $filename;
    }

    /**
     * Handle uploaded file object
     *
     * @return string $filename
     */
    public function handleUploadedFile(UploadedFile $file, string $folder = 'images', string $storage_disk = 'public', bool $randomize = true): string
    {
        //Get filename
        $basename = basename($file->getClientOriginalName(), '.'.$file->getClientOriginalExtension());
        if ($randomize) {
            $filename = Str::slug($basename).'_'.uniqid().'.'.$file->getClientOriginalExtension();
        } else {
            $filename = Str::slug($basename).'.'.$file->getClientOriginalExtension();
        }
        //Move file to location
        $file->storeAs($folder, $filename, $storage_disk);

        return $filename;
    }

    /**
     * @throws MissingRelationDataException
     */
    private function validateRelationData($related_data, $type): bool
    {
        //Check if data attribute was set
        if (! is_null($this->withHasMany) && $type == 'many') {
            if (! isset($this->withHasMany['relation']) || ! isset($this->withHasMany['data'])) {
                throw new MissingRelationDataException('HasMany Relation');
            }

            return true;
        }
        if (! is_null($this->withBelongsToMany) && $type == 'tomany') {
            if (! isset($this->withBelongsToMany['relation']) || ! isset($this->withBelongsToMany['data'])) {
                throw new MissingRelationDataException('BelongsToMany Relation');
            }

            return true;
        }

        return false;
    }

    /**
     * Generate URL slug from given string
     */
    public function generateSlug(string $name): string
    {
        if (config('crudable.localized_slugs')) {
            $slugify = new Slugify();
            $slugify->activateRuleSet(config('crudable.localization_rule'));

            return $slugify->slugify($name);
        }

        return Str::slug($name);
    }

    /**
     * @throws MissingSlugFieldException
     */
    private function checkForSlug(array $data): array
    {
        //Don't use slugs
        if (! $this instanceof Sluggable) {
            return $data;
        }
        //Check if slug field is set
        if (! isset($this->slug_field)) {
            throw new MissingSlugFieldException('The slug_field is required');
        }
        //Check if current translation contains a sluggable field
        if (array_key_exists($this->slug_field, $data)) {
            $data[$this->slug_name ?? 'slug'] = $this->generateSlug($data[$this->slug_field]);
        }

        return $data;
    }
}
