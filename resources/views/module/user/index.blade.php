@extends('main')
@section('title', '| User')

@section('content')

<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    @if (session('success'))
                    <script>
                        document.addEventListener("DOMContentLoaded", function () {
                            Swal.fire({
                                title: "Sukses!",
                                text: "{!! session('success') !!}",
                                icon: "success",
                                confirmButtonText: "OK"
                            });
                        });
                    </script>
                    @endif

                    @if ($errors->any())
                    <script>
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            html: `{!! implode('<br>', $errors->all()) !!}`,
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'Tutup'
                        });
                    </script>
                    @endif
                    <h4 class="card-title mb-0">Daftar List</h4>
                    <a href="{{route('user.create')}}">
                        <button type="button" class="btn btn-info mb-4">Tambah User</button>
                    </a>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Email</th>
                                <th>Nama</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById("todo-form").addEventListener("submit", function (event) {
        event.preventDefault(); // Mencegah reload
        let form = this;

        fetch(form.action, {
            method: form.method,
            body: new FormData(form),
            headers: {
                "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: "Sukses!",
                    text: data.message,
                    icon: "success",
                    confirmButtonText: "OK"
                }).then(() => {
                    location.reload(); // Reload halaman setelah SweetAlert ditutup
                });
            }
        })
        .catch(error => console.error("Error:", error));
    });
</script>

@endsection
