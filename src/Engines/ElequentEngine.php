<?php

namespace Elegant\DataTables\Engines;

use RuntimeException;
use Elegant\DataTables\Contracts\Engine;
use Elegant\DataTables\Engines\Concerns\InteractsWithQueryBuilder;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EloquentEngine implements Engine
{
    use InteractsWithQueryBuilder {
        search as traitSearch;
        order as traitOrder;
    }

    /**
     * @param QueryBuilder $source
     */
    public function __construct(QueryBuilder $source)
    {
        $this->original = $source;
        $this->source = clone $source;
    }

    /**
     * @inheritdoc
     */
    public function select(array $columns)
    {
        $this->source->addSelect($this->qualifyColumn($this->source, '*'));
    }

    /**
     * @inheritdoc
     */
    protected function qualifyColumn($query, $column)
    {
        return $query->qualifyColumn($column);
    }

    /**
     * @inheritdoc
     */
    protected function search($query, $column, $value, $regex, $boolean = 'or')
    {
        $model = $query->getModel();

        if ($this->isRelated($model, $column)) {
            $this->searchRelated($query, $column, $value, $regex, $boolean);
        } else {
            $this->traitSearch($query, $column, $value, $regex, $boolean);
        }
    }

    /**
     * Searchs for the related column.
     *
     * @param mixed  $query
     * @param string $column Column name
     * @param string $value
     * @param bool   $regex
     * @param string $boolean
     */
    protected function searchRelated($query, $column, $value, $regex, $boolean = 'or')
    {
        list($relation, $column) = explode('.', $column, 2);

        $query->has($relation, '>=', 1, $boolean, function ($query) use ($column, $value, $regex) {
            $this->search($query, $column, $value, $regex, 'and');
        });
    }

    /**
     * @inheritdoc
     */
    protected function order($query, $column, $dir)
    {
        $model = $query->getModel();

        if ($this->isRelated($model, $column)) {
            $this->orderRelated($query, $column, $dir);
        } else {
            $this->traitOrder($query, $column, $dir);
        }
    }

    /**
     * Orders the related column.
     *
     * @param mixed  $query
     * @param string $column Column name
     * @param string $dir
     */
    protected function orderRelated($query, $column, $dir)
    {
        $model = $query->getModel();

        order:

        list($relation, $column) = explode('.', $column, 2);

        $relation = $model->{$relation}();

        $this->joinRelated($query, $relation);

        $model = $relation->getRelated();

        if ($this->isRelated($model, $column)) {
            goto order;
        } else {
            $query->orderBy($model->qualifyColumn($this->resolveJsonColumn($column)), $dir);
        }
    }

    /**
     * Checks soft deletes on the model.
     *
     * @param object $model
     * @return bool
     */
    protected function checkSoftDeletes($model)
    {
        if (in_array(SoftDeletes::class, class_uses($model))) {
            return $model->getQualifiedDeletedAtColumn();
        } else {
            return false;
        }
    }

    /**
     * Joins the relation.
     *
     * @param mixed $query
     * @param mixed $model
     */
    protected function joinRelated($query, $model)
    {
        switch (true) {
            case $model instanceof BelongsToMany:
                $pivot = $model->getTable();
                $pivotPk = $model->getExistenceCompareKey();
                $pivotFk = $model->getQualifiedParentKeyName();
                $this->join($pivot, $pivotPk, $pivotFk);

                $related = $model->getRelated();
                $table = $related->getTable();
                $foreign = sprintf('%s.%s', $pivot, $related->getForeignKey());
                $other = $related->getQualifiedKeyName();
                $softDeletes = $this->checkSoftDeletesOnModel($related);
                $this->join($table, $foreign, $other, $softDeletes);
                break;
            case $model instanceof HasOneOrMany:
                $related = $model->getRelated();
                $table = $related->getTable();
                $foreign = $model->getQualifiedForeignKeyName();
                $other = $model->getQualifiedParentKeyName();
                $softDeletes = $this->checkSoftDeletesOnModel($related);
                $this->join($query, $table, $foreign, $other, $softDeletes);
                break;
            case $model instanceof BelongsTo:
                $related = $model->getRelated();
                $table = $related->getTable();
                $foreign = $model->getQualifiedForeignKey();
                $other = $model->getQualifiedOwnerKeyName();
                $softDeletes = $this->checkSoftDeletes($related);
                $this->join($query, $table, $foreign, $other, $softDeletes);
                break;
            default:
                throw new RuntimeException('Relation ['.get_class($model).'] is not yet supported.');
        }
    }

    /**
     * Performs join.
     *
     * @param string $table
     * @param string $foreign
     * @param string $other
     * @param string|false $softDeletes
     * @param string $type
     */
    protected function join($query, $table, $foreign, $other, $softDeletes = false)
    {
        $joins = [];
        foreach ((array) $query->getQuery()->joins as $key => $join) {
            $joins[] = $join->table;
        }

        if (!in_array($table, $joins)) {
            $query->leftJoin($table, $foreign, $other);
        }

        if (false !== $softDeletes) {
            $query->whereNull($softDeletes);
        }
    }

    /**
     * Indicates if the column is for a relationship.
     *
     * @param mixed $model
     * @param string $column
     * @return bool
     */
    protected function isRelated($model, $column)
    {
        list($relation,) = explode('.', $column);

        if (method_exists($model, $relation)) {
            return $model->{$relation}() instanceof Relation;
        } else {
            return false;
        }
    }
}
