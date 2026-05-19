<?php

namespace App\Transformers;

class ProjectTransformer
{
    public static function transform($project)
    {
        return [
            'id'          => $project->id,
            'name'        => $project->name,
            'lat'         => $project->lat,
            'lng'         => $project->lng,
            'type'        => $project->type ? [
                'id'   => $project->type->id,
                'name' => $project->type->name,
            ] : null,
            'industry'    => $project->industry ? [
                'id'   => $project->industry->id,
                'name' => $project->industry->name,
            ] : null,
            'price'       => $project->price,
            'link'        => $project->link,
            'image'       => $project->image ?? null,
            'districts'   => $project->districts->map(function ($district) {
                return [
                    'id'   => $district->id,
                    'name' => $district->name,
                ];
            }),
            'description' => $project->description,
        ];
    }
}
