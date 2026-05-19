<?php

return [
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
];
