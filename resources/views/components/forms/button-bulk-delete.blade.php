@props(['title', 'url', 'icon'])

<button type="button" class="btn btn-sm bg-maroon BulkDeleteRecords" data-checker=".checker"
    data-url="{{ $url ?? '' }}">
    <i class="{{ $icon ?? 'fa fa-trash mr-1' }} mr-1" aria-hidden="true"></i>
    {{ $title ?? 'Xóa' }}
</button>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.BulkDeleteRecords').forEach(button => {
            button.addEventListener('click', function() {
                const checkerSelector = this.dataset.checker;
                const url = this.dataset.url;
                const checkboxes = document.querySelectorAll(`${checkerSelector}:checked`);
                const ids = Array.from(checkboxes).map(cb => cb.value);

                if (!ids.length) {
                    alert('Vui lòng chọn ít nhất một bản ghi để xóa.');
                    return;
                }

                if (!confirm('Bạn có chắc chắn muốn xóa các bản ghi đã chọn?')) {
                    return;
                }

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            ids: ids
                        })
                    })
                    .then(async response => {
                        const contentType = response.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            return await response.json();
                        } else {
                            throw new Error('Response is not JSON');
                        }
                    })
                    .then(data => {
                        window.location.reload();
                    })
                    .catch(error => {
                        console.error('Fetch error:', error);
                    });
            });
        });
    });
</script>
