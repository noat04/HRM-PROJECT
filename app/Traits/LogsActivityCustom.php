<?php

namespace App\Traits;

use Spatie\Activitylog\Facades\Activity;

trait LogsActivityCustom
{
    protected static function bootLogsActivityCustom()
    {
        static::created(function ($model) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($model)
                ->withProperties([
                    'attributes' => $model->getAttributes()
                ])
                ->log(class_basename($model) . ' created');
        });

        static::updated(function ($model) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($model)
                ->withProperties([
                    'old' => $model->getOriginal(),
                    'new' => $model->getChanges()
                ])
                ->log(class_basename($model) . ' updated');
        });

        static::deleted(function ($model) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($model)
                ->withProperties([
                    'attributes' => $model->getAttributes()
                ])
                ->log(class_basename($model) . ' deleted');
        });

        static::restored(function ($model) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($model)
                ->withProperties([
                    'attributes' => $model->getAttributes()
                ])
                ->log(class_basename($model) . ' restored');
        });

        static::forceDeleted(function ($model) {
            activity()
                ->causedBy(auth()->user())
                ->performedOn($model)
                ->withProperties([
                    'attributes' => $model->getAttributes()
                ])
                ->log(class_basename($model) . ' force deleted');
        });
    }
}