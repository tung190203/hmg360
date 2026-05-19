@extends('backend.index')
@use('\Illuminate\Support\HtmlString')

@section('title')
    {{ $group->exists ? 'Sửa group' : 'Thêm mới group' }}
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('backend_user') }}">User</a></li>
    <li class="breadcrumb-item"><a href="{{ route('backend_group') }}">Group</a></li>
    <li class="breadcrumb-item active">{{ $group->exists ? 'Sửa group' : 'Thêm mới group' }}</li>
@endsection

@section('content')

    <script src="{{ asset('backend_assets/js/globals.js') }}"></script>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="float-right mb-3">
                        @can('group/' . ($group->exists ? 'edit' : 'add'))
                            <x-forms.button-save/>
                        @endcan
                        @if($group->exists)
                            @can('group/add')
                                <x-forms.button-url title="Thêm mới" class="btn-info" icon="fa fa-plus"
                                                    url="{{ route('backend_group_create') }}"/>
                            @endcan
                            @can('group/delete')
                                <x-forms.button-url title="Xóa" class="btn-danger" icon="fa fa-trash"
                                                    url="{{ route('backend_group_delete', $group->id) }}"/>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
            <div class="card card-primary">
                <form action="{{ route('backend_group_save', $group) }}" method="post"
                      enctype="multipart/form-data"
                      class="form-horizontal" id="formDataGrid">
                    @csrf
                    <div class="card-body" style="max-width: 700px; margin: auto">

                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label">
                                Tên group
                            </label>
                            <div class="col-sm-12">
                                <input type="text" name="name" value="{{ old('name') ?: $group->name }}" id="name"
                                       class="form-control" placeholder="Tên group">
                                @if($errors->has('name'))
                                    <div class="text-danger">{{ $errors->first('name') }}</div>
                                @endif
                            </div>
                        </div>

                        <div id="scope-container">
                            <div class="scope-item" data-permission="category">
                                <x-forms.select-multiple
                                name="scope_data_category"
                                label="Category Scope"
                                :options="$category->pluck('name','id')"
                                :selected="$group->scope_data['category'] ?? []"
                                :messages="$errors->get('scope_data.category')"
                                help="Chọn những danh mục mà group này được phép thao tác."
                                />
                            </div>

                            <div class="scope-item" data-permission="project">
                                <x-forms.select-multiple
                                name="scope_data_project"
                                label="Project Scope"
                                :options="$projects->pluck('name','id')"
                                :selected="$group->scope_data['project'] ?? []"
                                :messages="$errors->get('scope_data.project')"
                                help="Chọn những project mà group này được phép thao tác."
                                />
                            </div>

                            <div class="scope-item" data-permission="post">
                                <x-forms.select-multiple
                                name="scope_data_post"
                                label="Posts Scope"
                                :options="$posts->pluck('name','id')"
                                :selected="$group->scope_data['post'] ?? []"
                                :messages="$errors->get('scope_data.post')"
                                help="Chọn những bài viết mà group này được phép thao tác."
                                />
                            </div>

                            <div class="scope-item" data-permission="investment_guide">
                                <x-forms.select-multiple
                                name="scope_data_investment_guide"
                                label="Investment Guide Scope"
                                :options="$investment_guides->pluck('name','id')"
                                :selected="$group->scope_data['investment_guide'] ?? []"
                                :messages="$errors->get('scope_data.investment_guide')"
                                help="Chọn những cẩm nang đâu tư mà group này được phép thao tác."
                                />
                            </div>

                            <div class="scope-item" data-permission="menu">
                                <x-forms.select-multiple
                                name="scope_data_menu"
                                label="Menu Scope"
                                :options="$menus->pluck('name','id')"
                                :selected="$group->scope_data['menu'] ?? []"
                                :messages="$errors->get('scope_data.menu')"
                                help="Chọn những menu mà group này được phép thao tác."
                                />
                            </div>

                            <div class="scope-item" data-permission="popup">
                                <x-forms.select-multiple-image
                                name="scope_data_popup"
                                label="Popup Scope"
                                :options="$popups->pluck('image','id')"
                                :selected="$group->scope_data['popup'] ?? []"
                                :messages="$errors->get('scope_data.popup')"
                                help="Chọn những popup mà group này được phép thao tác."
                                displayType="image"
                                imageHeight="50px"
                                />
                            </div>

                            <div class="scope-item" data-permission="user">
                                <x-forms.select-multiple
                                name="scope_data_user"
                                label="User Scope"
                                :options="$users->pluck('name','id')"
                                :selected="$group->scope_data['user'] ?? []"
                                :messages="$errors->get('scope_data.user')"
                                help="Chọn những user mà group này được phép thao tác."
                                />
                            </div>
                        </div>

                        {{-- <div class="frm-grid">
                            <div>
                                <label>Permission Grant</label>
                                @if($errors->has('permission'))
                                    <div class="text-danger">{{ $errors->first('permission') }}</div>
                                @endif
                                <div class="backend-perm-tree js-permission_tree">
                                    <ul>
                                        @foreach($permission_configs as $module_key => $permission_data)
                                            <li>
                                                <input type="checkbox" 
                                                       name="permission[{{ $module_key }}]"
                                                       id="permission_{{ $module_key }}" 
                                                       value="1"
                                                       data-module="{{ $module_key }}"
                                                       class="permission-checkbox"
                                                       @if(in_array($module_key , ($group->permission_data ?? [])))
                                                           checked="checked"
                                                        @endif
                                                >
                                                <label class="label-inline" for="permission_{{ $module_key }}">
                                                    {{ $permission_data['label'] }}
                                                </label>
                                                @if(!empty($permission_data['items']))
                                                    <ul>
                                                        @foreach($permission_data['items'] as $per_lv1_key => $per_lv1_value)
                                                            <li>
                                                                <input type="checkbox"
                                                                       name="permission[{{ $module_key }}][{{ $per_lv1_key }}]"
                                                                       id="permission_{{ $module_key }}_{{ $per_lv1_key }}"
                                                                       value="1"
                                                                       @if(in_array($module_key . '/' . $per_lv1_key , ($group->permission_data ?? [])))
                                                                           checked="checked"
                                                                        @endif
                                                                >
                                                                <label class="label-inline"
                                                                       for="permission_{{ $module_key }}_{{ $per_lv1_key }}">
                                                                    {{ $per_lv1_value }}
                                                                </label>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div> --}}
                        <div class="frm-grid">
                            <div>
                                <label>Permission Grant</label>
                                @if($errors->has('permission'))
                                    <div class="text-danger">{{ $errors->first('permission') }}</div>
                                @endif
                                <div class="backend-perm-tree js-permission_tree">
                                    <ul>
                                        @foreach($permission_configs as $module_key => $permission_data)
                                            @php
                                                $requireSuperAdmin = $permission_data['super_admin_only'] ?? false;
                                                $canView = !$requireSuperAdmin || auth('web')->user()->is_super_admin;
                                            @endphp
                                            
                                            @if($canView)
                                                <li>
                                                    <input type="checkbox" 
                                                           name="permission[{{ $module_key }}]"
                                                           id="permission_{{ $module_key }}" 
                                                           value="1"
                                                           data-module="{{ $module_key }}"
                                                           class="permission-checkbox"
                                                           @if(in_array($module_key , ($group->permission_data ?? [])))
                                                               checked="checked"
                                                            @endif
                                                    >
                                                    <label class="label-inline" for="permission_{{ $module_key }}">
                                                        {{ $permission_data['label'] }}
                                                    </label>
                                                    @if(!empty($permission_data['items']))
                                                        <ul>
                                                            @foreach($permission_data['items'] as $per_lv1_key => $per_lv1_value)
                                                                <li>
                                                                    <input type="checkbox"
                                                                           name="permission[{{ $module_key }}][{{ $per_lv1_key }}]"
                                                                           id="permission_{{ $module_key }}_{{ $per_lv1_key }}"
                                                                           value="1"
                                                                           @if(in_array($module_key . '/' . $per_lv1_key , ($group->permission_data ?? [])))
                                                                               checked="checked"
                                                                            @endif
                                                                    >
                                                                    <label class="label-inline"
                                                                           for="permission_{{ $module_key }}_{{ $per_lv1_key }}">
                                                                        {{ $per_lv1_value }}
                                                                    </label>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </li>
                                            @endif
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>

@endsection

@section('bottom')
    <style>
        .backend-perm-tree {
            padding: 0 0 20px 0;
        }

        .backend-perm-tree ul {
            list-style-type: none;
            padding: 0;
            margin: 10px 0 0 0;
        }

        .backend-perm-tree li {
            padding: 5px 0 5px 30px;
            position: relative;
        }

        .backend-perm-tree li:before {
            content: "";
            display: block;
            position: absolute;
            top: 0;
            left: 0;
            width: 1px;
            height: 100%;
            background: #ddd;
        }

        .backend-perm-tree li:first-child:before {
            height: calc(100% + 10px);
            top: -10px;
        }

        .backend-perm-tree li:last-child:before {
            height: 13px;
        }

        .backend-perm-tree li:last-child:first-child:before {
            height: 22px;
        }

        .backend-perm-tree li:after {
            content: "";
            display: block;
            position: absolute;
            top: 12px;
            left: 0;
            width: 28px;
            height: 1px;
            background: #ddd;
        }

        .backend-perm-tree li > ul {
            display: none;
        }

        .backend-perm-tree li > input {
            cursor: pointer;
            vertical-align: -2px;
            position: relative;
            z-index: 2;
            margin-right: 3px;
            margin-left: 0;
        }

        .backend-perm-tree li > label {
            font-style: italic;
            color: #da0000;
        }

        .backend-perm-tree li > input:checked + label {
            font-weight: bold;
            font-style: normal;
            color: #007bda;
            margin-bottom: 0;
        }

        .backend-perm-tree li > input:checked + label + ul {
            display: block;
        }

        .scope-item {
            display: none;
        }

        .scope-item.active {
            display: block;
        }
    </style>
    <script>
        $(document).ready(function () {
            function toggleScopes() {
                $('.scope-item').each(function() {
                    const permission = $(this).data('permission');
                    const checkbox = $(`#permission_${permission}`);
                    
                    if (checkbox.length && checkbox.is(':checked')) {
                        $(this).addClass('active');
                    } else {
                        $(this).removeClass('active');
                    }
                });
            }

            toggleScopes();

            $('.permission-checkbox').on('change', function() {
                toggleScopes();
            });

            $('input[type="checkbox"]').change(function () {
                if (!$(this).is(':checked')) {
                    $(this).closest('li').find('ul input[type="checkbox"]').prop('checked', false);
                }
                toggleScopes();
            });
        });
    </script>
@endsection