<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class User extends Authenticatable
{
    use HasFactory, Notifiable, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(['remember_token', 'updated_at']);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar',
        'email_verified_at',
        'password',
        'password',
        'is_approve',
        'remember_token',
        'is_super_admin',
        'status',
        'group_id',
        'approval_level',
        'max_approval',
        'is_draft',
        'main_id',
        'status_approve'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 0;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    const ROOT_ADMIN_ID = 1;
    const PATTERN_PASSWORD = [
        'PATTERN' => '/^(?=.*[A-Z])(?=.*[!@#$%^&*(),.?":{}|<>]).+$/',
        'MESSAGE' => 'Mật khẩu phải lớn hơn 8 ký tự và chứa ít nhất 1 chữ viết hoa, 1 ký tự đặc biệt'
    ];

    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'id');
    }


    public function getAllPermissionsFromGroup(): array
    {
        $group = $this->group;
        if (!$group) return [];

        $permissions = $group->permission_data ?? [];
        if (is_string($permissions)) {
            $permissions = json_decode($permissions, true);
        }

        return $permissions ?? [];
    }
    public function getGroupNameAttribute(): string
    {
        if ($this->isSuperAdmin()) {
            return 'Super Admin';
        }

        return data_get($this, 'group.name', 'Guest');
    }

    public function isSuperAdmin(): bool
    {
        return $this->id === self::ROOT_ADMIN_ID || $this->is_super_admin;
    }

    /**
     * Kiểm tra quyền chung (không phụ thuộc record)
     */
    public function hasPermission(string $perm_key): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $permission_data = data_get($this, 'group.permission_data', []);

        return in_array($perm_key, $permission_data, true);
    }

    /**
     * Lấy scope theo module từ group.scope_data
     */
    public function getScope(string $perm_key): ?array
    {
        if ($this->isSuperAdmin()) {
            return null; // full
        }

        $scope_data = data_get($this, 'group.scope_data', []);

        // Tách resource từ key (vd: "project/edit" => "project")
        $resource = explode('/', $perm_key)[0] ?? null;
        if (!$resource) {
            return null;
        }

        return $scope_data[$resource] ?? null;
    }

    /**
     * Kiểm tra có thể thao tác trên record cụ thể hay không
     */
    public function canDoOn(string $permissionKey, ?int $recordId = null): bool
    {
        if (!$this->hasPermission($permissionKey)) {
            return false;
        }

        $module = explode('/', $permissionKey)[0];
        $scopeData = $this->getScopeData();

        if (!array_key_exists($module, $scopeData)) {
            return false;
        }

        $scope = $scopeData[$module];

        if (empty($scope)) {
            return true;
        }

        if ($recordId !== null) {
            return in_array($recordId, $scope);
        }

        return true;
    }

    public function getScopeData(): array
    {
        $group = $this->group;
        if (!$group) return [];

        $data = $group->scope_data ?? [];
        if (is_string($data)) {
            $data = json_decode($data, true);
        }

        return $data ?? [];
    }

    /**
     * Gợi ý các button action cho user
     */
    public static function makeOptionColumnButton(): array
    {
        $options = [];

        foreach (['edit', 'delete'] as $action) {
            if (Gate::allows('user/' . $action)) {
                $options[$action] = [
                    'route' => 'backend_user_' . $action,
                ];
            }
        }

        return $options;
    }
    public function draft()
    {
        return $this->hasOne(User::class, 'main_id')->where('is_draft', true);
    }

    public function main()
    {
        return $this->belongsTo(User::class, 'main_id');
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
