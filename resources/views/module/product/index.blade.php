@extends('main')
@section('title', '| Product')

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

                    <h4 class="card-title mb-0">Daftar Produk</h4>

                    @if (Session::get('error'))
                    <div class="alert alert-danger">
                        {{ Session::get('error') }}
                    </div>
                     @endif

                    @if(Auth::user()->role === 'admin')
                        <a href="{{ route('product.create') }}">
                            <button type="button" class="btn btn-info mb-4">
                                <i class="mdi mdi-plus"></i>Tambah Data</button>
                        </a>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Gambar</th>
                                <th>Nama Produk</th>
                                <th>Harga</th>
                                <th>Stok</th>
                                @if(Auth::user()->role === 'admin')
                                <th>Action</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>

                            <!-- Modal untuk tiap produk -->
                            <div class="modal fade" id="updateStockModal{{ $product->id }}" tabindex="-1" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Update Stock  <b>{{ $product->name }}</b></h5>
                                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <form action="{{ route('product.stock', $product->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')

                                                <!-- Stok -->
                                                <div class="mb-3">
                                                    <label for="stock" class="form-label">Stock</label>
                                                    <input type="number" class="form-control border-secondary" id="stock" name="stock" value="{{ $product->stock }}"  max="9999999999" oninput="this.value = this.value.slice(0, 10)">
                                                </div>

                                                <!-- Tombol Submit -->
                                                <button type="submit" class="btn btn-primary">Simpan</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @empty
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS ini berfungsi untuk modal -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

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
