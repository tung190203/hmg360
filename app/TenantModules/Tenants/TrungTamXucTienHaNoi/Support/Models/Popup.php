<?php

namespace App\TenantModules\Tenants\TrungTamXucTienHaNoi\Support\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class Popup extends Model
{
    protected $connection = 'tenant';

    protected $table = 'popups';
    protected $fillable = [
        'image',
        'link'
    ];

    public static function makeOptionColumnButton(): array
    {
        $options = [];

        foreach (['edit', 'delete'] as $action) {
            if (Gate::allows('popup/' . $action)) {
                $options[$action] = [
                    'route' => 'tenant.trung_tam_xuc_tien_ha_noi.popup.' . $action,
                ];
            }
        }

        return $options;
    }

    public function draft()
    {
        return $this->hasOne(Popup::class, 'parent_id')->where('is_draft', true);
    }

    public function parent()
    {
        return $this->belongsTo(Popup::class, 'parent_id');
    }
    public function scopeVisibleFor($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            if ($user->is_super_admin || $user->is_approve) {
                $q->where(function ($sub) {
                    $sub->where('is_draft', false)
                        ->where(function ($s) {
                            $s->whereDoesntHave('draft')
                            ->orWhereHas('draft', function ($d) {
                                $d->where('status_approve', 'rejected');
                            });
                        });
                })
                ->orWhere(function ($sub) {
                    $sub->where('is_draft', true)
                        ->where('status_approve', '!=', 'rejected');
                });
            } else {
                $q->where(function ($sub) {
                    $sub->where('is_draft', false)
                        ->whereDoesntHave('draft');
                })
                ->orWhere(function ($sub) {
                    $sub->where('is_draft', true);
                });
            }
        });
    }
}
