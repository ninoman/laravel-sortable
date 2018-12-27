<?php

namespace Ninoman\LaravelSortable;

class SortableObserver
{
    public function creating($model): void
    {
        if ($model->setSortIndexOnCreating) {
            $model->{$model->sortIndexColumn} = $model->getNextSortIndex();
        }
    }

    public function deleted($model): void
    {
        $model->sameParentChild()->where($model->sortIndexColumn, '>', $model->{$model->sortIndexColumn})->decrement($model->sortIndexColumn);
    }

    public function updating($model): void
    {
        if ($model->isDirty($model->sortIndexColumn) && $model->resortOthers) {
            if ($model->{$model->sortIndexColumn}> $model->getOriginal($model->sortIndexColumn)) {
                $this->decrementSortIndex($model);
            } else {
                $this->incrementSortIndex($model);
            }
        }
    }

    private function incrementSortIndex($model): void
    {
        $model->sameParentChild()
            ->where($model->sortIndexColumn, '<', $model->getOriginal($model->sortIndexColumn))
            ->where($model->sortIndexColumn, '>=', $model->{$model->sortIndexColumn})
            ->increment($model->sortIndexColumn);
    }

    private function decrementSortIndex($model): void
    {
        $model->sameParentChild()
            ->where($model->sortIndexColumn, '>', $model->getOriginal($model->sortIndexColumn))
            ->where($model->sortIndexColumn, '<=', $model->{$model->sortIndexColumn})
            ->decrement($model->sortIndexColumn);
    }
}
