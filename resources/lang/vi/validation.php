<?php

return [
    'required' => ':attribute là bắt buộc.',
    'required_if' => ':attribute là bắt buộc khi :other là :value.',
    'string' => ':attribute không hợp lệ.',
    'integer' => ':attribute phải là số nguyên.',
    'boolean' => ':attribute phải là đúng hoặc sai.',
    'email' => ':attribute không đúng định dạng email.',
    'min' => [
        'string' => ':attribute phải có ít nhất :min ký tự.',
        'numeric' => ':attribute phải lớn hơn hoặc bằng :min.',
    ],
    'max' => [
        'string' => ':attribute không được vượt quá :max ký tự.',
        'numeric' => ':attribute không được vượt quá :max.',
    ],
    'unique' => ':attribute đã được sử dụng.',
    'exists' => ':attribute không hợp lệ.',
    'in' => ':attribute không hợp lệ.',

    // Name
    'name.required' => 'Vui lòng nhập họ và tên',
    'name.string' => 'Họ và tên không hợp lệ',
    'name.max' => 'Họ và tên không được vượt quá 255 ký tự',

    // Email
    'email.required' => 'Vui lòng nhập email',
    'email.email' => 'Email không hợp lệ',
    'email.unique' => 'Email đã được sử dụng',

    // Password
    'password.required' => 'Mật khẩu là bắt buộc',
    'password.string' => 'Mật khẩu không hợp lệ',
    'password.min' => 'Mật khẩu phải có ít nhất :min ký tự',
    'password.confirmed' => 'Xác nhận mật khẩu không khớp',

    // Identification number (VNeID / Passport)
    'identification_number.required' => 'Vui lòng nhập số VNeID/Passport',
    'identification_number.regex' => 'Số VNeID / Passport chỉ chấp nhận số',
    'identification_number.min' => 'Số VNeID / Passport không được ít hơn 6 ký tự',
    'identification_number.max' => 'Số VNeID / Passport không được vượt quá 20 ký tự',
    'identification_number.unique' => 'Số VNeID / Passport đã được sử dụng',

    // Nationality
    'nation_id.required' => 'Vui lòng chọn quốc tịch',
    'nation_id.exists' => 'Quốc tịch không hợp lệ',

    // Phone
    'phone.numeric' => 'Số điện thoại không hợp lệ',
    'phone.max' => 'Số điện thoại không được vượt quá 15 ký tự',

    // Address
    'address.string' => 'Địa chỉ không hợp lệ',
    'address.max' => 'Địa chỉ không được vượt quá 255 ký tự',

    // Avatar
    'avatar.string' => 'Avatar không hợp lệ',

    'attributes' => [
        'name' => 'Tên tenant',
        'slug' => 'Slug',
        'status' => 'Status',
        'database.driver' => 'Driver',
        'database.host' => 'Host',
        'database.port' => 'Port',
        'database.database_name' => 'Database name',
        'database.username' => 'Username',
        'database.password' => 'Password',
        'organizer.name' => 'Tên organizer',
        'organizer.email' => 'Email organizer',
        'organizer.password' => 'Password organizer',
    ],
];
