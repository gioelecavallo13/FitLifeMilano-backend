<!-- Pusher e Laravel Echo (caricati solo nelle pagine chat) -->
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/laravel-echo@1.16.1/dist/echo.iife.min.js"></script>
<script>
(function() {
    if (typeof Pusher === 'undefined' || typeof Echo === 'undefined') return;
    var csrf = document.querySelector('meta[name="csrf-token"]');
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: "{{ config('broadcasting.connections.pusher.key') }}",
        cluster: "{{ config('broadcasting.connections.pusher.options.cluster') }}",
        forceTLS: true,
        authEndpoint: "{{ url('/broadcasting/auth') }}",
        auth: {
            headers: {
                'X-CSRF-TOKEN': (csrf && csrf.getAttribute('content')) || '',
                'Accept': 'application/json'
            }
        }
    });
})();
</script>
