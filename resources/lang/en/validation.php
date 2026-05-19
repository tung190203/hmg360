<?php

return [
    // Name
    'name.required' => 'Please enter your full name',
    'name.string' => 'Full name is invalid',
    'name.max' => 'Full name must not exceed 255 characters',

    // Email
    'email.required' => 'Please enter your email',
    'email.email' => 'Email is not valid',
    'email.unique' => 'This email has already been taken',

    // Password
    'password.required' => 'Password is required',
    'password.string' => 'Password is invalid',
    'password.min' => 'Password must be at least :min characters',
    'password.confirmed' => 'Password confirmation does not match',

    // Identification number (VNeID / Passport)
    'identification_number.required' => 'Please enter your VNeID/Passport number',
    'identification_number.regex' => 'VNeID / Passport number must contain only numbers',
    'identification_number.max' => 'VNeID / Passport number must not exceed 20 characters',
    'identification_number.unique' => 'This VNeID / Passport number has already been used',

    // Nationality
    'nation_id.required' => 'Please select your nationality',
    'nation_id.exists' => 'Selected nationality is invalid',

    // Phone
    'phone.numeric' => 'Phone number is invalid',
    'phone.max' => 'Phone number must not exceed 15 characters',

    // Address
    'address.string' => 'Address is invalid',
    'address.max' => 'Address must not exceed 255 characters',

    // Avatar
    'avatar.string' => 'Avatar is invalid',
];
