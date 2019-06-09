<?php

namespace Ninoman\LaravelSortable;

use Illuminate\Database\Eloquent\Builder;

trait Sortable
{
    /**
     * this getter determines initial value of sorting
     *
     * Override $startSortingFrom property to change it
     *
     * @return int
     */
    public function getSortingStartIndex(): int
    {
        return $this->startSortingFrom ?? 1;
    }

    /**
     * This getter determines column which should be used for sorting
     *
     * Override $sortIndexColumn to change it
     *
     * @return string
     */
    public function getSortIndexColumn(): string
    {
        return $this->sortIndexColumn ?? 'sort_index';
    }

    /**
     * This getter determines a column by which model sorting should be grouped (`null` stands for no grouping)
     * if you want a grouped sorting, assign a column name, by which you want a grouped sorting
     *
     * Override $sortingParentColumn property to change it
     *
     * @return string
     */
    public function getSortingParentColumn(): ?string
    {
        return $this->sortingParentColumn ?? null;
    }

    /**
     * This getter determines if the model should be assign an order on creating or not
     * set it to `false` to turn off that functionality
     *
     * Override $setSortIndexOnCreating property to change it
     *
     * @return bool
     */
    public function getSetSortingIndexOnCreating(): bool
    {
        return $this->setSortIndexOnCreating ?? true;
    }

    /**
     * This property determines if other models should be resorted if this model's sorting index was changed
     *
     * @var  bool
     */
    var $resortOthers = true;

    protected static function bootSortable()
    {
        static::observe(SortableObserver::class);
    }

    /**
     * This method is used to swap order of two models
     *
     * @param self $modelOne
     * @param self $modelTwo
     * @return void
     */
    public static function swapSort(self $modelOne, self $modelTwo): void
    {
        $sortingColumn = $modelOne->getSortIndexColumn();
        $indexOfOne = $modelOne->$sortingColumn;
        $indexOfTwo = $modelTwo->$sortingColumn;

        $modelOne->updateSortingWithoutResorting($indexOfTwo);
        $modelTwo->updateSortingWithoutResorting($indexOfOne);
    }

    /**
     * This method is used to get next sort index for a new model
     *
     * @return int
     */
    public function getNextSortIndex(): int
    {
        $builder = self::query();

        if ($this->getSortingParentColumn()) {
            $builder->where($this->getSortingParentColumn(), $this->{$this->getSortingParentColumn()});
        }

        return ($builder->latest($this->getSortIndexColumn())->first()->{$this->getSortIndexColumn()} ?? $this->getSortingStartIndex() - 1) + 1;
    }

    public function scopeSameParentChild(Builder $builder): Builder
    {
        return $this->getSortingParentColumn() ? $builder->where($this->getSortingParentColumn(), $this->{$this->getSortingParentColumn()}) : $builder;
    }

    /**
     * Use this scope to access your models sorted
     *
     * @param Builder $builder
     * @param string $direction
     * @return Builder
     */
    public function scopeSorted(Builder $builder, string $direction = 'asc'): Builder
    {
        return $builder->orderBy($this->getSortIndexColumn(), $direction);
    }

    /**
     * Use this scope to access your models sorted by desc
     *
     * @param Builder $builder
     * @return Builder
     */
    public function scopeSortedDesc(Builder $builder): Builder
    {
        return $builder->sorted('desc');
    }

    /**
     * This method will move your model up in sorted list, if it's not already there
     *
     * Returns new(or not changed) sorting index
     * @return int
     */
    public function moveSortIndexUp(): int
    {
        // if model is already on top
        if ($this->{$this->getSortIndexColumn()} == $this->getSortingStartIndex()) {
            return $this->{$this->getSortIndexColumn()};
        }

        $this->update([
            $this->getSortIndexColumn() => $this->{$this->getSortIndexColumn()} - 1
        ]);

        return $this->{$this->getSortIndexColumn()};
    }

    /**
     * This method will move your model down in sorted list, if it's not already there
     *
     * Returns new(or not changed) sorting index
     * @return int
     */
    public function moveSortIndexDown(): int
    {
        // if model is already on bottom
        if ($this->{$this->getSortIndexColumn()} == $this->getNextSortIndex() - 1) {
            return $this->{$this->getSortIndexColumn()};
        }

        $this->update([
            $this->getSortIndexColumn() => $this->{$this->getSortIndexColumn()} + 1
        ]);

        return $this->{$this->getSortIndexColumn()};
    }

    /**
     * This method will move your model onto bottom of sorting
     *
     * Returns new(or not changed) sorting index
     * @return int
     */
    public function toSortingTop(): int
    {
        $this->update([
            $this->getSortIndexColumn() => $this->getSortingStartIndex()
        ]);

        return $this->{$this->getSortIndexColumn()};
    }

    /**
     * This method will move your model onto top of sorting
     *
     * Returns new(or not changed) sorting index
     * @return int
     */
    public function toSortingBottom(): int
    {
        $this->update([
            $this->getSortIndexColumn() => $this->getNextSortIndex() - 1
        ]);

        return $this->{$this->getSortIndexColumn()};
    }

    public function turnOffResortingOfOthers(): void
    {
        $this->resortOthers = false;
    }

    public function turnOnResortingOfOthers(): void
    {
        $this->resortOthers = true;
    }

    public function updateSortingWithoutResorting(int $index): void
    {
        $this->turnOffResortingOfOthers();
        $this->update([
            $this->getSortIndexColumn() => $index
        ]);
        $this->turnOnResortingOfOthers();
    }
}