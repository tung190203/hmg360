@props(['name', 'value', 'label', 'messages', 'type', 'help'])

<div class="form-group row">
    <label class="col-sm-3 col-form-label" for="{{ $name }}">
        {{ $label }}
    </label>
    <div class="col-sm-9">
        <textarea id="{{ $name }}_input" class="form-control d-none"
                  name="{{ $name }}">{{ $value }}</textarea>

        @if ($messages)
            @foreach ((array) $messages as $message)
                <span class="text-danger d-block w-100">{{ $message }}</span><br>
            @endforeach
        @endif

        <div id="{{ $name }}_preview" class="list_multiple_image d-flex flex-wrap">
            @if($value)
                @foreach(explode(';', $value) as $image)
                    <div class="image-wrapper" data-url="{{ $image }}">
                        <img src="{{ $image }}"/>
                        <button type="button" class="remove-btn" onclick="removeImage(this)">×</button>
                    </div>
                @endforeach
            @endif
            <div class="image-wrapper add-image-btn" id="{{ $name }}_add_button">
                <div class="add-icon">+</div>
            </div>
        </div>

        @if($help ?? '')
            <div class="text-muted">{{ $help }}</div>
        @endif
    </div>

    <script>
        const inputId = "{{ $name }}_input";
        const previewId = "{{ $name }}_preview";

        document.getElementById("{{ $name }}_add_button").onclick = function () {
            selectMultiFileWithCKFinder(inputId, previewId);
        };

        function selectMultiFileWithCKFinder(inputId, previewId) {
            CKFinder.modal({
                chooseFiles: true,
                width: 800,
                height: 600,
                onInit: function (finder) {
                    finder.on('files:choose', function (evt) {
                        const files = evt.data.files.models;
                        const input = document.getElementById(inputId);
                        const preview = document.getElementById(previewId);
                        let urls = input.value ? input.value.split(';') : [];

                        files.forEach(function (file) {
                            const url = file.getUrl();
                            if (!urls.includes(url)) {
                                urls.push(url);

                                const wrapper = document.createElement('div');
                                wrapper.className = 'image-wrapper';
                                wrapper.setAttribute('data-url', url);

                                const img = document.createElement('img');
                                img.src = url;

                                const btn = document.createElement('button');
                                btn.className = 'remove-btn';
                                btn.type = 'button';
                                btn.innerText = '×';
                                btn.onclick = function () {
                                    removeImage(btn);
                                };

                                wrapper.appendChild(img);
                                wrapper.appendChild(btn);

                                const addBtn = document.getElementById("{{ $name }}_add_button");
                                preview.insertBefore(wrapper, addBtn);
                            }
                        });

                        input.value = urls.join(';');
                    });
                }
            });
        }

        function removeImage(button) {
            const wrapper = button.parentNode;
            const url = wrapper.getAttribute('data-url');
            wrapper.remove();

            const input = document.getElementById("{{ $name }}_input");
            let urls = input.value.split(';').filter(item => item !== url);
            input.value = urls.join(';');
        }
    </script>

    <style>
        .list_multiple_image {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .image-wrapper {
            width: 100px;
            height: 100px;
            position: relative;
        }

        .image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .add-image-btn {
            border: 2px dashed #bbb;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .add-image-btn:hover {
            background-color: #f8f9fa;
        }

        .add-icon {
            font-size: 32px;
            color: #bbb;
            user-select: none;
        }

        .remove-btn {
            position: absolute;
            top: -5px;
            right: -5px;
            background: red;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 14px;
            line-height: 16px;
            text-align: center;
            cursor: pointer;
        }
    </style>
</div>
