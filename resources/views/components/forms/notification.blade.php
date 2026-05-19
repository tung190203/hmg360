@props(['success', 'error'])


<script type="text/javascript">
    toastr.options = {
        "closeButton": false,
        "debug": false,
        "newestOnTop": false,
        "progressBar": true,
        "positionClass": "toast-bottom-right",
        "preventDuplicates": true,
        "onclick": null,
        "showDuration": "300",
        "hideDuration": "1000",
        "timeOut": "5000",
        "extendedTimeOut": "1000",
        "showEasing": "swing",
        "hideEasing": "linear",
        "showMethod": "fadeIn",
        "hideMethod": "fadeOut"
    }

    @if($success ?? '')
        toastr["success"]("{{ $success }}", "Success!")
    @endif

    @if($error ?? '')
    toastr["error"]("{{ $error }}", "Lỗi!")
    console.log("{{ $error }}");
    @endif

    @if(session('error'))
    toastr["error"]("{{ session('error') }}", "Lỗi!")
    console.log("{{ session('error') }}");
    @endif

    @if($errors -> any())
    @foreach($errors -> all() as $error_msg)
    toastr["error"]("{{ $error_msg }}", "Lỗi!")
    console.log("{{ $error_msg }}");
    @endforeach
    @endif
</script>